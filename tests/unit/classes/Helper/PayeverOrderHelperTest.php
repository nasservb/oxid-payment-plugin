<?php

class PayeverOrderHelperTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverConfigHelper */
    private $configHelper;

    /** @var PayeverOrderHelper */
    private $helper;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->configHelper = $this->getMockBuilder(PayeverConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper = new PayeverOrderHelper();
        $this->helper->setConfigHelper($this->configHelper);
    }

    public function testGetAmountByCart()
    {
        $cart = $this->getMockBuilder(oxbasket::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configHelper->expects($this->once())
            ->method('getOxidVersionInt')
            ->willReturn(63);
        $cart->expects($this->any())
            ->method('getPaymentCost')
            ->willReturn(
                $paymentCostPrice = $this->getMockBuilder(oxprice::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $paymentCostPrice->expects($this->once())
            ->method('getPrice')
            ->willReturn(1);
        $cart->expects($this->once())
            ->method('getPrice')
            ->willReturn(
                $cartPrice = $this->getMockBuilder(oxprice::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $cartPrice->expects($this->once())
            ->method('getPrice')
            ->willReturn(11);
        $this->assertEquals(10, $this->helper->getAmountByCart($cart));
    }

    public function testGetFeeByCart()
    {
        $cart = $this->getMockBuilder(oxbasket::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configHelper->expects($this->once())
            ->method('getOxidVersionInt')
            ->willReturn(63);
        $cart->expects($this->any())
            ->method('getDeliveryCost')
            ->willReturn(
                $deliveryCostPrice = $this->getMockBuilder(oxprice::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $deliveryCostPrice->expects($this->once())
            ->method('getPrice')
            ->willReturn(1);
        $this->assertEquals(1, $this->helper->getFeeByCart($cart));
    }
}
