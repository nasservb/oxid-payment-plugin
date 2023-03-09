@pluginIsEnabled @thirdPartySubscribed @javascript @resetSession @products
Feature: Product and inventory manual export

  Scenario: Export products
    Given I am on admin login page
    And I login to admin section
    And I reset the requests storage
    And I wait till element exists "#navigation"
    And I switch to iframe "navigation"
    And I wait till element exists "#adminnav"
    And I switch to iframe "adminnav"
    And I wait till element exists "td.main"
    And I follow "Payever"
    And I follow "General configuration"
    And I switch to root document
    And I switch to iframe "basefrm"
    And I wait till element exists "#myedit"
    And I press export products button
    And I wait request stack exists and populated with size 11
    Then the requests sequence contains:
      | path             | method |
      | ~/api/inventory~ | POST   |
      | ~/api/product~   | PUT    |
    # Open homepage so session reset happens on shop domain
    Given I am on the homepage

  Scenario: Disable sync on products export error
    Given I expect third-party product actions to be forbidden
    And I am on admin login page
    And I login to admin section
    And I reset the requests storage
    And I wait till element exists "#navigation"
    And I switch to iframe "navigation"
    And I wait till element exists "#adminnav"
    And I switch to iframe "adminnav"
    And I wait till element exists "td.main"
    And I follow "Payever"
    And I follow "General configuration"
    And I switch to root document
    And I switch to iframe "basefrm"
    And I wait till element exists "#myedit"
    And I press export products button
    And I wait request stack exists and populated with size 5
    Then plugin config "payeverProductsSyncEnabled" value must be equal to "0"
    Given I expect third-party product actions to be allowed
    # Open homepage so session reset happens on shop domain
    Given I am on the homepage
