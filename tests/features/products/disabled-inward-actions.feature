@pluginIsEnabled @cleanProducts @thirdPartySubscribed @resetSession @products
Feature: Handling inward product actions
  Background:
    Given I set plugin config "payeverProductsSyncEnabled" value to "0"

  Scenario Outline: Manage simple product
    Given the product with SKU "PROD2" should not exist
    Given I expect the next third-party action to fail with status 400
    Given I execute third-party action "<initial_action>" for business "payever" with fixture "third-party/create-product"
    Then the product with SKU "PROD2" should not exist
    Examples:
      | initial_action |
      | create-product |
      | update-product |

  Scenario: Manage product with variants
    Given the product with SKU "PROD1" should not exist
    Given I expect the next third-party action to fail with status 400
    And I execute third-party action "create-product" for business "payever" with fixture "third-party/create-product-with-variants"
    Then the product with SKU "PROD1" should not exist

  Scenario: Manage inventory for simple product
    Given the product with SKU "PROD2" should not exist
    Given I expect the next third-party action to fail with status 400
    And I execute third-party action "create-product" for business "payever" with fixture "third-party/create-product"
    Then the product with SKU "PROD2" should not exist
    Given I expect the next third-party action to fail with status 400
    When I execute third-party action "set-inventory" for business "payever" with body:"
      """
      {
        "sku": "PROD2",
        "stock": 11
      }
      """
    Given I expect the next third-party action to fail with status 400
    When I execute third-party action "add-inventory" for business "payever" with body:"
      """
      {
        "sku": "PROD2",
        "stock": 19,
        "quantity": 4
      }
      """
    Given I expect the next third-party action to fail with status 400
    When I execute third-party action "subtract-inventory" for business "payever" with body:"
      """
      {
        "sku": "PROD2",
        "stock": 0,
        "quantity": 19
      }
      """

  Scenario: Delete simple product
    Then the product with SKU "PROD2" should not exist
    Given I expect the next third-party action to fail with status 400
    And I execute third-party action "remove-product" for business "payever" with body:"
      """
      {
        "sku": "PROD2"
      }
      """
    Then the product with SKU "PROD2" should not exist
