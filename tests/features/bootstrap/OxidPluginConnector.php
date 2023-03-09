<?php

namespace Payever\Tests;

use Payever\Stub\BehatExtension\ServiceContainer\PluginConnectorInterface;

class OxidPluginConnector implements PluginConnectorInterface
{
    const PLUGIN_CODE = 'payever';
    const STUB_PRODUCT_SKU = '1302';

    /** @var string */
    private $oxidDir;

    /** @var \PayeverProductHelper */
    private $productHelper;

    /** @var \PayeverCategoryManager */
    private $categoryManager;

    /** @var \PayeverSyncQueueConsumeCommand */
    private $syncQueueConsumeCommand;

    /** @var bool|null */
    private static $isCmsConfigPrepared;

    /** @var bool|null */
    private static $isPluginEnabled;

    /** @var bool|null */
    private static $paymentMethodsAreSet;

    /** @var string|null */
    private $oxidVersion = null;

    /**
     * @param string $oxidDir
     * @throws \oxSystemComponentException
     */
    public function __construct($oxidDir)
    {
        if (!file_exists($oxidDir)) {
            throw new \RuntimeException(sprintf('Oxid directory %s does not exists', $oxidDir));
        }
        $this->oxidDir = rtrim($oxidDir, '/');
        $this->initOxid();
    }

