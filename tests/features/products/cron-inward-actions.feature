@pluginIsEnabled @cleanProducts @thirdPartySubscribed @resetSession @products
Feature: Handling inward product actions
  Background:
    Given I set plugin config "payeverProductsSyncMode" value to "cron"

  Scenario Outline: Manage simple product
    Given the product with SKU "PROD2" should not exist
    And I clear products sync queue
    Given I execute third-party action "<initial_action>" for business "payever" with fixture "third-party/create-product"
    Then the product with SKU "PROD2" should not exist
    And I wait sync queue exists and populated with size 1
    Then I run products sync cronjob
    Then the product with SKU "PROD2" should exist
    And the product with SKU "PROD2" must have the following field values:
      | oxtitle     | Main Product 2             |
      | oxshortdesc | Main Product 2 description |
      | oxactive    | 1                          |
      | oxweight    | 21.000000                  |
      | oxwidth     | 0.05                       |
      | oxlength    | 0.05                       |
      | oxheight    | 0.05                       |
      | oxprice     | 400                        |
      | oxvat       | 19                         |
    But the product with SKU "PROD2" must have inventory quantity "0"
    And the product with SKU "PROD2" must be assigned to the category "Stub goods category 1"
    And I clear products sync queue
    When I execute third-party action "update-product" for business "payever" with fixture "third-party/create-product" and body:
      """
      {
        "active": false,
        "price": 390,
        "on_sales": true,
        "salePrice": 377,
        "title": "Main Product 2 Updated",
        "description": "Main Product 2 description updated"
      }
      """
    And I wait sync queue exists and populated with size 1
    Then I run products sync cronjob
    And the product with SKU "PROD2" must have the following field values:
      | oxtitle     | Main Product 2 Updated             |
      | oxshortdesc | Main Product 2 description updated |
      | oxactive    | 0                                  |
      | oxtprice    | 390                                |
      | oxprice     | 377                                |
    But the product with SKU "PROD2" must have inventory quantity "0"

    Examples:
      | initial_action |
      | create-product |
      # Create product on "update-product" without prior "create-product"
      | update-product |

  Scenario: Manage inventory for simple product
    Given the product with SKU "PROD2" should not exist
    And I clear products sync queue
    And I execute third-party action "create-product" for business "payever" with fixture "third-party/create-product"
    Then the product with SKU "PROD2" should not exist
    And I wait sync queue exists and populated with size 1
    Then I run products sync cronjob
    Then the product with SKU "PROD2" should exist
    But the product with SKU "PROD2" must have inventory quantity "0"
    And I clear products sync queue
    When I execute third-party action "set-inventory" for business "payever" with body:"
      """
      {
        "sku": "PROD2",
        "stock": 11
      }
      """
    Then the product with SKU "PROD2" must have inventory quantity "0"
    And I wait sync queue exists and populated with size 1
    Then I run products sync cronjob
    And the product with SKU "PROD2" must have inventory quantity "11"
    And I clear products sync queue
    When I execute third-party action "set-inventory" for business "payever" with body:"
      """
      {
        "sku": "PROD2",
        "stock": 15
      }
      """
    Then the product with SKU "PROD2" must have inventory quantity "11"
    And I wait sync queue exists and populated with size 1
    Then I run products sync cronjob
    Then the product with SKU "PROD2" must have inventory quantity "15"
    And I clear products sync queue
    When I execute third-party action "add-inventory" for business "payever" with body:"
      """
      {
        "sku": "PROD2",
        "stock": 19,
        "quantity": 4
      }
      """
    Then the product with SKU "PROD2" must have inventory quantity "15"
    And I wait sync queue exists and populated with size 1
    Then I run products sync cronjob
    Then the product with SKU "PROD2" must have inventory quantity "19"
    And I clear products sync queue
    When I execute third-party action "subtract-inventory" for business "payever" with body:"
      """
      {
        "sku": "PROD2",
        "stock": 0,
        "quantity": 19
      }
      """
    Then the product with SKU "PROD2" must have inventory quantity "19"
    And I wait sync queue exists and populated with size 1
    And I run products sync cronjob
    But the product with SKU "PROD2" must have inventory quantity "0"
    And I clear products sync queue
    When I execute third-party action "subtract-inventory" for business "payever" with body:"
      """
      {
        "sku": "PROD2",
        "stock": -1,
        "quantity": 1
      }
      """
    Then the product with SKU "PROD2" must have inventory quantity "0"
    And I wait sync queue exists and populated with size 1
    And I run products sync cronjob
    Then the product with SKU "PROD2" must have inventory quantity "-1"

  Scenario: Delete product with variants
    Given the product with SKU "PROD1" should not exist
    And I clear products sync queue
    When I execute third-party action "create-product" for business "payever" with fixture "third-party/create-product-with-variants"
    Then the product with SKU "PROD1" should not exist
    And I wait sync queue exists and populated with size 1
    And I run products sync cronjob
    Then the product with SKU "PROD1" should exist
    And the product with SKU "PROD1" must have the variant with SKU "PROD1-VAR1"
    And the product with SKU "PROD1" must have the variant with SKU "PROD1-VAR2"
    And I clear products sync queue
    When I execute third-party action "remove-product" for business "payever" with body:"
      """
      {
        "sku": "PROD1-VAR2"
      }
      """

    Then the product with SKU "PROD1" should exist
    And the product with SKU "PROD1" must have the variant with SKU "PROD1-VAR2"
    And the product with SKU "PROD1" must have the variant with SKU "PROD1-VAR1"
    And I wait sync queue exists and populated with size 1
    And I run products sync cronjob
    Then the product with SKU "PROD1" should exist
    And the product with SKU "PROD1" must not have the variant with SKU "PROD1-VAR2"
    And the product with SKU "PROD1" must have the variant with SKU "PROD1-VAR1"
    And I clear products sync queue
    When I execute third-party action "remove-product" for business "payever" with body:"
      """
      {
        "sku": "PROD1-VAR1"
      }
      """
    Then the product with SKU "PROD1" should exist
    And the product with SKU "PROD1" must not have the variant with SKU "PROD1-VAR2"
    And the product with SKU "PROD1" must have the variant with SKU "PROD1-VAR1"
    And I wait sync queue exists and populated with size 1
    And I run products sync cronjob
    Then the product with SKU "PROD1" should exist
    And the product with SKU "PROD1" must not have the variant with SKU "PROD1-VAR2"
    And the product with SKU "PROD1" must not have the variant with SKU "PROD1-VAR1"
    And I clear products sync queue
    When I execute third-party action "remove-product" for business "payever" with body:"
      """
      {
        "sku": "PROD1"
      }
      """
    Then the product with SKU "PROD1" should exist
    And the product with SKU "PROD1" must not have the variant with SKU "PROD1-VAR2"
    And the product with SKU "PROD1" must not have the variant with SKU "PROD1-VAR1"
    And I wait sync queue exists and populated with size 1
    And I run products sync cronjob
    Then the product with SKU "PROD1" should not exist
