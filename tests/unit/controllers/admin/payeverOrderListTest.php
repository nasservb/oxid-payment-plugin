<?php

use Payever\ExternalIntegration\Core\Base\ResponseInterface;
use Payever\ExternalIntegration\Payments\Action\ActionDeciderInterface;
use Payever\ExternalIntegration\Payments\Http\MessageEntity\GetTransactionResultEntity;
use Payever\ExternalIntegration\Payments\Http\ResponseEntity\GetTransactionResponse;
use Payever\ExternalIntegration\Payments\PaymentsApiClient;
use Psr\Log\LoggerInterface;

class payeverOrderListTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverConfigHelper */
    private $configHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface */
    private $logger;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverOrderFactory */
    private $orderFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PaymentsApiClient */
    private $paymentsApiClient;

    /** @var payeverOrderList */
    private $controller;

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
        $this->orderFactory = $this->getMockBuilder(PayeverOrderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentsApiClient = $this->getMockBuilder(PaymentsApiClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = new payeverOrderList(true);
        $this->controller->setConfigHelper($this->configHelper)
            ->setLogger($this->logger)
            ->setOrderFactory($this->orderFactory)
            ->setPaymentsApiClient($this->paymentsApiClient);
    }

    public function testStorno()
    {
        $this->orderFactory->expects($this->once())
            ->method('create')
            ->willReturn(
                $oOrder = $this->getMockBuilder(oxorder::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $oOrder->expects($this->at(0))
            ->method('getFieldData')
            ->willReturn('oxpe_paypal');
        $this->configHelper->expects($this->once())
            ->method('isPayeverPaymentMethod')
            ->willReturn(true);
        $oOrder->expects($this->at(1))
            ->method('getFieldData')
            ->willReturn('some-payment-uuid');
        $this->paymentsApiClient->expects($this->once())
            ->method('getTransactionRequest')
            ->willReturn(
                $getTransactionResponse = $this->getMockBuilder(ResponseInterface::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $getTransactionResponse->expects($this->once())
            ->method('getResponseEntity')
            ->willReturn(
                $getTransactionEntity = $this->getMockBuilder(GetTransactionResponse::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $getTransactionEntity->expects($this->at(0))
            ->method('__call')
            ->willReturn(
                $getTransactionResult = $this->getMockBuilder(GetTransactionResultEntity::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $getTransactionResult->expects($this->at(0))
            ->method('__call')
            ->willReturn([
                (object) [
                    'action' => ActionDeciderInterface::ACTION_REFUND,
                    'enabled' => true,
                ]
            ]);
        $this->controller->storno();
    }

    public function testStornoCaseCancel()
    {
        $this->orderFactory->expects($this->once())
            ->method('create')
            ->willReturn(
                $oOrder = $this->getMockBuilder(oxorder::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $oOrder->expects($this->at(0))
            ->method('getFieldData')
            ->willReturn('oxpe_paypal');
        $this->configHelper->expects($this->once())
            ->method('isPayeverPaymentMethod')
            ->willReturn(true);
        $oOrder->expects($this->at(1))
            ->method('getFieldData')
            ->willReturn('some-payment-uuid');
        $this->paymentsApiClient->expects($this->any())
            ->method('getTransactionRequest')
            ->willReturn(
                $getTransactionResponse = $this->getMockBuilder(ResponseInterface::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $getTransactionResponse->expects($this->any())
            ->method('getResponseEntity')
            ->willReturn(
                $getTransactionEntity = $this->getMockBuilder(GetTransactionResponse::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $getTransactionEntity->expects($this->any())
            ->method('__call')
            ->willReturn(
                $getTransactionResult = $this->getMockBuilder(GetTransactionResultEntity::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $getTransactionResult->expects($this->any())
            ->method('__call')
            ->willReturn([
                (object) [
                    'action' => ActionDeciderInterface::ACTION_CANCEL,
                    'enabled' => true,
                ]
            ]);
        $this->controller->storno();
    }
}
