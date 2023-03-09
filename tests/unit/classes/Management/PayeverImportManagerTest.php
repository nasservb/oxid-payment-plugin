<?php

use Payever\ExternalIntegration\ThirdParty\Enum\ActionEnum;
use Psr\Log\LoggerInterface;

class PayeverImportManagerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverConfigHelper */
    protected $configHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface */
    protected $logger;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverSynchronizationManager */
    protected $synchronizationManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverSubscriptionManager */
    protected $subscriptionManager;

    /** @var PayeverImportManager */
    protected $manager;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->configHelper = $this->getMockBuilder(PayeverConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->synchronizationManager = $this->getMockBuilder(PayeverSynchronizationManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subscriptionManager = $this->getMockBuilder(PayeverSubscriptionManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager = (new PayeverImportManager())
            ->setConfigHelper($this->configHelper)
            ->setLogger($this->logger)
            ->setSynchronizationManager($this->synchronizationManager)
            ->setSubscriptionManager($this->subscriptionManager);
    }

    public function testImport()
    {
        $this->configHelper->expects($this->once())
            ->method('isProductsSyncEnabled')
            ->willReturn(true);
        $this->subscriptionManager->expects($this->once())
            ->method('getSupportedActions')
            ->willReturn([$synAction = ActionEnum::ACTION_CREATE_PRODUCT]);
        $this->configHelper->expects($this->once())
            ->method('getProductsSyncExternalId')
            ->willReturn($externalId = 'some-external-id');
        $this->synchronizationManager->expects($this->once())
            ->method('handleInwardAction');
        $this->manager->import(
            $synAction,
            $externalId,
            \json_encode(['some-data'])
        );
    }

    public function testImportCaseInvalidPayload()
    {
        $this->configHelper->expects($this->once())
            ->method('isProductsSyncEnabled')
            ->willReturn(true);
        $this->subscriptionManager->expects($this->once())
            ->method('getSupportedActions')
            ->willReturn([$synAction = ActionEnum::ACTION_CREATE_PRODUCT]);
        $this->configHelper->expects($this->once())
            ->method('getProductsSyncExternalId')
            ->willReturn($externalId = 'some-external-id');
        $this->synchronizationManager->expects($this->never())
            ->method('handleInwardAction');
        $this->manager->import(
            $synAction,
            $externalId,
            'invalid-json'
        );
    }

    public function testImportCaseInvalidExternalId()
    {
        $this->configHelper->expects($this->once())
            ->method('isProductsSyncEnabled')
            ->willReturn(true);
        $this->subscriptionManager->expects($this->once())
            ->method('getSupportedActions')
            ->willReturn([$synAction = ActionEnum::ACTION_CREATE_PRODUCT]);
        $this->configHelper->expects($this->once())
            ->method('getProductsSyncExternalId')
            ->willReturn('some-external-id');
        $this->synchronizationManager->expects($this->never())
            ->method('handleInwardAction');
        $this->manager->import(
            $synAction,
            'some-other-external-id',
            null
        );
    }

    public function testImportCaseInvalidAction()
    {
        $this->configHelper->expects($this->once())
            ->method('isProductsSyncEnabled')
            ->willReturn(true);
        $this->subscriptionManager->expects($this->once())
            ->method('getSupportedActions')
            ->willReturn([ActionEnum::ACTION_CREATE_PRODUCT]);
        $this->synchronizationManager->expects($this->never())
            ->method('handleInwardAction');
        $this->manager->import(
            'invalid-action',
            null,
            null
        );
    }

    public function testImportCaseDisabled()
    {
        $this->configHelper->expects($this->once())
            ->method('isProductsSyncEnabled')
            ->willReturn(false);
        $this->synchronizationManager->expects($this->never())
            ->method('handleInwardAction');
        $this->manager->import(
            null,
            null,
            null
        );
    }
}
