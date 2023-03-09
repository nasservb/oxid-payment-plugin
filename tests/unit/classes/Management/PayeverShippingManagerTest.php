<?php

use Payever\ExternalIntegration\Products\Http\MessageEntity\ProductShippingEntity;
use Payever\ExternalIntegration\Products\Http\RequestEntity\ProductRequestEntity;

class PayeverShippingManagerTest extends \PHPUnit\Framework\TestCase
{
    /** @var PayeverShippingManager */
    protected $manager;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->manager = new PayeverShippingManager();
    }

    public function testGetShipping()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|oxarticle $product */
        $product = $this->getMockBuilder(oxarticle::class)
            ->disableOriginalConstructor()
            ->getMock();
        $weight = new \stdClass();
        $weight->value = 1;
        $product->oxarticles__oxweight = $weight;
        $width = new \stdClass();
        $width->value = 1;
        $product->oxarticles__oxwidth = $width;
        $length = new \stdClass();
        $length->value = 1;
        $product->oxarticles__oxlength = $length;
        $height = new \stdClass();
        $height->value = 1;
        $product->oxarticles__oxheight = $height;
        $this->assertNotNull(
            $this->manager->getShipping($product)
        );
    }

    public function testGetShippingEmpty()
    {
        $this->assertNotNull(
            $this->manager->getShipping(
                $this->getMockBuilder(oxarticle::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            )
        );
    }

    public function testSetShipping()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|oxarticle $product */
        $product = $this->getMockBuilder(oxarticle::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|ProductRequestEntity $requestEntity */
        $requestEntity = $this->getMockBuilder(ProductRequestEntity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $requestEntity->expects($this->once())
            ->method('__call')
            ->willReturn(
                $shipping = $this->getMockBuilder(ProductShippingEntity::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $shipping->expects($this->at(0))
            ->method('__call')
            ->willReturn(PayeverShippingManager::MASS_GRAM);
        $shipping->expects($this->at(1))
            ->method('__call')
            ->willReturn(PayeverShippingManager::SIZE_CENTIMETER);
        $shipping->expects($this->at(2))
            ->method('__call')
            ->willReturn(1);
        $shipping->expects($this->at(3))
            ->method('__call')
            ->willReturn(1);
        $shipping->expects($this->at(4))
            ->method('__call')
            ->willReturn(1);
        $shipping->expects($this->at(5))
            ->method('__call')
            ->willReturn(1);
        $this->manager->setShipping($product, $requestEntity);
    }
}
