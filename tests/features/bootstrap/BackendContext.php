<?php

namespace Payever\Tests;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Mink;
use Behat\MinkExtension\Context\MinkAwareContext;
use Payever\Stub\BehatExtension\Context\PluginAwareContext;
use Payever\Stub\BehatExtension\ServiceContainer\PluginConnectorInterface;

class BackendContext implements PluginAwareContext, MinkAwareContext
{
    /** @var Mink */
    private $mink;

    /** @var array */
    private $minkConfig;

    /** @var OxidPluginConnector */
    private $connector;

    /** @var array */
    private $extensionConfig;

    /** @var FrontendContext */
    private $frontend;

    /**
     * {@inheritDoc}
     */
    public function setPluginConnector(PluginConnectorInterface $connector)
    {
        $this->connector = $connector;
    }

    /**
     * {@inheritDoc}
     */
    public function setExtensionConfig(array $config)
    {
        $this->extensionConfig = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function setMink(Mink $mink)
    {
        $this->mink = $mink;
    }

    /**
     * {@inheritDoc}
     */
    public function setMinkParameters(array $parameters)
    {
        $this->minkConfig = $parameters;
    }

    /**
     * @BeforeScenario
     */
    public function beforeScenario(BeforeScenarioScope $scope)
    {
        $this->frontend = $scope->getEnvironment()->getContext(FrontendContext::class);
    }

    /**
     * @Given /^the following payment options should (not\s)?be active:$/
     * | key |
     *
     * @param TableNode $table
     * @param bool $not
     * @throws AssertionFailedException
     * @throws \oxSystemComponentException
     */
    public function assertPaymentOptionsActive(TableNode $table, $not = false)
    {
        foreach ($table as $item) {
            /** @var \oxPayment $oPayment */
            $oPayment = oxNew('oxpayment');
            $oPayment->load('oxpe_' . $item['key']);
            Assertion::eq(
                $oPayment->oxpayments__oxactive,
                $not ? false : true,
                "{$item['key']} state is %s"
            );
        }
    }

    /**
     * @Given /^new order status should be "(OK|IN\_PROCESS)"$/
     *
     * @param string $expectedState
     * @throws AssertionFailedException
     */
    public function assertOrderStateEqualsTo($expectedState)
    {
        $order = $this->connector->getLastOrder();
        Assertion::eq($order['OXTRANSSTATUS'], $expectedState);
    }

    /**
     * @Given /^(?:|I )select the top order$/
     */
    public function selectTopOrder()
    {
        $this->frontend->clickLink($this->connector->getLastOrderId());
    }

    /**
     * @Given /^(?:|I )call executePluginCommands controller$/
     */
    public function iCallExecutePluginCommandsController()
    {
        // prepare client for session
        $client = new \GuzzleHttp\Client([
            'base_uri' => $this->extensionConfig['plugin']['base_url'],
            'cookies' => true,
        ]);
        $client->get('/index.php?cl=payeverstandardDispatcher&fnc=executePluginCommands');
    }

    /**
     * @Given /^(?:|I )set plugin command "([^"]+)" value "([^"]*)"$/
     */
    public function iSetPluginCommandValue($keyName, $value)
    {
        $this->connector->setPluginCommandsValue($keyName, $value);
    }

    /**
     * @Given /^(?:|I )set plugin api version "([^"]+)" value to "([^"]*)"$/
     */
    public function iSetPluginApiVersionValueTo($keyName, $value)
    {
        $this->connector->setPluginApiVersionValue($keyName, $value);
    }

    /**
     * @Given /^plugin api version "([^"]+)" value must be equal to "([^"]+)"$/
     */
    public function pluginApiVersionValueMustBeEqualTo($key, $value)
    {
        Assertion::eq($value, $this->connector->getPluginApiVersionlValue($key));
    }

    /**
     * @Given /^plugin custom url "([^"]+)" value must be equal to "([^"]+)"$/
     */
    public function pluginCustomUrlValueMustBeEqualTo($key, $value)
    {
        Assertion::eq($value, $this->connector->getPluginCustomUrlValue($key));
    }

    /**
     * @Given /^(?:|I )connect payment methods to shipping method$/
     */
    public function connectPaymentMethodsToShippingMethod()
    {
        $this->connector->connectPaymentMethodsToShippingMethod();
    }

    /**
     * @Then /^(?:|I )wait sync queue (may not\s)?exists and populated with size (\d+)$/
     * @param bool $not
     * @param int $size
     * @throws AssertionFailedException
     */
    public function waitSyncQueueExistsAndPopulatedWithSize($not = false, $size = 1)
    {
        $sizeMatched = false;
        $attempt = 60;
        while ($attempt) {
            if ($this->connector->getSyncQueueCount() >= $size) {
                $sizeMatched = true;
                break;
            }
            sleep(1);
            $attempt--;
        }
        if (!$not) {
            Assertion::true($sizeMatched, 'Sync queue to be populated awaiting is timed out');
        }
    }

    /**
     * @Then /^(?:|I )clear cache$/
     */
    public function clearCache()
    {
        $this->connector->clearCache();
    }

    /**
     * @Then /^(?:|I )clear products sync queue$/
     */
    public function clearSynchronizationQueue()
    {
        $this->connector->clearSynchronizationQueue();
    }

    /**
     * @Then /^(?:|I )run products sync cronjob$/
     */
    public function runProductsSyncCronJob()
    {
        $this->connector->runSyncQueueConsumer();
    }

    /**
     * @Then /^(?:|I )press export products button$/
     */
    public function pressExportProductsButton()
    {
        $buttonSelector = 'input[name="exportProductsAndInventory"]';
        $this->frontend->waitTillElementExists($buttonSelector);
        $this->frontend->getSession()->executeScript("
            let elements = document.querySelectorAll('$buttonSelector');
            if (elements.length > 0) {
                elements[0].data_page=10;
            } else {
               throw new Error('Element not found by $buttonSelector');
            }
        ");
        $this->frontend->pressOnCssSelector($buttonSelector);
    }
}
