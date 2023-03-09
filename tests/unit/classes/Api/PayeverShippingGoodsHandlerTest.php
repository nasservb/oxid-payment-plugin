<?php

use Payever\ExternalIntegration\Payments\Action\ActionDecider;
use Payever\ExternalIntegration\Payments\Http\ResponseEntity\ShippingGoodsPaymentResponse;
use Payever\ExternalIntegration\Payments\PaymentsApiClient;
use Psr\Log\LoggerInterface;

class PayeverShippingGoodsHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverConfigHelper */
    private $configHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface */
    private $logger;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PaymentsApiClient */
    private $paymentsApiClient;

    /** @var PayeverShippingGoodsHandler */
    private $handler;

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
        $this->paymentsApiClient = $this->getMockBuilder(PaymentsApiClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->handler = new PayeverShippingGoodsHandler();
        $this->handler->setConfigHelper($this->configHelper)
            ->setLogger($this->logger)
            ->setPaymentsApiClient($this->paymentsApiClient);

        $this->handler->actionDecider = $this->getMockBuilder(ActionDecider::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testTriggerShippingGoodsPaymentRequest()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|oxorder $order */
        $order = $this->getMockBuilder(oxorder::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject|oxvoucherdiscount $order */
        $voucher = $this->getMockBuilder(oxvoucherdiscount::class)
            ->disableOriginalConstructor()
            ->getMock();

        $order->oxorder__oxvoucherdiscount = $voucher;

        /** @var \PHPUnit_Framework_MockObject_MockObject|oxorder__oxdiscount $order */
        $discount = $this->getMockBuilder(oxorder__oxdiscount::class)
            ->disableOriginalConstructor()
            ->getMock();

        $order->oxorder__oxdiscount = $discount;

        /** @var \PHPUnit_Framework_MockObject_MockObject|oxorder__oxtotalordersum $order */
        $total = $this->getMockBuilder(oxorder__oxtotalordersum::class)
            ->disableOriginalConstructor()
            ->getMock();
        $total->value = 100;
        $order->oxorder__oxtotalordersum = $total;

        $order->expects($this->any())
            ->method('getOrderArticles')
            ->willReturn([]);

        $order->expects($this->any())
            ->method('getOrderDeliveryPrice')
            ->willReturn(0);

        $order->expects($this->at(0))
            ->method('getFieldData')
            ->willReturn('oxpe_stripe'); // oxpaymenttype

        $order->expects($this->at(1))
            ->method('getFieldData')
            ->willReturn('some-uuid'); // oxtransid

        $order->expects($this->at(2))
            ->method('getFieldData')
            ->willReturn('DHL'); // oxdeltype

        $order->expects($this->at(1))
            ->method('getFieldData')
            ->willReturn(date('D-M-Y')); // oxsenddate

        $this->configHelper->expects($this->once())
            ->method('isPayeverPaymentMethod')
            ->willReturn(true);

        $this->handler->actionDecider->expects($this->once())
            ->method('isShippingAllowed')
            ->willReturn(true);

        $shippingGoodsPaymentResponse = $this->getMockBuilder(ShippingGoodsPaymentResponse::class)
            ->disableOriginalConstructor()
            ->getMock();
        $shippingGoodsPaymentResponse->expects($this->once())
            ->method('isSuccessful')
            ->willReturn(true);

        $this->paymentsApiClient->expects($this->once())
            ->method('shippingGoodsPaymentRequest')
            ->willReturn($shippingGoodsPaymentResponse);

        $this->handler->triggerShippingGoodsPaymentRequest($order);
    }

    public function testTriggerShippingGoodsPaymentRequestCaseException()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|oxorder $order */
        $order = $this->getMockBuilder(oxorder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $order->expects($this->any())
            ->method('getOrderArticles')
            ->willReturn([]);

        $order->expects($this->at(0))
            ->method('getFieldData')
            ->willReturn('oxpe_stripe');
        $order->expects($this->at(1))
            ->method('getFieldData')
            ->willThrowException(new \Exception());
        $this->configHelper->expects($this->once())
            ->method('isPayeverPaymentMethod')
            ->willReturn(true);
        $this->paymentsApiClient->expects($this->never())
            ->method('getTransactionRequest');
        $this->handler->triggerShippingGoodsPaymentRequest($order);
    }

    public function testTriggerShippingGoodsPaymentRequestCaseNonPayeverMethod()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|oxorder $order */
        $order = $this->getMockBuilder(oxorder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $order->expects($this->at(0))
            ->method('getFieldData')
            ->willReturn('unknown_method');
        $this->configHelper->expects($this->once())
            ->method('isPayeverPaymentMethod')
            ->willReturn(false);
        $this->paymentsApiClient->expects($this->never())
            ->method('getTransactionRequest');
        $this->handler->triggerShippingGoodsPaymentRequest($order);
    }
}