    /**
     * {@inheritDoc}
     */
    public function prepareCmsConfig()
    {
        if (null === self::$isCmsConfigPrepared) {
            $this->setupStubProduct();
            $this->setupCurrenciesAndRates();
            $this->allowAllConstraints();
            $this->updateViews();
            self::$isCmsConfigPrepared = true;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getPluginDefaultConfig()
    {
        return [
            \PayeverConfig::KEY_LOG_LEVEL => \Psr\Log\LogLevel::DEBUG,
            \PayeverConfig::KEY_API_MODE => '0',
            \PayeverConfig::KEY_IS_REDIRECT => '1',
            \PayeverConfig::KEY_API_CLIENT_ID => '1454_2ax8i5chkvggc8w00g8g4sk80ckswkw0c8k8scss40o40ok4sk',
            \PayeverConfig::KEY_API_CLIENT_SECRET => '22uvxi05qlgk0wo8ws8s44wo8ccg48kwogoogsog4kg4s8k8k',
            \PayeverConfig::KEY_API_SLUG => 'payever',
            \PayeverConfig::PRODUCTS_SYNC_ENABLED => '0',
            \PayeverConfig::PRODUCTS_OUTWARD_SYNC_ENABLED => '1',
            \PayeverConfig::PRODUCTS_SYNC_EXTERNAL_ID => 'externalIdHash',
            \PayeverConfig::PRODUCTS_SYNC_CURRENCY_RATE_SOURCE => \PayeverConfig::CURRENCY_RATE_SOURCE_PAYEVER,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function isPluginEnabled()
    {
        if (null === self::$isPluginEnabled) {
            self::$isPluginEnabled = $this->getPlugin()->isActive();
        }

        return self::$isPluginEnabled;
    }

    /**
     * {@inheritDoc}
     */
    public function enablePlugin()
    {
        if ($this->isPluginEnabled()) {
            return;
        }
        if ($this->oxidVersion && '6.3.0' === $this->oxidVersion) {
            $binPath = str_replace('/source', '/bin', $this->oxidDir);
            shell_exec("php $binPath/oe-console oe:module:activate payever");
            self::$isPluginEnabled = true;
            return;
        }

        $plugin = $this->getPlugin();
        $oModuleInstaller = $this->getModuleInstaller();

        if ($oModuleInstaller) {
            $errorReporting = ini_get('error_reporting');
            ini_set('error_reporting', 0);
            if (!$oModuleInstaller->activate($plugin)) {
                throw new \RuntimeException("Couldn't enable payever plugin");
            }
            ini_set('error_reporting', $errorReporting);
        } else {
            /**
             * Turns out OXID4 never loads plugin classes while installation from CLI
             * hence we're doing it manually
             */
            $payeverClassesDir = $this->oxidDir
                . DIRECTORY_SEPARATOR . 'modules'
                . DIRECTORY_SEPARATOR . self::PLUGIN_CODE
                . DIRECTORY_SEPARATOR . 'classes'
                . DIRECTORY_SEPARATOR ;

            require_once  $payeverClassesDir . 'PayeverInstaller.php';
            require_once  $payeverClassesDir . 'PayeverConfig.php';
            // old oxid versions
            method_exists($plugin, 'activate') && $plugin->activate();
        }

        self::$isPluginEnabled = true;
        $this->clearCache();
    }

    /**
     * {@inheritDoc}
     */
    public function disablePlugin()
    {
        if (!$this->isPluginEnabled()) {
            return;
        }
        if ($this->oxidVersion && '6.3.0' === $this->oxidVersion) {
            $binPath = str_replace('/source', '/bin', $this->oxidDir);
            shell_exec("php $binPath/oe-console oe:module:deactivate payever");
            self::$isPluginEnabled = false;
            return;
        }

        $plugin = $this->getPlugin();
        $oModuleInstaller = $this->getModuleInstaller();
        if ($oModuleInstaller) {
            if (!$oModuleInstaller->deactivate($plugin)) {
                throw new \RuntimeException('Unable to disable payever plugin');
            }
        } else {
            // old oxid versions
            method_exists($plugin, 'deactivate') && $plugin->deactivate();
        }
        self::$isPluginEnabled = false;
        $this->clearCache();
    }

    /**
     * {@inheritDoc}
     */
    public function setPluginConfigValue($key, $value)
    {
        $pluginConf = $this->getOxConfig()->getShopConfVar(\PayeverConfig::VAR_CONFIG);
        $pluginConf[$key] = $value;
        $this->getOxConfig()->saveShopConfVar('arr', \PayeverConfig::VAR_CONFIG, $pluginConf);
    }

    /**
     * {@inheritDoc}
     */
    public function getPluginConfigValue($key)
    {
        $pluginConf = $this->getOxConfig()->getShopConfVar(\PayeverConfig::VAR_CONFIG);

        return isset($pluginConf[$key]) ? $pluginConf[$key] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function setStubApiEndpoint($url = null)
    {
        $this->getOxConfig()->saveShopConfVar(
            'arr',
            \PayeverConfig::VAR_SANDBOX,
            [\PayeverConfig::KEY_SANDBOX_URL => $url]
        );
        $this->getOxConfig()->saveShopConfVar(
            'arr',
            \PayeverConfig::VAR_LIVE,
            [\PayeverConfig::KEY_LIVE_URL => $url]
        );
        $this->getOxConfig()->saveShopConfVar(
            'arr',
            \PayeverConfig::VAR_CUSTOM_THIRD_PARTY_PRODUCTS_SANDBOX_URL,
            [\PayeverConfig::KEY_CUSTOM_THIRD_PARTY_PRODUCTS_SANDBOX_URL => $url]
        );
        $this->getOxConfig()->saveShopConfVar(
            'arr',
            \PayeverConfig::VAR_CUSTOM_THIRD_PARTY_PRODUCTS_LIVE_URL,
            [\PayeverConfig::KEY_CUSTOM_THIRD_PARTY_PRODUCTS_LIVE_URL => $url]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function clearOauthTokensStorage()
    {
        $this->getOxConfig()->saveShopConfVar(
            'arr',
            \PayeverApiOauthTokenList::CONFIG_STORAGE_VAR,
            []
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getLastOrderId()
    {
        $order = $this->getLastOrder();

        return is_array($order) && !empty($order['OXORDERNR']) ? $order['OXORDERNR'] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function isThirdPartySubscriptionEnabled()
    {
        return (bool) $this->getPluginConfigValue(\PayeverConfig::PRODUCTS_SYNC_ENABLED);
    }

    /**
     * {@inheritDoc}
     */
    public function toggleThirdPartySubscription()
    {
        $this->resetConfigs();
        $before = $this->isThirdPartySubscriptionEnabled();
        $after = $this->getSubscriptionManager()->toggleSubscription(!$before);
        $this->setPluginConfigValue(\PayeverConfig::PRODUCTS_SYNC_ENABLED, $after);
    }

    /**
     * {@inheritDoc}
     */
    public function doesProductExist($sku)
    {
        return (bool) $this->getProductHelper()->getProductBySku($sku)->getId();
    }

    /**
     * {@inheritDoc}
     */
    public function getProductCategories($sku)
    {
        $product = $this->getProductHelper()->getProductBySku($sku);

        return $this->getCategoryManager()->getCategoryNames($product);
    }

    /**
     * {@inheritDoc}
     */
    public function removeProduct($sku)
    {
        $this->resetConfigs();
        $product = $this->getProductHelper()->getProductBySku($sku);
        method_exists($product, 'setConfig') && $product->setConfig(null);
        \PayeverRegistry::set(\PayeverRegistry::LAST_INWARD_PROCESSED_PRODUCT, $product);
        $product->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function getProductInventoryValue($sku)
    {
        return $this->getProductHelper()->getProductBySku($sku)->getFieldData('oxstock');
    }

    /**
     * {@inheritDoc}
     */
    public function getProductFieldValue($sku, $fieldName)
    {
        return $this->getProductHelper()->getProductBySku($sku)->getFieldData($fieldName);
    }

    /**
     * {@inheritDoc}
     */
    public function getProductVariantFieldValue($sku, $fieldName)
    {
        return $this->getProductFieldValue($sku, $fieldName);
    }

    /**
     * {@inheritDoc}
     */
    public function productHasVariant($sku, $variantSku)
    {
        $product = $this->getProductHelper()->getProductBySku($sku);
        $variant = $this->getProductHelper()->getProductBySku($variantSku);

        return $variant->getParentId() === $product->getId();
    }

    /**
     * {@inheritDoc}
     */
    public function getProductUrl($sku)
    {
        $row = $this->getDb()
            ->getRow(
                'SELECT OXSEOURL as path FROM oxseo where OXOBJECTID = ?',
                [$this->getProductHelper()->getProductBySku($sku)->getId()]
            );

        return !empty($row['path']) ? '/' . $row['path'] : null;
    }

    /**
     * @param string $sku
     * @param string $field
     * @param string $value
     * @return bool
     * @throws \oxSystemComponentException
     */
    public function getProductVariantOptionValueExists($sku, $field, $value)
    {
        $variant = $this->getProductHelper()->getProductBySku($sku);
        $product = $this->getProductHelper()->getProductById($variant->getParentId());

        return false !== mb_strpos($product->getFieldData('oxvarname'), $field)
            && false !== mb_strpos($variant->getFieldData('oxvarselect'), $value);
    }

    /**
     * @return array
     * @throws \oxConnectionException
     */
    public function getLastOrder()
    {
        return $this->getDb()
            ->getRow('SELECT * FROM oxorder ORDER BY OXORDERNR DESC LIMIT 1');
    }

    /**
     * @return bool
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function arePaymentMethodsSet()
    {
        return (bool) self::$paymentMethodsAreSet;
    }

    /**
     * @throws \oxConnectionException
     */
    public function setupPaymentMethods()
    {
        if (null === self::$paymentMethodsAreSet) {
            $this->resetConfigs();
            $payeverConfig = new \payever_config();
            $payeverConfig->setParameters($this->getOxConfig()->getShopConfVar(\PayeverConfig::VAR_CONFIG));
            $payeverConfig->synchronize();
            self::$paymentMethodsAreSet = true;
        }
    }

    /**
     * @throws \oxConnectionException
     */
    public function connectPaymentMethodsToShippingMethod()
    {
        $deliverySetPaymentControl = new \deliveryset_payment_ajax();
        $GLOBALS['_POST'] = [
            'synchoxid' => 'oxidstandard',
            'all' => 1,
        ];

        $deliverySetPaymentControl->addPayToSet();

        // the code below is still actual for oxid 4.8.9
        // delivery to country
        $deliveryCountryControl = new \deliveryset_country_ajax();
        $deliveryCountryControl->addCountryToSet();

        // delivery to shipping rules
        $deliveryCountryControl = new \deliveryset_main_ajax();
        $deliveryCountryControl->addToSet();

        // shipping rules to country
        $shippingRuleControl = new \delivery_main_ajax();
        $rules = $this->getDb()->getAll('SELECT * FROM oxdelivery');
        foreach ($rules as $rule) {
            $id = !empty($rule['OXID']) ? $rule['OXID'] : null;
            if (!$id && !empty($rule[0])) {
                $id = $rule[0];
            }
            if ($id) {
                $GLOBALS['_POST']['synchoxid'] = $id;
                $shippingRuleControl->addCountryToDel();
            }
        }

        // user group to payment
        $paymentControl = new \payment_main_ajax();
        $payments = $this->getDb()->getAll('SELECT * FROM oxpayments');
        foreach ($payments as $payment) {
            $id = !empty($payment['OXID']) ? $payment['OXID'] : null;
            if (!$id && !empty($payment[0])) {
                $id = $payment[0];
            }
            if ($id) {
                $GLOBALS['_POST']['synchoxid'] = $id;
                $paymentControl->addPayGroup();
            }
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setPluginCommandsValue($key, $value)
    {
        $data = $this->getOxConfig()->getShopConfVar(\PayeverConfig::VAR_PLUGIN_COMMANDS);
        $data[$key] = $value;
        $this->getOxConfig()->saveShopConfVar('arr', \PayeverConfig::VAR_PLUGIN_COMMANDS, $data);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setPluginApiVersionValue($key, $value)
    {
        $data = $this->getOxConfig()->getShopConfVar(\PayeverConfig::VAR_PLUGIN_API_VERSION);
        $data[$key] = $value;
        $this->getOxConfig()->saveShopConfVar('arr', \PayeverConfig::VAR_PLUGIN_API_VERSION, $data);
    }

    /**
     * @param string $dataKey
     * @return string|null
     */
    public function getPluginApiVersionlValue($dataKey)
    {
        $key = \PayeverConfig::VAR_PLUGIN_API_VERSION;
        $data = $this->getOxConfig()->getShopConfVar($key);

        return !empty($data[$dataKey]) ? $data[$dataKey] : null;
    }

    /**
     * @param string $dataKey
     * @return string|null
     */
    public function getPluginCustomUrlValue($dataKey)
    {
        $key = \PayeverConfig::VAR_SANDBOX;
        if (\PayeverConfig::KEY_LIVE_URL === $dataKey) {
            $key = \PayeverConfig::VAR_LIVE;
        }
        $data = $this->getOxConfig()->getShopConfVar($key);

        return !empty($data[$dataKey]) ? $data[$dataKey] : null;
    }

    /**
     * Clears cache
     */
    public function clearCache()
    {
        $cacheDir = rtrim($this->oxidDir, DIRECTORY_SEPARATOR) . 'tmp';
        if ($cacheDir !== DIRECTORY_SEPARATOR . 'tmp' && is_dir($cacheDir)) {
            shell_exec(sprintf('rm -rf %s', $cacheDir));
        }
    }

    /**
     * @throws \oxConnectionException
     */
    public function clearSynchronizationQueue()
    {
        $this->getDb()->execute('DELETE FROM payeversynchronizationqueue WHERE 1');
    }

    /**
     * @return int
     * @throws \oxConnectionException
     */
    public function getSyncQueueCount()
    {
        $result = 0;
        $row = $this->getDb()->getRow('SELECT COUNT(*) AS cnt FROM payeversynchronizationqueue');
        if (!empty($row['cnt'])) {
            $result = (int) $row['cnt'];
        }

        return $result;
    }

    /**
     * @throws \Exception
     */
    public function runSyncQueueConsumer()
    {
        $this->resetConfigs();
        $this->getSyncQueueConsumeCommand()->run(
            new \Symfony\Component\Console\Input\ArrayInput([]),
            new \Symfony\Component\Console\Output\NullOutput()
        );
    }

    /**
     * @return string
     */
    public function getStubProductId()
    {
        return self::STUB_PRODUCT_SKU;
    }

    /**
     * @throws \oxSystemComponentException
     */
    private function initOxid()
    {
        // oxid and contexts use conflicting versions of symfony packages
        // Lets trigger autoload of contexts' package version
        $conflictClasses = [
            \Symfony\Component\Config\FileLocator::class,
            \Symfony\Component\Console\Command\Command::class,
            \Symfony\Component\Console\Input\ArrayInput::class,
            \Symfony\Component\Console\Output\NullOutput::class,
            \Symfony\Component\DependencyInjection\Container::class,
            \Symfony\Component\EventDispatcher\EventDispatcher::class,
            \Symfony\Component\Process\Process::class,
        ];
        foreach ($conflictClasses as $class) {
            class_exists($class);
        }
        $modulesDir = sprintf('%s%smodules%s', $this->oxidDir, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
        $moduleDir = sprintf('%s%s%s', $modulesDir, self::PLUGIN_CODE, DIRECTORY_SEPARATOR);
        $filesToRequire = [
            $this->oxidDir . DIRECTORY_SEPARATOR . 'bootstrap.php',
            $moduleDir . 'autoload.php',
            $this->oxidDir . '/Core/ShopVersion.php',
        ];

        $aModule = [];
        include $moduleDir . 'metadata.php';
        if (!empty($aModule['files']) && is_array($aModule['files'])) {
            foreach ($aModule['files'] as $filePath) {
                $filesToRequire[] = $modulesDir . $filePath;
            }
        }
        foreach ($filesToRequire as $fileToRequire) {
            if (file_exists($fileToRequire)) {
                require_once $fileToRequire;
            }
        }
        if (class_exists('\OxidEsales\EshopCommunity\Core\ShopVersion', false)) {
            $this->oxidVersion = \OxidEsales\EshopCommunity\Core\ShopVersion::getVersion();
        }

        $this->updateViews();
    }

    /**
     * @throws \oxSystemComponentException
     */
    private function setupStubProduct()
    {
        /** @var \payeverOxArticle $product */
        $product = oxNew('oxarticle');
        $query = $product->buildSelectString(['oxartnum' => self::STUB_PRODUCT_SKU]);
        $product->assignRecord($query);
        $field = oxNew('oxfield');
        $field->setValue(50);
        $product->oxarticles__oxprice = $field;
        $product->oxarticles__oxtprice = $field;
        \PayeverRegistry::set(\PayeverRegistry::LAST_INWARD_PROCESSED_PRODUCT, $product);
        method_exists($product, 'setSkipSyncHandling') && $product->setSkipSyncHandling(true);
        $product->save();
    }

    /**
     * Configures currencies
     */
    private function setupCurrenciesAndRates()
    {
        $currencies = [
            'EUR@ 1.00@ ,@ .@ €@ 2',
            'USD@ 1.2994@ .@  @ $@ 2',
            'NOK@ 10@ ,@ .@ NOK@ 2',
            'DKK@ 7.69@ ,@ .@ DKK@ 2',
            'SEK@ 11@ ,@ .@ SEK@ 2',
        ];
        $this->getOxConfig()->saveShopConfVar('arr', 'aCurrencies', $currencies);
    }

    /**
     * @throws \oxConnectionException
     */
    private function allowAllConstraints()
    {
        // do not remove uploaded images on product delete
        $this->getOxConfig()->saveShopConfVar('num', 'iPicCount', '-1');
        // set all countries active
        $this->getDb()->execute('UPDATE oxcountry SET OXACTIVE = 1;');
        $this->getOxConfig()->saveShopConfVar('num', 'iCreditRating', 2000);
        $this->getOxConfig()->saveShopConfVar('bool', 'blUseStock', false);
        // remove live keys otherwise each second test run will be broken
        $this->getDb()->execute("DELETE FROM `oxconfig` WHERE `OXVARNAME` = 'payever_live_keys'");
        // countries
        $countryMap = [
            'Deutschland' => 'Germany',
            'Dänemark' => 'Denmark',
            'Schweden' => 'Sweden',
            'Norwegen' => 'Norway'
        ];
        foreach ($countryMap as $deName => $enName) {
            $this->getDb()->execute("UPDATE `oxcountry` SET `OXTITLE` = '$enName' WHERE `OXTITLE` = '$deName'");
        }
    }

    /**
     * @return \oxDb|\oxLegacyDb
     * @throws \oxConnectionException
     */
    private function getDb()
    {
        return \oxDb::getDb(\oxDb::FETCH_MODE_ASSOC);
    }

    /**
     * @throws \oxSystemComponentException
     */
    private function updateViews()
    {
        /** @var \oxDbMetaDataHandler $oDbHandler */
        $oDbHandler = oxNew(\oxDbMetaDataHandler::class);
        $oDbHandler->updateViews();
    }

    /**
     * @return \oxModule
     * @throws \oxSystemComponentException
     */
    private function getPlugin()
    {
        /** @var \oxModule $module */
        $module = \oxNew(\oxModule::class);
        if (!$module->load(self::PLUGIN_CODE)) {
            throw new \UnexpectedValueException("Couldn't load payever plugin");
        }

        return $module;
    }

    /**
     * @return \oxModuleInstaller|null
     * @throws \oxSystemComponentException
     */
    private function getModuleInstaller()
    {
        $oModuleInstaller = null;
        if (class_exists('oxModuleInstaller')) {
            /** @var \oxModuleInstaller|null $oModuleInstaller */
            $oModuleInstaller = \oxNew(\oxModuleInstaller::class);
        }

        return $oModuleInstaller;
    }

    /**
     * @return \oxConfig
     */
    private function getOxConfig()
    {
        /**
         * This instance should not be cached - we need config reload on each call,
         * otherwise we may have stale data inside instance and it will be written into DB
         */
        return new \oxConfig();
    }

    /**
     * @return \PayeverSubscriptionManager
     * @throws \Exception
     */
    private function getSubscriptionManager()
    {
        // do not cache the instance to reload modified one by behat options
        $this->resetConfigs();
        $subscriptionManager = new \PayeverSubscriptionManager();
        $subscriptionManager->setThirdPartyApiClient(\PayeverApiClientProvider::getThirdPartyApiClient(true));

        return $subscriptionManager;
    }

    /**
     * Reset configs to reload
     */
    private function resetConfigs()
    {
        \oxRegistry::set('oxConfig', null);
        \PayeverConfig::reset();
    }

    /**
     * @return \PayeverProductHelper
     */
    private function getProductHelper()
    {
        return null === $this->productHelper
            ? $this->productHelper = new \PayeverProductHelper()
            : $this->productHelper;
    }

    /**
     * @return \PayeverCategoryManager
     */
    private function getCategoryManager()
    {
        return null === $this->categoryManager
            ? $this->categoryManager = new \PayeverCategoryManager()
            : $this->categoryManager;
    }

    /**
     * @return \PayeverSyncQueueConsumeCommand
     */
    private function getSyncQueueConsumeCommand()
    {
        return null === $this->syncQueueConsumeCommand
            ? $this->syncQueueConsumeCommand = new \PayeverSyncQueueConsumeCommand()
            : $this->syncQueueConsumeCommand;
    }
}
