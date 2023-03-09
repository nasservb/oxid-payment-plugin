@pluginIsEnabled @setupPaymentMethods @javascript @resetSession @v4 @v65 @payments
Feature: Events
  In order to provide better experience for merchant
  We need to track particular order events

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
    And I click on selector "button.nextStep"
    And I select payment option "oxpe_santander_installment"
    And I wait 1 seconds
    Given I expect payment redirect url to be "success_url"
    And I expect payment status to be "STATUS_PAID"
    And I click on selector "button.nextStep"
    And I press confirm order button
    Then I should see "We registered your order"
    And new order should be created
    And new order status should be "OK"
    And I am on admin login page
    And I login to admin section
    And I switch to iframe "navigation"
    And I switch to iframe "adminnav"
    And I wait till element exists "#nav"
    And I follow "Administer Orders"
    And I wait 1 seconds
    And I follow "Orders"
    And I wait 1 seconds

  @v4 @v65
  Scenario: Order canceled event is enabled
    And I expect payment action "cancel" to be "allowed"
    And I expect payment action "refund" to be "disallowed"
    And I switch to root document
    And I switch to iframe "basefrm"
    And I switch to iframe "list"
    And I follow "pau.1"
    And I confirm the popup
    And I switch to root document
    And I switch to iframe "basefrm"
    And I switch to iframe "edit"
    Then I should see "Order is canceled"
    And the last requests sequence equals to:
      | path                         |
      | ~/api/payment/cancel/~       |
      | ~/api/rest/v1/transactions/~ |
    # Open homepage so session reset happens on shop domain
    Given I am on the homepage

  @v65
  Scenario: Order shipped event is enabled
    And I expect payment action "shipping_goods" to be "allowed"
    And I switch to root document
    And I switch to iframe "basefrm"
    And I switch to iframe "list"
    And I select the top order
    And I switch to root document
    And I switch to iframe "basefrm"
    And I switch to iframe "edit"
    And I press "Ship Now"
    And I wait 3 seconds
    Then I should see "Shipped on"
    And the last requests sequence equals to:
      | path                           |
      | ~/api/payment/shipping-goods/~ |
      | ~/api/rest/v1/transactions/~   |
    # Open homepage so session reset happens on shop domain
    Given I am on the homepage

  @v4 @v65
  Scenario: Order canceled event is disabled
    And I expect payment action "cancel" to be "disallowed"
    And I expect payment action "refund" to be "disallowed"
    And I switch to root document
    And I switch to iframe "basefrm"
    And I switch to iframe "list"
    And I follow "pau.1"
    And I confirm the popup
    And I switch to root document
    And I switch to iframe "basefrm"
    And I switch to iframe "edit"
    Then I should see "Order is canceled"
    And the last requests sequence equals to:
      | path                         |
      | ~/api/rest/v1/transactions/~ |
    # Open homepage so session reset happens on shop domain
    Given I am on the homepage

  @v4 @v65
  Scenario: Order shipped event is disabled
    And I expect payment action "shipping_goods" to be "disallowed"
    And I switch to root document
    And I switch to iframe "basefrm"
    And I switch to iframe "list"
    And I select the top order
    And I switch to root document
    And I switch to iframe "basefrm"
    And I switch to iframe "edit"
    And I press "Ship Now"
    Then I should see "Shipped on"
    And the last requests sequence equals to:
      | path                         |
      | ~/api/rest/v1/transactions/~ |
    # Open homepage so session reset happens on shop domain
    Given I am on the homepage

  @v4 @v65
  Scenario: Order canceled event is disabled but refund event is enabled
    And I expect payment action "cancel" to be "disallowed"
    And I expect payment action "refund" to be "allowed"
    And I switch to root document
    And I switch to iframe "basefrm"
    And I switch to iframe "list"
    And I follow "pau.1"
    And I confirm the popup
    And I switch to root document
    And I switch to iframe "basefrm"
    And I switch to iframe "edit"
    Then I should see "Order is canceled"
    And the requests sequence contains:
      | path                       |
      | */api/payment/refund/*     |
    # Open homepage so session reset happens on shop domain
    Given I am on the homepage
