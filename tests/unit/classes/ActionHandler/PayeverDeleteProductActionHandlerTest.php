<?php

use Payever\ExternalIntegration\Products\Http\RequestEntity\ProductRemovedRequestEntity;
use Payever\ExternalIntegration\ThirdParty\Action\ActionPayload;
use Payever\ExternalIntegration\ThirdParty\Action\ActionResult;
use Psr\Log\LoggerInterface;

class PayeverDeleteProductActionHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface */
    protected $logger;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverProductTransformer */
    protected $productTransformer;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ActionResult */
    protected $actionResult;

    /** @var PayeverDeleteProductActionHandler */
    protected $actionHandler;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productTransformer = $this->getMockBuilder(PayeverProductTransformer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->actionHandler = (new PayeverDeleteProductActionHandler())
            ->setProductTransformer($this->productTransformer)
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
                $productEntity = $this->getMockBuilder(ProductRemovedRequestEntity::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $productEntity->expects($this->at(0))
            ->method('__call')
            ->willReturn('some-sku');
        $this->productTransformer->expects($this->once())
            ->method('transformRemovedPayeverIntoOxid')
            ->willReturn(
                $product = $this->getMockBuilder(oxarticle::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $product->expects($this->once())
            ->method('getId')
            ->willReturn('some-id');
        $this->actionResult->expects($this->once())
            ->method('incrementDeleted');
        $this->actionHandler->handle(
            $actionPayload,
            $this->actionResult
        );
    }
}
