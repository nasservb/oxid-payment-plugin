@pluginIsEnabled @setupPaymentMethods @javascript @resetSession @payments
Feature: Finance express widget
  In order to use finance express widget
  As a customer
  I need to be able to place an order

  Background:
    Given I am on "/en/Kiteboarding/Kiteboards/Kiteboard-CABRINHA-CALIBER-2011.html"
    And I switch to "EUR" currency
    And I expect reference from stub product

  Scenario: Successful finance express order
    Given I reset the requests storage
    Given I expect payment id to be "some-payment-id-1"
    And I create payment with amount "53.90"
    And I expect payment status to be "STATUS_ACCEPTED"
    And I send callback by url "/?cl=payeverExpressDispatcher&fnc=payeverWidgetSuccess&payment_id=some-payment-id-1"
    Then new order should be created
    And new order status should be "OK"
    And I send callback by url "/?cl=payeverExpressDispatcher&fnc=payeverWidgetSuccess&payment_id=some-payment-id-1"
    And new order should not be created
    And the last requests sequence equals to:
      | path                           | method |
      | /api/payment/some-payment-id-1 | get    |
      | /oauth/v2/token                | post   |


  Scenario: Try to create finance express order with wrong amount
    Given I expect payment id to be "some-payment-id-2"
    And I create payment with amount "53"
    And I expect payment status to be "STATUS_ACCEPTED"
    And I send callback by url "/?cl=payeverExpressDispatcher&fnc=payeverWidgetSuccess&payment_id=some-payment-id-2"
    Then I should see "The amount really paid (53.00 EUR) is not equal to the product amount (53.90 EUR)."
    And new order should not be created

  Scenario: Try to create finance express order with new status
    Given I expect payment id to be "some-payment-id-3"
    And I create payment with amount "53.90"
    And I expect payment status to be "STATUS_NEW"
    And I send callback by url "/?cl=payeverExpressDispatcher&fnc=payeverWidgetSuccess&payment_id=some-payment-id-3"
    Then I should see "The payment hasn't been successful"
    And new order should not be created

  Scenario: Successful finance express order with pending status
    Given I reset the requests storage
    And I expect payment id to be "some-payment-id-4"
    And I create payment with amount "53.90"
    And I expect payment status to be "STATUS_IN_PROCESS"
    And I send callback by url "/?cl=payeverExpressDispatcher&fnc=payeverWidgetSuccess&payment_id=some-payment-id-4"
    Then new order should be created
    And new order status should be "IN_PROCESS"

  Scenario: Failure finance express payment
    Given I reset the requests storage
    And I expect payment id to be "some-payment-id-5"
    And I create payment with amount "53.90"
    And I expect payment status to be "STATUS_DECLINED"
    And I send callback by url "/?cl=payeverExpressDispatcher&fnc=payeverWidgetFailure&payment_id=some-payment-id-5"
    Then I should see "The payment hasn't been successful"
    And new order should not be created

  Scenario: Successful finance express payment notification
    Given I expect payment id to be "some-payment-id-6"
    And I expect notice_url to be "/?cl=payeverExpressDispatcher&fnc=payeverWidgetNotice&payment_id=some-payment-id-6"
    And I create payment with amount "53.90"
    And I expect payment status to be "STATUS_IN_PROCESS"
    And I send payment notification for payment "some-payment-id-6"
    And new order should be created
    And new order status should be "IN_PROCESS"
    Then I expect payment status to be "STATUS_PAID"
    And I send payment notification for payment "some-payment-id-6"
    And new order should not be created
    And new order status should be "OK"
    # Should reject stalled notification
    And I send stalled payment notification for payment "some-payment-id-6" and expect it to fail
