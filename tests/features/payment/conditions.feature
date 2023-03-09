@pluginIsEnabled @setupPaymentMethods @javascript @resetSession @payments
Feature: Conditions
  In order to make checkout user-friendly
  As merchant
  I need payment options to be visible only in suitable for customer conditions

  Scenario Outline: Conditions
    # Open homepage so session reset happens on shop domain
    Given I am on the homepage
    Given I am on "/en/Kiteboarding/Kiteboards/Kiteboard-CABRINHA-CALIBER-2011.html"
    And I switch to "<currency>" currency
    And I wait till element exists "#amountToBasket"
    And I fill in "amountToBasket" with "<qty>"
    And I click on CSS locator "#toBasket"
    And I wait till element exists ".modal.fade.basketFlyout.in .btn-primary .fa.fa-shopping-cart"
    And I am on "/en/cart/"
    And I wait till element exists "button.nextStep"
    And I click on selector "button.nextStep"
    And I choose purchase without registration
    And I wait till element exists "button.nextStep"
    And I fill in the following:
      | userLoginName               | autotest-plugin@example.com |
      | invadr[oxuser__oxfname]     | Stub                        |
      | invadr[oxuser__oxlname]     | User                        |
      | invadr[oxuser__oxstreet]    | Augsburger Strasse          |
      | invadr[oxuser__oxstreetnr]  | 120                         |
      | invadr[oxuser__oxzip]       | <zip>                       |
      | invadr[oxuser__oxcity]      | <city>                      |
    And I select "<country>" from country field "invCountrySelect"
    And I click on selector "button.nextStep"
    And I wait till element exists "button.nextStep"
    Then payment option "<payment_option_1>" should be available
    Then payment option "<payment_option_2>" should be available
    Then payment option "<no_payment_option_1>" should not be available
    Then payment option "<no_payment_option_2>" should not be available
    Then payment option "<no_payment_option_3>" should not be available
    # Open homepage so session reset happens on shop domain
    Given I am on the homepage

    Examples:
      | currency | qty | city       | zip    | country | payment_option_1         | payment_option_2   | no_payment_option_1      | no_payment_option_2      | no_payment_option_3      |
      | EUR      | 20  | Berlin     | 10111  | Germany | santander_installment    | sofort             | santander_installment_dk | santander_installment_se | santander_installment_no |
      | EUR      | 1   | Berlin     | 10111  | Germany | santander_invoice_de     | sofort             | santander_installment    | santander_installment_se | santander_installment_no |
      | DKK      | 40  | Copenhagen | 1100   | Denmark | santander_installment_dk | payex_creditcard   | santander_invoice_de     | santander_installment_se | santander_installment_no |
      | SEK      | 2   | Stockholm  | 114 55 | Sweden  | santander_installment_se | payex_faktura      | santander_invoice_de     | santander_installment_dk | santander_installment_no |
      | NOK      | 50  | Oslo       | 1400   | Norway  | santander_installment_no | paymill_creditcard | santander_invoice_de     | santander_installment_dk | santander_installment_se |

  Scenario Outline: Hide after failure & on different addresses
    # Open homepage so session reset happens on shop domain
    Given I am on the homepage
    Given I am on "/en/Kiteboarding/Kiteboards/Kiteboard-CABRINHA-CALIBER-2011.html"
    And I wait till element exists "#amountToBasket"
    And I fill in "amountToBasket" with "<qty>"
    And I click on CSS locator "#toBasket"
    And I wait till element exists ".modal.fade.basketFlyout.in .btn-primary .fa.fa-shopping-cart"
    And I am on "/en/cart/"
    And I wait till element exists "button.nextStep"
    And I click on selector "button.nextStep"
    And I wait till element exists "#optionNoRegistration button"
    And I choose purchase without registration
    And I wait till element exists "button.nextStep"
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
    And I wait 5 seconds
    And I uncheck "showShipAddress"
    And I fill in the following:
      | deladr[oxaddress__oxfname]    | Stub            |
      | deladr[oxaddress__oxlname]    | User            |
      | deladr[oxaddress__oxstreet]   | Another Strasse |
      | deladr[oxaddress__oxstreetnr] | 120             |
      | deladr[oxaddress__oxzip]      | 10122           |
      | deladr[oxaddress__oxcity]     | Berlin          |
    And I select "Germany" from country field "delCountrySelect"
    And I click on selector "button.nextStep"
    # Validate payment options are hidden on different billing/shipping addresses
    Then payment option "<payment_option>" should not be available
    # Select latest method from list in order to scroll the page below
    And I select payment option "oxidinvoice"
    And I follow "paymentBackStepBottom"
    And I check "showShipAddress"
    And I click on selector "button.nextStep"
    # Validate payment options are visible on same billing/shipping addresses
    And I wait till element exists "#paymentHeader"
    Then payment option "<payment_option>" should be available
    # Hide on failure part
    And I select payment option "oxpe_<payment_option>"
    And I expect payment redirect url to be "failure_url"
    And I expect payment status to be "STATUS_FAILED"
    And I click on selector "button.nextStep"
    And I press confirm order button
    And I wait 5 seconds
    Then I should see "Unfortunately, the application was not successful. Please choose another payment option to pay for your order."
    And new order should not be created
    And payment option "<payment_option>" should not be available
    # Open homepage so session reset happens on shop domain
    Given I am on the homepage

    Examples:
      | qty | payment_option       |
      | 1   | santander_invoice_de |
