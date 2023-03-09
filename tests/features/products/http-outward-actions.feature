@pluginIsEnabled @cleanProducts @thirdPartySubscribed @resetSession @products
Feature: Executing outward product actions
  Background:
    Given I set plugin config "payeverProductsSyncMode" value to "instant"
    And I reset the requests storage
    # Open homepage so session reset happens on shop domain
    And I am on the homepage
    And I am on admin login page
    And I login to admin section
    And I wait till element exists "#navigation"
    And I switch to iframe "navigation"
    And I wait till element exists "#adminnav"
    And I switch to iframe "adminnav"
    And I wait till element exists "td.main"
    And I click on CSS locator "#nav-1-4 a"
    And I wait till element exists "li.exp"
    And I click on CSS locator "#nav-1-4-1 a"
    And I switch to root document
    And I switch to iframe "basefrm"

  Scenario: Create product
    Given I switch to iframe "edit"
    And I wait till element exists "#myedit"
    And I fill in the following:
      | editval[oxarticles__oxactive] | 1              |
      | editval[oxarticles__oxtitle]  | Inward product |
      | editval[oxarticles__oxartnum] | OUTWRD-1       |
      | editval[oxarticles__oxprice]  | 10             |
    When I press "Save"
    And I wait request stack exists and populated with size 5
    Then the requests sequence contains:
      | path              | method | json_body                                                                                                                                                                                                                                                                                                                                        |
      | ~/api/inventory~  | POST   |                                                                                                                                                                                                                                                                                                                                                  |
      | ~/api/product~    | PUT    | {"externalId":"*","images":[],"imagesUrl":[],"active":"*","categories":[],"currency":"EUR","title":"Inward product","description":"*","price":"*","onSales":false,"sku":"OUTWRD-1","type":"physical","variants":[],"shipping":{"measure_mass":"kg","measure_size":"*","free":false,"general":false,"weight":0,"width":0,"length":0,"height":0}}  |

  Scenario: Update product
    Given the product with SKU "PROD2" should not exist
    Given I expect the next third-party action to fail with status 200
    When I execute third-party action "create-product" for business "payever" with fixture "third-party/create-product"
    Then the product with SKU "PROD2" should exist
    Given I switch to iframe "list"
    And I wait till element exists "input.listedit"
    Then I fill in "where[oxarticles][oxartnum]" with "PROD2"
    And I press "Search"
    And I click on CSS locator "#search tbody td div a"
    And I reset the requests storage
    And I switch to root document
    And I switch to iframe "basefrm"
    And I switch to iframe "edit"
    And I wait till element exists "#myedit"
    And I fill in the following:
      | editval[oxarticles__oxtitle]  | Inward product updated |
      | editval[oxarticles__oxartnum] | OUTWRD-1               |
      | editval[oxarticles__oxprice]  | 10                     |
    And I switch to root document
    And I switch to iframe "basefrm"
    And I switch to iframe "list"
    And I follow "Extended"
    And I switch to root document
    And I switch to iframe "basefrm"
    And I switch to iframe "edit"
    And I wait till element exists "#myedit"
    And I fill in "editval[oxarticles__oxtprice]" with "15"
    When I press "Save"
    And I wait request stack exists and populated with size 3
    Then the requests sequence contains:
      | path             | method | json_body                                                                                                                                                                                                                                                                       |
      | ~/api/inventory~ | POST   |                                                                                                                                                                                                                                                                                 |
      | ~/api/product~   | PUT    | {"externalId":"*","images":[],"imagesUrl":[],"active":"*","categories":[],"currency":"EUR","title":"*","description":"*","price":15,"onSales":false,"sku":"*","type":"physical","variants":[],"shipping":{"measure_mass":"kg","measure_size":"*","free":false,"general":false}} |

  Scenario: Delete product
    Given the product with SKU "PROD2" should not exist
    Given I expect the next third-party action to fail with status 200
    When I execute third-party action "create-product" for business "payever" with fixture "third-party/create-product"
    Then the product with SKU "PROD2" should exist
    Given I switch to iframe "list"
    And I wait till element exists "input.listedit"
    Then I fill in "where[oxarticles][oxartnum]" with "PROD2"
    And I press "Search"
    And I reset the requests storage
    And I click on CSS locator "#search tbody td a.delete"
    And I confirm the popup
    And I wait request stack exists and populated with size 1
    Then the requests sequence contains:
      | path            | method |
      | ~/api/product~  | DELETE |
