@pluginIsEnabled @cleanProducts @thirdPartySubscribed @resetSession @products
Feature: Omit executing outward product actions when sync is disabled
  Background:
    Given I set plugin config "payeverProductsSyncEnabled" value to "0"
    And I reset the requests storage
    # Open homepage so session reset happens on shop domain
    And I am on the homepage
    And I am on admin login page
    And I login to admin section
    And I switch to iframe "navigation"
    And I switch to iframe "adminnav"
    And I wait till element exists "td.main"
    And I click on CSS locator "#nav-1-4 a"
    And I wait till element exists "li.exp"
    And I click on CSS locator "#nav-1-4-1 a"
    And I switch to root document
    And I switch to iframe "basefrm"
    And I switch to iframe "edit"
    And I wait till element exists "#myedit"
    And I fill in the following:
      | editval[oxarticles__oxactive] | 1              |
      | editval[oxarticles__oxtitle]  | Inward product |
      | editval[oxarticles__oxartnum] | OUTWRD-1       |
      | editval[oxarticles__oxprice]  | 10             |

  Scenario: Create product
    Given I press "Save"
    And the request stack should be empty

  Scenario: Update product
    Given I press "Save"
    And the request stack should be empty
    And I switch to root document
    And I switch to iframe "basefrm"
    Given I switch to iframe "list"
    And I wait till element exists "input.listedit"
    Then I fill in "where[oxarticles][oxartnum]" with "OUTWRD-1"
    And I press "Search"
    And I click on CSS locator "#search tbody td div a"
    And I switch to root document
    And I switch to iframe "basefrm"
    And I switch to iframe "edit"
    And I wait till element exists "#myedit"
    And I fill in the following:
      | editval[oxarticles__oxtitle]  | Inward product updated |
      | editval[oxarticles__oxartnum] | OUTWRD-1               |
      | editval[oxarticles__oxprice]  | 15                     |
    When I press "Save"
    Then the request stack should be empty

  Scenario: Delete product
    Given I press "Save"
    And the request stack should be empty
    And I switch to root document
    And I switch to iframe "basefrm"
    Given I switch to iframe "list"
    And I wait till element exists "input.listedit"
    Then I fill in "where[oxarticles][oxartnum]" with "OUTWRD-1"
    And I press "Search"
    And I click on CSS locator "#search tbody td a.delete"
    And I confirm the popup
    Then the request stack should be empty
