@baseScenarios
Feature: Installation
  In order to use plugin
  As merchant
  I need to manage plugin installation

  @pluginIsDisabled
  Scenario: Install
    Given the plugin is not enabled
    And I enable the plugin
    Then the plugin is enabled
    And I am on admin login page
    And I login to admin section
    And I switch to iframe "basefrm"
    And I should see "Welcome to the OXID eShop Admin"
    And I follow "Modules"
    And I switch to iframe "list"
    And I should see "payever"
    And I switch to root document
    And I switch to iframe "basefrm"
    And I switch to iframe "edit"
    And I should not see "Invalid modules were detected"
    And I should not see "PROBLEMATIC FILES"
    # Validate that our plugin did not brake something in the public section
    And I am on the homepage
    Then I should see "OXID Online Shop"

  @pluginIsEnabled
  Scenario: Uninstall
    Given the plugin is enabled
    And I disable the plugin
    Then the plugin is not enabled
    And I am on the homepage
    Then I should see "OXID Online Shop"
