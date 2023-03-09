<?php

use Payever\ExternalIntegration\Products\Http\RequestEntity\ProductRequestEntity;
use Payever\ExternalIntegration\ThirdParty\Action\BidirectionalActionProcessor;
use Payever\ExternalIntegration\ThirdParty\Enum\ActionEnum;
use Payever\ExternalIntegration\ThirdParty\Enum\DirectionEnum;
use Psr\Log\LoggerInterface;

class PayeverSynchronizationManagerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverActionQueueManager */
    protected $actionQueueManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverConfigHelper */
    protected $configHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|BidirectionalActionProcessor */
    protected $bidirectionalSyncActionProcessor;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverProductTransformer */
    protected $productTransformer;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverInventoryTransformer */
    protected $inventoryTransformer;

    /** @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface */
    protected $logger;

    /** @var PayeverSynchronizationManager */
    protected $manager;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->configHelper = $this->getMockBuilder(PayeverConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->bidirectionalSyncActionProcessor = $this->getMockBuilder(BidirectionalActionProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productTransformer = $this->getMockBuilder(PayeverProductTransformer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inventoryTransformer = $this->getMockBuilder(PayeverInventoryTransformer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->actionQueueManager = $this->getMockBuilder(PayeverActionQueueManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager = (new PayeverSynchronizationManager())
            ->setActionQueueManager($this->actionQueueManager)
            ->setConfigHelper($this->configHelper)
            ->setBidirectionalSyncActionProcessor($this->bidirectionalSyncActionProcessor)
            ->setProductTransformer($this->productTransformer)
            ->setInventoryTransformer($this->inventoryTransformer)
            ->setLogger($this->logger);
        PayeverRegistry::set(PayeverRegistry::LAST_INWARD_PROCESSED_PRODUCT, null);
    }

    public function testHandleProductSave()
    {
        $this->productTransformer->expects($this->once())
            ->method('transformFromOxidIntoPayever')
            ->willReturn(
                $requestEntity = $this->getMockBuilder(ProductRequestEntity::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $requestEntity->expects($this->any())
            ->method('getSku')
            ->willReturn('some-sku');
        $this->manager->handleProductSave(
            $this->getMockBuilder(oxarticle::class)
                ->disableOriginalConstructor()
                ->getMock()
        );
    }

    public function testHandleProductDelete()
    {
        $this->productTransformer->expects($this->once())
            ->method('transformRemovedOxidIntoPayever');
        $this->manager->handleProductDelete(
            $this->getMockBuilder(oxarticle::class)
                ->disableOriginalConstructor()
                ->getMock()
        );
    }

    public function testHandleSetInventory()
    {
        $this->inventoryTransformer->expects($this->once())
            ->method('transformFromOxidIntoPayever');
        $this->manager->handleInventory(
            $this->getMockBuilder(oxarticle::class)
                ->disableOriginalConstructor()
                ->getMock(),
            1
        );
    }

    public function testHandleSetInventoryCaseNoDelta()
    {
        $this->inventoryTransformer->expects($this->once())
            ->method('transformCreatedOxidIntoPayever');
        $this->manager->handleInventory(
            $this->getMockBuilder(oxarticle::class)
                ->disableOriginalConstructor()
                ->getMock(),
            null
        );
    }

    public function testHandleAction()
    {
        $this->configHelper->expects($this->once())
            ->method('isProductsSyncEnabled')
            ->willReturn(true);
        $this->configHelper->expects($this->once())
            ->method('isProductsOutwardSyncEnabled')
            ->willReturn(true);
        $this->configHelper->expects($this->once())
            ->method('isCronMode')
            ->willReturn(false);
        $this->bidirectionalSyncActionProcessor->expects($this->once())
            ->method('processOutwardAction');
        $this->manager->handleAction(
            ActionEnum::ACTION_CREATE_PRODUCT,
            DirectionEnum::OUTWARD,
            ''
        );
    }

    public function testHandleActionCaseDisabled()
    {
        $this->configHelper->expects($this->once())
            ->method('isProductsSyncEnabled')
            ->willReturn(false);
        $this->manager->handleAction(
            ActionEnum::ACTION_CREATE_PRODUCT,
            DirectionEnum::OUTWARD,
            ''
        );
    }

    public function testHandleActionCaseOutwardSyncDisabled()
    {
        $this->configHelper->expects($this->once())
            ->method('isProductsSyncEnabled')
            ->willReturn(true);
        $this->configHelper->expects($this->once())
            ->method('isProductsOutwardSyncEnabled')
            ->willReturn(false);
        $this->manager->handleAction(
            ActionEnum::ACTION_CREATE_PRODUCT,
            DirectionEnum::OUTWARD,
            ''
        );
    }

    public function testHandleActionCaseInwardAction()
    {
        $this->configHelper->expects($this->once())
            ->method('isProductsSyncEnabled')
            ->willReturn(true);
        $this->configHelper->expects($this->once())
            ->method('isCronMode')
            ->willReturn(false);
        $this->bidirectionalSyncActionProcessor->expects($this->once())
            ->method('processInwardAction');
        $this->manager->handleAction(
            ActionEnum::ACTION_CREATE_PRODUCT,
            DirectionEnum::INWARD,
            ''
        );
    }

    public function testHandleActionCaseEnqueueAction()
    {
        $this->configHelper->expects($this->once())
            ->method('isProductsSyncEnabled')
            ->willReturn(true);
        $this->configHelper->expects($this->once())
            ->method('isCronMode')
            ->willReturn(true);
        $this->actionQueueManager->expects($this->once())
            ->method('addItem');
        $this->manager->handleAction(
            ActionEnum::ACTION_CREATE_PRODUCT,
            DirectionEnum::INWARD,
            ''
        );
    }

    public function testHandleActionCaseEnqueueActionCaseException()
    {
        $this->configHelper->expects($this->once())
            ->method('isProductsSyncEnabled')
            ->willReturn(true);
        $this->configHelper->expects($this->once())
            ->method('isCronMode')
            ->willReturn(true);
        $this->actionQueueManager->expects($this->once())
            ->method('addItem')
            ->willThrowException(new \Exception());
        $this->manager->handleAction(
            ActionEnum::ACTION_CREATE_PRODUCT,
            DirectionEnum::INWARD,
            ''
        );
    }

    public function testHandleActionCaseInwardActionCaseException()
    {
        $this->configHelper->expects($this->once())
            ->method('isProductsSyncEnabled')
            ->willReturn(true);
        $this->configHelper->expects($this->once())
            ->method('isCronMode')
            ->willReturn(false);
        $this->bidirectionalSyncActionProcessor->expects($this->once())
            ->method('processInwardAction')
            ->willThrowException(new \Exception());
        $this->manager->handleAction(
            ActionEnum::ACTION_CREATE_PRODUCT,
            DirectionEnum::INWARD,
            ''
        );
    }
}
