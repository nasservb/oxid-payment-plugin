@pluginIsEnabled @javascript @resetSession @baseScenarios
Feature: Plugin commands
  In order to keep track of plugin installations
  We need the plugin to register itself in our registry and keep track of plugin commands

  Scenario: Register and execute commands
    Given I set plugin command "payever_command_timestamp" value ""
    And I call executePluginCommands controller
    Then the last requests sequence equals to:
      | path                                                            |
      | ~/api/plugin/registry/ack/41d16fe6-3e91-4abd-9176-df44ee3d2837~ |
      | ~/api/plugin/registry/ack/d73635ff-51da-41ca-bfab-f08052de6163~ |
      | ~/api/plugin/registry/ack/8e2ec308-3500-4374-a0e5-43fbc39a8ddf~ |
      | ~/api/plugin/command/list~                                      |
      | ~/api/plugin/registry/register~                                 |
    And plugin custom url "payeverLiveUrl" value must be equal to "https://stub-live-url.com"
    And plugin custom url "payeverSandboxUrl" value must be equal to "https://stub-sandbox-url.com"
    And plugin api version "payeverApiVersion" value must be equal to "2"

  Scenario: Check notification message about new plugin version after visiting configuration page
    Given I am on admin login page
    And I login to admin section
    And I wait till element exists "#navigation"
    And I switch to iframe "navigation"
    And I switch to iframe "adminnav"
    And I follow "Payever"
    And I follow "General configuration"
    And I switch to root document
    And I switch to iframe "basefrm"
    And I wait till element exists ".payever-config-versions"
    Then I should see "There is a new version of payever module available."
