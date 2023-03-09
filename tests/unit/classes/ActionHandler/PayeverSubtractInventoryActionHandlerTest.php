<?php

use Payever\ExternalIntegration\Inventory\Http\MessageEntity\InventoryChangedEntity;
use Payever\ExternalIntegration\ThirdParty\Action\ActionPayload;
use Payever\ExternalIntegration\ThirdParty\Action\ActionResult;
use Psr\Log\LoggerInterface;

class PayeverSubtractInventoryActionHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface */
    protected $logger;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverInventoryTransformer */
    protected $inventoryTransformer;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ActionResult */
    protected $actionResult;

    /** @var PayeverSetInventoryActionHandler */
    protected $actionHandler;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inventoryTransformer = $this->getMockBuilder(PayeverInventoryTransformer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->actionHandler = (new PayeverSubtractInventoryActionHandler())
            ->setInventoryTransformer($this->inventoryTransformer)
            ->setLogger($this->logger);
        $this->actionResult = $this->getMockBuilder(ActionResult::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetSupportedAction()
    {
        $this->assertNotEmpty($this->actionHandler->getSupportedAction());
    }

    public function testHandle()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ActionPayload $actionPayload */
        $actionPayload = $this->getMockBuilder(ActionPayload::class)
            ->disableOriginalConstructor()
            ->getMock();
        $actionPayload->expects($this->once())
            ->method('getPayloadEntity')
            ->willReturn(
                $inventoryChangedEntity = $this->getMockBuilder(InventoryChangedEntity::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $inventoryChangedEntity->expects($this->at(0))
            ->method('__call')
            ->willReturn('some-sku');
        $this->inventoryTransformer->expects($this->once())
            ->method('transformFromPayeverIntoOxid')
            ->willReturn(
                $product = $this->getMockBuilder(oxarticle::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $product->expects($this->once())
            ->method('getFieldData')
            ->willReturn(1);
        $this->actionResult->expects($this->once())
            ->method('incrementUpdated');
        $this->actionHandler->handle(
            $actionPayload,
            $this->actionResult
        );
    }
}
