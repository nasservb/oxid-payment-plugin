<?php

namespace Payever\Tests;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ResponseTextException;
use oxviewconfig;
use Payever\Stub\BehatExtension\Context\FrontendContext as BaseFrontendContext;
use Payever\Stub\BehatExtension\ServiceContainer\BackendCredentialsAwareInterface;
use WebDriver\Exception\ElementNotVisible;

class FrontendContext extends BaseFrontendContext implements BackendCredentialsAwareInterface
{
    const CHECKOUT_NEXT_STEP_TEXT = 'Continue to the next step';
    const CHECKOUT_NEXT_STEP_TEXT_LEGACY = 'Continue to Next Step';

    /** @var string */
    private $backendPath;

    /** @var string */
    private $backendUsername;

    /** @var string */
    private $backendPassword;

    /**
     * {@inheritDoc}
     */
    public function setPath($path)
    {
        $this->backendPath = $path;
    }

    /**
     * {@inheritDoc}
     */
    public function setUsername($username)
    {
        $this->backendUsername = $username;
    }

    /**
     * {@inheritDoc}
     */
    public function setPassword($password)
    {
        $this->backendPassword = $password;
    }

    /**
     * @Given /^(?:|I )login to admin section$/
     */
    public function loginToAdminSection()
    {
        $page = $this->getSession()->getPage();
        $this->waitTillElementExists('#usr');
        $this->waitTillElementExists('#pwd');
        $page->fillField('usr', $this->backendUsername);
        $page->fillField('pwd', $this->backendPassword);
        $page->pressButton('Start OXID eShop Admin');
        $this->getSession()->wait(2000);
    }

    /**
     * @Given /^(?:|I am )on admin login page$/
     */
    public function openAdminHomepage()
    {
        $this->visitPath($this->backendPath);
    }

    /**
     * @Given /^(?:|I )switch to iframe "(\w+)"$/
     * @Given /^(?:|I )switch to root document$/
     *
     * @param $name
     */
    public function switchToIframe($name = null)
    {
        $this->wait(1);
        if ($name) {
            $this->waitTillElementExists('#' . $name);
        }
        $this->getSession()->switchToIFrame($name);
        $this->wait(1);
    }

    /**
     * @Given /^(?:|I )choose purchase without registration$/
     */
    public function selectGuestCheckout()
    {
        $this->pressOnCssLocator('#optionNoRegistration button');
    }

    /**
     * @Given /^(?:|I )select "(\w+)" from country field "(\w+)"$/
     *
     * @param string $name
     * @param string $fieldName
     * @param int $attemptsLeft
     * @throws ResponseTextException
     * @throws \Behat\Mink\Exception\ElementException
     */
    public function selectCountry($name, $fieldName, $attemptsLeft = 5)
    {
        try {
            $page = $this->getSession()->getPage();
            if (ThemeHelper::isOldAzureTheme()) {
                $select = $page->find('css', "#{$fieldName}");
                $option = $select->find('xpath', "//option[normalize-space(text())=\"{$name}\"]");
                if ($option) {
                    $option->click();

                    return;
                }
            }
            $selectBtn = $page->find('css', "[data-id=\"{$fieldName}\"]");
            if ($selectBtn) {
                $selectBtn->click();
                $this->getSession()->wait(5000);
                foreach ($selectBtn->getParent()->findAll('css', 'ul > li') as $element) {
                    /** @var NodeElement $element */
                    if (stripos($element->getHtml(), $name) !== false) {
                        $element->click();

                        return;
                    }
                }
            }
        } catch (ElementNotVisible $exception) {
            if ($attemptsLeft--) {
                return $this->selectCountry($name, $fieldName, $attemptsLeft);
            }
        }

        throw new ResponseTextException(
            sprintf('Country %s not found', $name),
            $this->getSession()->getDriver()
        );
    }

