@pluginIsEnabled @cleanProducts @thirdPartySubscribed @resetSession @products
Feature: Handling inward product actions
  Background:
    Given I set plugin config "payeverProductsSyncMode" value to "instant"

  Scenario Outline: Manage simple product
    Given the product with SKU "PROD2" should not exist
    Given I expect the next third-party action to fail with status 200
    Given I execute third-party action "<initial_action>" for business "payever" with fixture "third-party/create-product"
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
      | update-product |

  Scenario: Open product detail page
    Given the product with SKU "PROD2" should not exist
    Given I execute third-party action "create-product" for business "payever" with fixture "third-party/create-product"
    Then the product with SKU "PROD2" should exist
    And I open the product with SKU "PROD2" detail page
    Then I should see "Main Product 2"

  Scenario: Convert price to the base currency by payever rates
    Given the product with SKU "PROD2" should not exist
    Given I execute third-party action "create-product" for business "payever" with fixture "third-party/create-product" and body:
      """
      {
        "currency": "NOK",
        "price": 5000,
        "on_sales": false
      }
      """
    Then the product with SKU "PROD2" should exist
    And I wait request stack exists and populated with size 1
    And the last requests sequence equals to:
      | method | path                  |
      | GET    | /api/rest/v1/currency |
    And the product with SKU "PROD2" must have the following field values:
      | oxprice  | 526.27 |
    Then I execute third-party action "create-product" for business "payever" with fixture "third-party/create-product" and body:
      """
      {
        "currency": "NOK",
        "price": 5000,
        "on_sales": true,
        "salePrice": 3900
      }
      """
    And the last requests sequence equals to:
      | method | path                    |
      | GET    | ~/api/rest/v1/currency~ |
    And the product with SKU "PROD2" must have the following field values:
      | oxtprice  | 526.27 |
      | oxprice   | 410.49 |

  Scenario: Manage product with variants
    Given the product with SKU "PROD1" should not exist
    And I execute third-party action "create-product" for business "payever" with fixture "third-party/create-product-with-variants"
    Then the product with SKU "PROD1" should exist
    And the product with SKU "PROD1" must be assigned to the following categories:
      | Stub goods category 1 |
      | Stub goods category 2 |
    But the product with SKU "PROD1" must have inventory quantity "0"
    And the product with SKU "PROD1" must have the following field values:
      | oxtitle     | Main Product 1             |
      | oxshortdesc | Main Product 1 description |
      | oxactive    | 1                          |
      | oxweight    | 10.000000                  |
      | oxwidth     | 0.08                       |
      | oxlength    | 0.07                       |
      | oxheight    | 0.06                       |
      | oxprice     | 0                          |
      | oxvat       | 19                         |
    Then the product with SKU "PROD1" must have the variant with SKU "PROD1-VAR1"
    And the product with SKU "PROD1-VAR1" must have inventory quantity "0"
    And the product with SKU "PROD1-VAR1" must have the following field values:
      | oxtitle     | Variant 1             |
      | oxshortdesc | Variant 1 description |
      | oxactive    | 1                     |
      | oxtprice    | 700                   |
      | oxprice     | 600                   |
      | oxvat       | 19                    |
    And the product variant with SKU "PROD1-VAR1" must have the following option values:
      | Attr 1 | Attr 1 value 1 |
      | Attr 2 | Attr 2 value 3 |
      | Attr 3 | Attr 3 value 5 |
    And I open the product with SKU "PROD1-VAR1" detail page
    And I should see the following:
      | title              |
      | Variant 1          |
      | Attr 1             |
      | Attr 2             |
      | Attr 3             |
      | Attr 1 value 1     |
      | Attr 2 value 3     |
      | Attr 3 value 5     |

  Scenario: Manage inventory for simple product
    Given the product with SKU "PROD2" should not exist
    And I execute third-party action "create-product" for business "payever" with fixture "third-party/create-product"
    Then the product with SKU "PROD2" should exist
    But the product with SKU "PROD2" must have inventory quantity "0"
    When I execute third-party action "set-inventory" for business "payever" with body:"
      """
      {
        "sku": "PROD2",
        "stock": 11
      }
      """
    Then the product with SKU "PROD2" must have inventory quantity "11"
    When I execute third-party action "set-inventory" for business "payever" with body:"
      """
      {
        "sku": "PROD2",
        "stock": 15
      }
      """
    Then the product with SKU "PROD2" must have inventory quantity "15"
    When I execute third-party action "add-inventory" for business "payever" with body:"
      """
      {
        "sku": "PROD2",
        "stock": 19,
        "quantity": 4
      }
      """
    Then the product with SKU "PROD2" must have inventory quantity "19"
    When I execute third-party action "subtract-inventory" for business "payever" with body:"
      """
      {
        "sku": "PROD2",
        "stock": 0,
        "quantity": 19
      }
      """
    Then the product with SKU "PROD2" must have inventory quantity "0"
    When I execute third-party action "subtract-inventory" for business "payever" with body:"
      """
      {
        "sku": "PROD2",
        "stock": -1,
        "quantity": 1
      }
      """
    Then the product with SKU "PROD2" must have inventory quantity "-1"

  Scenario: Create inventory on "add-inventory" without prior "set-inventory"
    Given the product with SKU "PROD2" should not exist
    And I execute third-party action "create-product" for business "payever" with fixture "third-party/create-product"
    Then the product with SKU "PROD2" should exist
    But the product with SKU "PROD2" must have inventory quantity "0"
    When I execute third-party action "add-inventory" for business "payever" with body:"
      """
      {
        "sku": "PROD2",
        "stock": 19,
        "quantity": 4
      }
      """
    Then the product with SKU "PROD2" must have inventory quantity "19"

  Scenario: Create inventory on "subtract-inventory" without prior "set-inventory"
    Given the product with SKU "PROD2" should not exist
    And I execute third-party action "create-product" for business "payever" with fixture "third-party/create-product"
    Then the product with SKU "PROD2" should exist
    But the product with SKU "PROD2" must have inventory quantity "0"
    When I execute third-party action "subtract-inventory" for business "payever" with body:"
      """
      {
        "sku": "PROD2",
        "stock": 18,
        "quantity": 5
      }
      """
    Then the product with SKU "PROD2" must have inventory quantity "18"

  Scenario: Manage inventory for a product with variants
    Given the product with SKU "PROD1" should not exist
    And I execute third-party action "create-product" for business "payever" with fixture "third-party/create-product-with-variants"
    Then the product with SKU "PROD1" should exist
    But the product with SKU "PROD1" must have inventory quantity "0"
    And the product with SKU "PROD1" must have the variant with SKU "PROD1-VAR1"
    And the product with SKU "PROD1-VAR1" must have inventory quantity "0"
    And the product with SKU "PROD1" must have the variant with SKU "PROD1-VAR2"
    And the product with SKU "PROD1-VAR2" must have inventory quantity "0"
    When I execute third-party action "set-inventory" for business "payever" with body:"
      """
      {
        "sku": "PROD1-VAR1",
        "stock": 20
      }
      """
    Then the product with SKU "PROD1-VAR1" must have inventory quantity "20"
    And the product with SKU "PROD1-VAR2" must have inventory quantity "0"
    When I execute third-party action "set-inventory" for business "payever" with body:"
      """
      {
        "sku": "PROD1-VAR2",
        "stock": 25
      }
      """
    Then the product with SKU "PROD1-VAR1" must have inventory quantity "20"
    And the product with SKU "PROD1-VAR2" must have inventory quantity "25"
    When I execute third-party action "subtract-inventory" for business "payever" with body:"
      """
      {
        "sku": "PROD1-VAR1",
        "stock": 5,
        "quantity": 15
      }
      """
    Then the product with SKU "PROD1-VAR1" must have inventory quantity "5"
    When I execute third-party action "subtract-inventory" for business "payever" with body:"
      """
      {
        "sku": "PROD1-VAR2",
        "stock": 10,
        "quantity": 15
      }
      """
    Then the product with SKU "PROD1-VAR2" must have inventory quantity "10"
    When I execute third-party action "add-inventory" for business "payever" with body:"
      """
      {
        "sku": "PROD1-VAR1",
        "stock": 25,
        "quantity": 20
      }
      """
    Then the product with SKU "PROD1-VAR1" must have inventory quantity "25"
    When I execute third-party action "add-inventory" for business "payever" with body:"
      """
      {
        "sku": "PROD1-VAR2",
        "stock": 40,
        "quantity": 30
      }
      """
    Then the product with SKU "PROD1-VAR2" must have inventory quantity "40"

  Scenario: Delete simple product
    Given the product with SKU "PROD2" should not exist
    Given I execute third-party action "create-product" for business "payever" with fixture "third-party/create-product"
    Then the product with SKU "PROD2" should exist
    And I execute third-party action "remove-product" for business "payever" with body:"
      """
      {
        "sku": "PROD2"
      }
      """
    Then the product with SKU "PROD2" should not exist

  Scenario: Delete product with variants
    Given the product with SKU "PROD1" should not exist
    Given I execute third-party action "create-product" for business "payever" with fixture "third-party/create-product-with-variants"
    Then the product with SKU "PROD1" should exist
    And the product with SKU "PROD1" must have the variant with SKU "PROD1-VAR1"
    And the product with SKU "PROD1" must have the variant with SKU "PROD1-VAR2"
    When I execute third-party action "remove-product" for business "payever" with body:"
      """
      {
        "sku": "PROD1-VAR2"
      }
      """
    Then the product with SKU "PROD1" should exist
    And the product with SKU "PROD1" must not have the variant with SKU "PROD1-VAR2"
    And the product with SKU "PROD1" must have the variant with SKU "PROD1-VAR1"
    When I execute third-party action "remove-product" for business "payever" with body:"
      """
      {
        "sku": "PROD1-VAR1"
      }
      """
    Then the product with SKU "PROD1" should exist
    And the product with SKU "PROD1" must not have the variant with SKU "PROD1-VAR2"
    And the product with SKU "PROD1" must not have the variant with SKU "PROD1-VAR1"
    When I execute third-party action "remove-product" for business "payever" with body:"
      """
      {
        "sku": "PROD1"
      }
      """
    Then the product with SKU "PROD1" should not exist
