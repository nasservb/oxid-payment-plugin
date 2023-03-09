@pluginIsEnabled @javascript @resetSession @products
Feature: Third-party subscription management

  Background:
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

  @thirdPartyUnsubscribed
  Scenario: Subscribe
    And I wait till element exists "input.editinput[name="payever_config[payeverProductsSyncEnabled]"]"
    And I click on CSS locator 'input.editinput[name="payever_config[payeverProductsSyncEnabled]"]'
    And I press "Save"
    And I wait request stack exists and populated with size 7
    And plugin config "payeverProductsSyncEnabled" value must be equal to "1"
    Then the requests sequence contains:
      | path                                             | method  | json_body                                                                                                                                                                                                                                                                                                                                                                                                 |
      | */api/business/payever/connection/authorization* | GET     |                                                                                                                                                                                                                                                                                                                                                                                                           |
      | */api/business/payever/integration/oxid*         | POST    | {"businessUuid":"payever","externalId":"*","thirdPartyName":"oxid","actions":[{"name":"create-product","url":"*","method":"POST"},{"name":"update-product","url":"*","method":"POST"},{"name":"remove-product","url":"*","method":"POST"},{"name":"add-inventory","url":"*","method":"POST"},{"name":"set-inventory","url":"*","method":"POST"},{"name":"subtract-inventory","url":"*","method":"POST"}]} |
    And I expect export button is not disabled

  @thirdPartySubscribed
  Scenario: Unsubscribe
    And I wait till element exists "input.editinput[name="payever_config[payeverProductsSyncEnabled]"]"
    And I click on CSS locator 'input.editinput[name="payever_config[payeverProductsSyncEnabled]"]'
    And I press "Save"
    And I wait request stack exists and populated with size 5
    And I wait till element exists ".payever-config-header"
    And plugin config "payeverProductsSyncEnabled" value must be equal to "0"
    Then the requests sequence contains:
      | path                                             | method |
      | */api/business/payever/connection/authorization* | DELETE |
    And I expect export button is disabled
