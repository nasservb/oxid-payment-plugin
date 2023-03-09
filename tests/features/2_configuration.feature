@pluginIsEnabled @javascript @resetSession @baseScenarios
Feature: Configuration
  In order to use plugin
  As merchant
  I need to be able to manage plugin configuration

  Scenario: Setup sandbox keys and synchronize
    Given I set plugin config "clientId" value to ""
    Given I set plugin config "clientSecrect" value to ""
    Given I set plugin config "slug" value to ""
    And I am on admin login page
    And I login to admin section
    And I wait till element exists "#navigation"
    And I switch to iframe "navigation"
    And I switch to iframe "adminnav"
    And I follow "Payever"
    And I follow "General configuration"
    And I switch to root document
    And I switch to iframe "basefrm"
    And I wait till element exists "#myedit"
    And I wait till element exists ".payever-embedded-support"
    Then I should see the following:
      | title                             |
      | Need help? Chat with us!          |
      | API Credentials                   |
      | Appearance and behaviour          |
      | Choose the mode                   |
      | Client ID                         |
      | Client Secret                     |
      | Business UUID                     |
      | Default language on checkout      |
      | Display payment icon              |
      | Display payment description       |
      | Redirect to payever               |
      | Display "Reference" in order grid |
      | Logging level                     |
    And I press "setApiKeys"
    And I confirm the popup
    Then I should see "Sandbox API keys was set up successfully"
    Then the "payever_config[slug]" field should contain "payever"
    And I press "Synchronize Settings"
    And I confirm the popup
    And I wait till element exists ".payever-message-container p"
    Then I should see "Settings synchronize success"
    And the following payment options should be active:
      | key                        |
      | stripe                     |
      | stripe_directdebit         |
      | sofort                     |
      | paymill_directdebit        |
      | paymill_creditcard         |
      | santander_installment      |
      | santander_installment-1    |
      | santander_installment-2    |
      | santander_installment_no   |
      | santander_installment_no-1 |
      | santander_installment_dk   |
      | santander_installment_dk-1 |
      | santander_installment_dk-2 |
      | santander_installment_se   |
      | santander_installment_se-1 |
      | santander_factoring_de     |
      | santander_invoice_de       |
      | santander_invoice_de-1     |
      | santander_invoice_no       |
      | santander_invoice_no-1     |
      | paypal                     |
      | payex_faktura              |
      | payex_creditcard           |
    And the requests sequence contains:
      | path                                                    | method |
      | ~/api/shop/oauth/payever/payment-options/variants/oxid~ | GET    |
    Given I connect payment methods to shipping method

  Scenario: Redirect & iFrame, display configs: positive
    Given I set plugin config "redirectToPayever" value to "1"
    And I set plugin config "displayBasketId" value to "1"
    And I set plugin config "displayPaymentIcon" value to "1"
    And I set plugin config "displayPaymentDescription" value to "1"
    Given I am on "/en/Kiteboarding/Kiteboards/Kiteboard-CABRINHA-CALIBER-2011.html"
    And I wait till element exists "#amountToBasket"
    And I fill in "amountToBasket" with "3"
    # Old OXID template has animation overlay over basket button - wait for it
    And I wait 4 seconds
    And I press "toBasket"
    And I am on "/en/cart/"
    And I click on selector "button.nextStep"
    And I choose purchase without registration
    And I wait till element exists "#userLoginName"
    And I fill in the following:
      | userLoginName               | autotest-plugin@example.com |
      | invadr[oxuser__oxfname]     | Stub                        |
      | invadr[oxuser__oxlname]     | User                        |
      | invadr[oxuser__oxstreet]    | Augsburger Strasse          |
      | invadr[oxuser__oxstreetnr]  | 120                         |
      | invadr[oxuser__oxzip]       | 10111                       |
      | invadr[oxuser__oxcity]      | Berlin                      |
    And I select "Germany" from country field "invCountrySelect"
    And I click on selector "button.nextStep"
    # displayPaymentIcon
    And I wait till element exists ".payever-payment-icon"
    Then I should see payever payment option icons
    # checkPaymentIconsPath
    Then I check payment icons path
    # displayPaymentDescription
    Then I should see payever payment option descriptions
    And I select payment option "oxpe_santander_invoice_de"
    And I expect payment redirect url to be "none"
    And I click on selector "button.nextStep"
    And I press confirm order button
    # redirectToPayever
    Then I should not see payever iframe
    And I am on admin login page
    And I login to admin section
    And I switch to iframe "basefrm"
    And I follow "Orders"
    And I switch to iframe "list"
    # displayBasketId
    Then I should see "Reference"

  Scenario: Redirect & iFrame, display configs: negative
    Given I set plugin config "redirectToPayever" value to "0"
    And I set plugin config "displayBasketId" value to "0"
    And I set plugin config "displayPaymentIcon" value to "0"
    And I set plugin config "displayPaymentDescription" value to "0"
    Given I am on "/en/Kiteboarding/Kiteboards/Kiteboard-CABRINHA-CALIBER-2011.html"
    And I wait till element exists "#amountToBasket"
    And I fill in "amountToBasket" with "3"
    # Old OXID template has animation overlay over basket button - wait for it
    And I wait 4 seconds
    And I press "toBasket"
    And I am on "/en/cart/"
    And I click on selector "button.nextStep"
    And I choose purchase without registration
    And I fill in the following:
      | userLoginName               | autotest-plugin@example.com |
      | invadr[oxuser__oxfname]     | Stub                        |
      | invadr[oxuser__oxlname]     | User                        |
      | invadr[oxuser__oxstreet]    | Augsburger Strasse          |
      | invadr[oxuser__oxstreetnr]  | 120                         |
      | invadr[oxuser__oxzip]       | 10111                       |
      | invadr[oxuser__oxcity]      | Berlin                      |
    And I select "Germany" from country field "invCountrySelect"
    And I click on selector "button.nextStep"
    # displayPaymentIcon
    Then I should not see payever payment option icons
    # displayPaymentDescription
    Then I should not see payever payment option descriptions
    And I select payment option "oxpe_santander_invoice_de"
    And I expect payment redirect url to be "none"
    And I click on selector "button.nextStep"
    And I press confirm order button
    # redirectToPayever
    Then I wait till element exists "#payever_iframe"
    And I am on admin login page
    And I login to admin section
    And I switch to iframe "basefrm"
    And I wait till element exists "table"
    And I follow "Orders"
    And I switch to iframe "list"
    # displayBasketId
    Then I should not see "Reference"
    # Open homepage so session reset happens on shop domain
    Given I am on the homepage
