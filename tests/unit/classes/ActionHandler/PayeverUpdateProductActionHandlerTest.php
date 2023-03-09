<?php

use Payever\ExternalIntegration\Products\Http\RequestEntity\ProductRequestEntity;
use Payever\ExternalIntegration\ThirdParty\Action\ActionPayload;
use Payever\ExternalIntegration\ThirdParty\Action\ActionResult;
use Psr\Log\LoggerInterface;

class PayeverUpdateProductActionHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface */
    protected $logger;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverProductTransformer */
    protected $productTransformer;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverSeoHelper */
    protected $seoHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ActionResult */
    protected $actionResult;

    /** @var PayeverUpdateProductActionHandler */
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
        $this->seoHelper = $this->getMockBuilder(PayeverSeoHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->actionHandler = (new PayeverUpdateProductActionHandler())
            ->setProductTransformer($this->productTransformer)
            ->setSeoHelper($this->seoHelper)
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
                $entity = $this->getMockBuilder(ProductRequestEntity::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $entity->expects($this->once())
            ->method('getSku')
            ->willReturn('some-sku');
        $this->productTransformer->expects($this->once())
            ->method('isTypeSupported')
            ->willReturn(true);
        $this->productTransformer->expects($this->once())
            ->method('transformFromPayeverIntoOxid')
            ->willReturn(
                $product = $this->getMockBuilder(oxarticle::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $product->expects($this->once())
            ->method('save')
            ->willReturn('some-uid');
        $product->expects($this->once())
            ->method('getFullVariants')
            ->willReturn(
                $collection = $this->getMockBuilder(oxArticleList::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $collection->expects($this->once())
            ->method('getArray')
            ->willReturn([
                $this->getMockBuilder(oxarticle::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            ]);
        $this->actionResult->expects($this->once())
            ->method('incrementUpdated');
        $this->actionHandler->handle(
            $actionPayload,
            $this->actionResult
        );
    }

    public function testHandleCaseUnsupportedType()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ActionPayload $actionPayload */
        $actionPayload = $this->getMockBuilder(ActionPayload::class)
            ->disableOriginalConstructor()
            ->getMock();
        $actionPayload->expects($this->once())
            ->method('getPayloadEntity')
            ->willReturn(
                $entity = $this->getMockBuilder(ProductRequestEntity::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $entity->expects($this->once())
            ->method('getSku')
            ->willReturn('some-sku');
        $this->productTransformer->expects($this->once())
            ->method('isTypeSupported')
            ->willReturn(false);
        $this->actionHandler->handle(
            $actionPayload,
            $this->actionResult
        );
    }

    public function testHandleCaseVariant()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ActionPayload $actionPayload */
        $actionPayload = $this->getMockBuilder(ActionPayload::class)
            ->disableOriginalConstructor()
            ->getMock();
        $actionPayload->expects($this->once())
            ->method('getPayloadEntity')
            ->willReturn(
                $entity = $this->getMockBuilder(ProductRequestEntity::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $entity->expects($this->once())
            ->method('getSku')
            ->willReturn('some-sku');
        $this->productTransformer->expects($this->once())
            ->method('isTypeSupported')
            ->willReturn(true);
        $entity->expects($this->once())
            ->method('isVariant')
            ->willReturn(true);
        $this->actionHandler->handle(
            $actionPayload,
            $this->actionResult
        );
    }
}
