<?php

use Payever\ExternalIntegration\Products\Http\RequestEntity\ProductRequestEntity;
use Payever\ExternalIntegration\ThirdParty\Action\ActionPayload;
use Payever\ExternalIntegration\ThirdParty\Action\ActionResult;
use Psr\Log\LoggerInterface;

class PayeverCreateProductActionHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface */
    protected $logger;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverProductTransformer */
    protected $productTransformer;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ActionResult */
    protected $actionResult;

    /** @var PayeverCreateProductActionHandler */
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
        $this->actionHandler = (new PayeverCreateProductActionHandler())
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
                $productEntity = $this->getMockBuilder(ProductRequestEntity::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $productEntity->expects($this->once())
            ->method('getSku')
            ->willReturn('some-sku');
        $this->productTransformer->expects($this->once())
            ->method('isTypeSupported')
            ->willReturn(true);
        $this->productTransformer->expects($this->once())
            ->method('transformFromPayeverIntoOxid')
            ->willReturn(
                $this->getMockBuilder('oxarticle')
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->actionResult->expects($this->once())
            ->method('incrementCreated');
        $this->actionHandler->handle(
            $actionPayload,
            $this->actionResult
        );
    }
}
