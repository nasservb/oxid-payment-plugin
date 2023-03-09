@pluginIsEnabled @setupPaymentMethods @javascript @resetSession @payments
Feature: Payment
  In order to use plugin payment methods
  As a customer
  I need to be able to place an order

  Background:
    # Open homepage so session reset happens on shop domain
    Given I am on the homepage
    Given I am on "/en/Kiteboarding/Kiteboards/Kiteboard-CABRINHA-CALIBER-2011.html"
    And I wait till element exists "#amountToBasket"
    And I fill in "amountToBasket" with "20"
    And I click on CSS locator "#toBasket"
    And I wait till element exists ".modal.fade.basketFlyout.in .btn-primary .fa.fa-shopping-cart"
    And I am on "/en/cart/"
    And I click on CSS locator "button.nextStep"
    And I choose purchase without registration
    And I wait till element exists "button.nextStep"
    And I fill in the following:
      | userLoginName               | autotest-plugin@example.com |
      | invadr[oxuser__oxfname]     | Stub                        |
      | invadr[oxuser__oxlname]     | User                        |
      | invadr[oxuser__oxstreet]    | Augsburger Strasse          |
      | invadr[oxuser__oxstreetnr]  | 120                         |
      | invadr[oxuser__oxzip]       | 10111                       |
      | invadr[oxuser__oxcity]      | Berlin                      |
    And I select "Germany" from country field "invCountrySelect"
    And I click on CSS locator "button.nextStep"
    And I select payment option "oxpe_paymill_directdebit"

  Scenario: Payment option exsists
    Then payment option "stripe" should be available
    Then payment option "stripe_directdebit" should be available
    Then payment option "paymill_directdebit" should be available
    Then payment option "paymill_creditcard" should be available
    Then payment option "sofort" should be available
    Then payment option "paypal" should be available
    Then payment option "santander_installment" should be available
    Then payment option "payex_creditcard" should be available
    Then payment option "santander_installment-1" should be available
    Then payment option "santander_installment-2" should be available
    Then payment option "payex_creditcard" should be available
    Then payment option "santander_invoice_de" should not be available
    Then payment option "santander_invoice_no" should not be available
    Then payment option "santander_installment_no" should not be available
    Then payment option "santander_installment_dk" should not be available
    Then payment option "santander_installment_se" should not be available
    Then payment option "payex_faktura" should not be available
    # Open homepage so session reset happens on shop domain
    Given I am on the homepage

  Scenario: Successful checkout
    Given I expect payment redirect url to be "success_url"
    And I expect payment status to be "STATUS_PAID"
    And I select payment option "oxpe_santander_installment-2"
    And I click on CSS locator "button.nextStep"
    And I press confirm order button
    And I wait till element exists "#thankyouPage"
    Then I should see "We registered your order"
    And new order should be created
    And new order status should be "OK"
    And the requests sequence contains:
      | path              | json_body                                                                                        |
      | ~/api/payment/~   |                                                                                                  |
      | ~/api/v2/payment~ |{"payment_method": "santander_installment", "variant_id": "6f636e95-f11d-40c4-8548-4e395adb4344"} |
    # Open homepage so session reset happens on shop domain
    Given I am on the homepage
    

  Scenario: Pending payment
    Given I expect payment redirect url to be "pending_url"
    And I expect payment status to be "STATUS_IN_PROCESS"
    And I click on CSS locator "button.nextStep"
    And I press confirm order button
    And I wait till element exists "#thankyouPage"
    Then I should see "We registered your order"
    Then I should see "Thank you, your order has been received. You will receive an update once your request has been processed."
    And new order should be created
    And new order status should be "IN_PROCESS"
    # Open homepage so session reset happens on shop domain
    Given I am on the homepage

  Scenario: Pending payment case expired session
    Given I expect payment redirect url to be "none"
    And I expect payment status to be "STATUS_IN_PROCESS"
    And I click on CSS locator "button.nextStep"
    And I press confirm order button
    And I remember redirect url "pending_url"
    # the step imitates session expiration
    And I restart session
    And I visit redirect url "pending_url"
    And new order should be created
    And new order status should be "IN_PROCESS"
    # Open homepage so session reset happens on shop domain
    Given I am on the homepage

  Scenario: Payment failed
    Given I expect payment redirect url to be "failure_url"
    And I expect payment status to be "STATUS_FAILED"
    And I click on CSS locator "button.nextStep"
    And I press confirm order button
    And I wait till element exists "#thankyouPage"
    Then I should see "The payment was not successful"
    And new order should not be created
    # Open homepage so session reset happens on shop domain
    Given I am on the homepage

  Scenario: Payment failed case expired session
    Given I expect payment redirect url to be "none"
    And I expect payment status to be "STATUS_FAILED"
    And I click on CSS locator "button.nextStep"
    And I press confirm order button
    And I remember redirect url "failure_url"
    # the step imitates session expiration
    And I restart session
    And I visit redirect url "failure_url"
    And new order should not be created
    # Open homepage so session reset happens on shop domain
    Given I am on the homepage

  Scenario: Payment canceled
    Given I expect payment redirect url to be "cancel_url"
    And I expect payment status to be "STATUS_FAILED"
    And I click on CSS locator "button.nextStep"
    And I press confirm order button
    And I wait till element exists "#thankyouPage"
    Then I should see "Payment was cancelled"
    And new order should not be created
    # Open homepage so session reset happens on shop domain
    Given I am on the homepage

  Scenario: Payment canceled case expired session
    Given I expect payment redirect url to be "none"
    And I expect payment status to be "STATUS_FAILED"
    And I click on CSS locator "button.nextStep"
    And I press confirm order button
    And I remember redirect url "cancel_url"
    # the step imitates session expiration
    And I restart session
    And I visit redirect url "cancel_url"
    And new order should not be created
    # Open homepage so session reset happens on shop domain
    Given I am on the homepage

  Scenario: Create order on notification & update status
    Given I expect payment redirect url to be "none"
    And I expect payment status to be "STATUS_IN_PROCESS"
    And I click on CSS locator "button.nextStep"
    And I press confirm order button
    And I send payment notification
    Then new order should be created
    And new order status should be "IN_PROCESS"
    Then I expect payment status to be "STATUS_PAID"
    And I send payment notification
    Then new order status should be "OK"
    # Open homepage so session reset happens on shop domain
    And I am on the homepage

  Scenario: Should reject stalled notification
    Given I expect payment redirect url to be "none"
    And I expect payment status to be "STATUS_IN_PROCESS"
    And I click on CSS locator "button.nextStep"
    And I press confirm order button
    And I send payment notification
    Then new order should be created
    And new order status should be "IN_PROCESS"
    Then I expect payment status to be "STATUS_PAID"
    And I send payment notification
    Then new order status should be "OK"
    And I send stalled payment notification and expect it to fail
    # Open homepage so session reset happens on shop domain
    And I am on the homepage

  Scenario: Create order on notification with signature & update status
    And I expect payment redirect url to be "none"
    And I expect payment status to be "STATUS_IN_PROCESS"
    And I click on CSS locator "button.nextStep"
    And I press confirm order button
    And I send payment notification with signature for "clientId" and "clientSecrect"
    Then new order should be created
    And new order status should be "IN_PROCESS"
    Then I expect payment status to be "STATUS_PAID"
    And I send payment notification with signature for "clientId" and "clientSecrect"
    Then new order status should be "OK"
    # Open homepage so session reset happens on shop domain
    And I am on the homepage

  Scenario: Create order on notification with invalid signature
    And I expect payment redirect url to be "none"
    And I expect payment status to be "STATUS_IN_PROCESS"
    And I click on CSS locator "button.nextStep"
    And I press confirm order button
    And I send payment notification with signature for "clientId" and "clientSecrect"
    Then new order should be created
    And new order status should be "IN_PROCESS"
    Then I expect payment status to be "STATUS_FAILED"
    And I send payment notification with invalid signature
    Then new order status should be "IN_PROCESS"
    # Open homepage so session reset happens on shop domain
    And I am on the homepage

  Scenario: Create order on stalled notification with signature
    And I expect payment redirect url to be "none"
    And I expect payment status to be "STATUS_IN_PROCESS"
    And I click on CSS locator "button.nextStep"
    And I press confirm order button
    And I send payment notification with signature for "clientId" and "clientSecrect"
    Then new order should be created
    And new order status should be "IN_PROCESS"
    Then I expect payment status to be "STATUS_PAID"
    And I send payment notification with signature for "clientId" and "clientSecrect"
    Then new order status should be "OK"
    And I send stalled payment notification with signature for "clientId" and "clientSecrect" and expect it to fail
    # Open homepage so session reset happens on shop domain
    And I am on the homepage