    /**
     * @Given /^payment option "([^"]+)" should (not\s)?be available$/
     *
     * @param string $option
     * @param bool $assertNot
     * @throws ResponseTextException
     * @throws \Behat\Mink\Exception\DriverException
     * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
     */
    public function assertPaymentOptionAvailableOrNot($option, $assertNot = false)
    {
        $selector = sprintf('input[value="oxpe_%s"]', $option);
        $script = "(function () {
            let result = false;
            let elements = document.querySelectorAll('$selector');
            if (elements.length > 0) {
                result = true;
            }

            return result;
        })();";
        $result = $this->getSession()->evaluateScript($script);
        $failed = $assertNot ? $result !== false : $result !== true;

        if ($failed) {
            throw new ResponseTextException(
                sprintf(
                    'Payment option %s is %s found',
                    $option,
                    $assertNot ? '' : 'not '
                ),
                $this->getSession()
            );
        }
    }

    /**
     * @Given /^(?:|I )select payment option "([^"]+)"$/
     *
     * @param string $name
     */
    public function selectCheckoutPaymentOption($name)
    {
        $this->waitTillElementExists('#paymentHeader');
        $selector = sprintf('input[value="%s"]', $name);
        $this->getSession()->executeScript("(function () {
            let elements = document.querySelectorAll('$selector');
            if (elements.length > 0) {
                elements[0].click();
                return;
            }
            throw new Error('Payment option $selector not found on this page');
        })();");
    }

    /**
     * @Given /^(?:|I )switch to "([A-Z]{3,3})" currency$/
     *
     * @param $symbol
     */
    public function switchToCurrency($symbol)
    {
        /**
         * order should be same as in @see Helper::setupCurrenciesAndRates()
         */
        static $map = [
            'EUR',
            'USD',
            'NOK',
            'DKK',
            'SEK',
        ];
        $this->visit(
            $this->getSession()->getCurrentUrl()
            . "?cur=" . array_search($symbol, $map) . "&lang=1"
        );
    }

    /**
     * @Given /^(?:|I )should (not\s)?see payever iframe$/
     */
    public function assertPayeverIframeExists($not = false)
    {
        $iframe = $this->getSession()->getPage()->find('css', '#payever_iframe');
        if ($not) {
            Assertion::null($iframe, 'payever iframe found on the current page');
        } else {
            Assertion::notNull($iframe, 'payever iframe not found on the current page');
        }
    }

    /**
     * @Given /^(?:|I )should (not\s)?see payever payment option icons$/
     *
     * @param bool $not
     * @throws AssertionFailedException
     */
    public function assertPayeverPaymentIconsPresent($not = false)
    {
        $icons = $this->getSession()->getPage()->findAll('css', '.payever-payment-icon');
        $cnt = count($icons);
        if ($not) {
            Assertion::eq($cnt, 0, 'payever payment icons found on the current page');
        } else {
            Assertion::greaterOrEqualThan($cnt, 1, 'payever payment icons not found on the current page');
        }
    }


    /**
     * @Then /^(?:|I )check payment icons path$/
     */
    public function iCheckPaymentIconsPath()
    {
        $iconUrls = $this->getSession()->getPage()->findAll('css', '.payever-payment-icon img');
        $patternNewPath = str_replace('%s', '.+', 'out/pictures/%s.png');

        foreach ($iconUrls as $iconUrl) {
            if ($iconUrl->hasAttribute('src')) {
                $iconSrc = $iconUrl->getAttribute('src');
                Assertion::eq(1, preg_match("@$patternNewPath@", $iconSrc), 'payever payment icons have wrong path');
            }
        }
    }

    /**
     * @Given /^(?:|I )should (not\s)?see payever payment option descriptions$/
     *
     * @param bool $not
     * @throws AssertionFailedException
     */
    public function asserPayeverPaymentDescriptionPresent($not = false)
    {
        $descrs = $this->getSession()->getPage()->findAll('css', '.payever-payment-description');
        $cnt = count($descrs);

        if ($not) {
            Assertion::eq($cnt, 0, 'payever payment description found on the current page');
        } else {
            Assertion::greaterOrEqualThan($cnt, 1, 'payever payment description not found on the current page');
        }
    }

    /**
     * @Given /^(?:|I )press confirm order button$/
     */
    public function confirmOrder()
    {
        $this->pressOnCssLocator('#orderConfirmAgbBottom .nextStep');
    }

    /**
     * @Then /^(?:|I )expect export button is (not\s)?disabled$/
     *
     * @param bool $not
     * @throws AssertionFailedException
     */
    public function exportButtonIsDisabledOrNot($not = false)
    {
        $script = "(function () {
            let result = false;
            let elements = document.querySelectorAll('input[name=exportProductsAndInventory]');
            if (elements.length > 0) {
                result = elements[0].disabled === true;
            }

            return result;
        })();";
        $result =  $this->getSession()->evaluateScript($script);
        Assertion::true(
            $not ? !$result : $result,
            "Export button is " . ($result ? 'disabled' : 'enabled')
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function fixStepArgument($argument)
    {
        if ($argument === static::CHECKOUT_NEXT_STEP_TEXT) {
            /** @var \oxTheme $theme */
            $theme = oxNew('oxtheme');
            $theme->load('azure');
            if (ThemeHelper::isOldAzureTheme()) {
                $argument = static::CHECKOUT_NEXT_STEP_TEXT_LEGACY;
            }
        }

        return str_replace('\\"', '"', $argument);
    }
}
