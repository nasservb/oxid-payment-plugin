<?php

use Payever\ExternalIntegration\Inventory\Http\MessageEntity\InventoryChangedEntity;

class PayeverInventoryTransformerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverConfigHelper */
    protected $configHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverProductHelper */
    protected $productHelper;

    /** @var PayeverInventoryTransformer */
    protected $transformer;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->configHelper = $this->getMockBuilder(PayeverConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productHelper = $this->getMockBuilder(PayeverProductHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transformer = (new PayeverInventoryTransformer())
            ->setConfigHelper($this->configHelper)
            ->setProductHelper($this->productHelper);
    }

    public function testTransformCreatedOxidIntoPayever()
    {
        $this->assertNotNull(
            $this->transformer->transformCreatedOxidIntoPayever(
                $this->getMockBuilder(oxarticle::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            )
        );
    }

    public function testTransformFromOxidIntoPayever()
    {
        $this->assertNotNull(
            $this->transformer->transformFromOxidIntoPayever(
                $this->getMockBuilder(oxarticle::class)
                    ->disableOriginalConstructor()
                    ->getMock(),
                1
            )
        );
    }

    public function testTransformFromPayeverIntoOxid()
    {
        $this->productHelper->expects($this->once())
            ->method('getProductBySku')
            ->willReturn(
                $this->getMockBuilder(oxarticle::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->assertNotNull(
            $this->transformer->transformFromPayeverIntoOxid(
                $this->getMockBuilder(InventoryChangedEntity::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            )
        );
    }
}
