<?php

use Payever\ExternalIntegration\Products\Http\MessageEntity\ProductVariantOptionEntity;
use Payever\ExternalIntegration\Products\Http\RequestEntity\ProductRequestEntity;
use Psr\Log\LoggerInterface;

class PayeverOptionManagerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverConfigHelper */
    protected $configHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface */
    protected $logger;

    /** @var PayeverOptionManager */
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
        $this->manager = (new PayeverOptionManager())
            ->setConfigHelper($this->configHelper)
            ->setLogger($this->logger);
    }

    public function testSetSelectionName()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|oxarticle $product */
        $product = $this->getMockBuilder(oxarticle::class)
            ->disableOriginalConstructor()
            ->getMock();
        $variant = $this->getMockBuilder(ProductRequestEntity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $variant->expects($this->once())
            ->method('__call')
            ->willReturn(
                [
                    $option = $this->getMockBuilder(ProductVariantOptionEntity::class)
                        ->disableOriginalConstructor()
                        ->getMock()
                ]
            );
        $option->expects($this->once())
            ->method('__call')
            ->willReturn('some-title');
        $this->configHelper->expects($this->once())
            ->method('getLanguageIds')
            ->willReturn([0]);
        $this->manager->setSelectionName($product, [$variant]);
    }

    public function testSetVariantSelectionName()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|oxarticle $variant */
        $variant = $this->getMockBuilder(oxarticle::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|ProductRequestEntity $variantRequestEntity */
        $variantRequestEntity = $this->getMockBuilder(ProductRequestEntity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $variantRequestEntity->expects($this->once())
            ->method('__call')
            ->willReturn([
                $option = $this->getMockBuilder(ProductVariantOptionEntity::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            ]);
        $option->expects($this->once())
            ->method('__call')
            ->willReturn('some-option-value');
        $this->configHelper->expects($this->once())
            ->method('getLanguageIds')
            ->willReturn([0]);
        $this->manager->setVariantSelectionName($variant, $variantRequestEntity);
    }
}
