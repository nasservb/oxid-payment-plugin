<?php

use Payever\ExternalIntegration\Core\Base\ResponseInterface;
use Payever\ExternalIntegration\Core\Lock\LockInterface;
use Payever\ExternalIntegration\Payments\Enum\Status;
use Payever\ExternalIntegration\Payments\Http\MessageEntity\PaymentDetailsEntity;
use Payever\ExternalIntegration\Payments\Http\MessageEntity\RetrievePaymentResultEntity;
use Payever\ExternalIntegration\Payments\Http\ResponseEntity\CreatePaymentV2Response;
use Payever\ExternalIntegration\Payments\Http\ResponseEntity\RetrievePaymentResponse;
use Payever\ExternalIntegration\Payments\PaymentsApiClient;
use Payever\ExternalIntegration\Plugins\Command\PluginCommandManager;
use Payever\ExternalIntegration\Plugins\PluginsApiClient;
use Psr\Log\LoggerInterface;

class payeverStandardDispatcherTest extends \PHPUnit\Framework\TestCase
{
    use DatabaseMockTrait;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverAddressFactory */
    private $addressFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverConfigHelper */
    private $configHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverCountryFactory */
    private $countryFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface */
    private $logger;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverMethodHider */
    private $methodHider;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverOrderFactory */
    private $orderFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverOrderHelper */
    private $orderHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PaymentsApiClient */
    private $paymentsApiClient;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverPaymentMethodFactory */
    private $paymentMethodFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverRequestHelper */
    private $requestHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|LockInterface */
    private $locker;

    /** @var \PHPUnit_Framework_MockObject_MockObject|oxutils */
    private $utils;

    /** @var \PHPUnit_Framework_MockObject_MockObject|oxutilsurl */
    private $urlUtil;

    /** @var \PHPUnit_Framework_MockObject_MockObject|oxutilsview */
    private $viewUtil;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PluginsApiClient */
    private $pluginsApiClient;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PluginCommandManager */
    private $pluginCommandManager;

    /** @var oxsession */
    private $session;

    /** @var oxconfig */
    private $config;

    /** @var payeverStandardDispatcher */
    private $controller;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->buildDatabaseMock();
        $this->addressFactory = $this->getMockBuilder(PayeverAddressFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configHelper = $this->getMockBuilder(PayeverConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->countryFactory = $this->getMockBuilder(PayeverCountryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderFactory = $this->getMockBuilder(PayeverOrderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderHelper = $this->getMockBuilder(PayeverOrderHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentsApiClient = $this->getMockBuilder(PaymentsApiClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentMethodFactory = $this->getMockBuilder(PayeverPaymentMethodFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestHelper = $this->getMockBuilder(PayeverRequestHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->locker = $this->getMockBuilder(LockInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->utils = $this->getMockBuilder(oxutils::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlUtil = $this->getMockBuilder(oxutilsurl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewUtil = $this->getMockBuilder(oxutilsview::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pluginsApiClient = $this->getMockBuilder(PluginsApiClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pluginCommandManager = $this->getMockBuilder(PluginCommandManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->session = $this->getMockBuilder(oxsession::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder(oxconfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = new payeverStandardDispatcher();
        $this->controller->setSession($this->session);
        $this->controller->setConfig($this->config);
        $this->controller->setDatabase($this->database)
            ->setAddressFactory($this->addressFactory)
            ->setConfigHelper($this->configHelper)
            ->setCountryFactory($this->countryFactory)
            ->setLogger($this->logger)
            ->setOrderFactory($this->orderFactory)
            ->setOrderHelper($this->orderHelper)
            ->setPaymentsApiClient($this->paymentsApiClient)
            ->setPaymentMethodFactory($this->paymentMethodFactory)
            ->setRequestHelper($this->requestHelper)
            ->setLocker($this->locker)
            ->setUtils($this->utils)
            ->setUrlUtil($this->urlUtil)
            ->setViewUtil($this->viewUtil)
            ->setPluginsApiClient($this->pluginsApiClient)
            ->setPluginCommandManager($this->pluginCommandManager)
            ->setDryRun(true);
    }

    public function testProcessPayment()
    {
        $this->session->expects($this->at(0))
            ->method('getVariable')
            ->willReturn('http://some.domain/path');
        $this->urlUtil->expects($this->once())
            ->method('processUrl');
        $this->utils->expects($this->once())
            ->method('redirect');
        $this->assertNotEmpty($this->controller->processPayment());
    }

    public function testGetRedirectUrl()
    {
        $this->markTestSkipped();
        $this->paymentMethodFactory->expects($this->once())
            ->method('create')
            ->willReturn(
                $paymentMethod = $this->getMockBuilder(oxpayment::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->session->expects($this->once())
            ->method('getBasket')
            ->willReturn(
                $oBasket = $this->getMockBuilder(oxbasket::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $oBasket->expects($this->any())
            ->method('getBasketUser')
            ->willReturn(
                $oUser = $this->getMockBuilder(oxuser::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $oBasket->expects($this->once())
            ->method('getContents')
            ->willReturn([
                $item = $this->getMockBuilder(oxbasketitem::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            ]);
        $item->expects($this->once())
            ->method('getArticle')
            ->willReturn(
                $this->getMockBuilder(oxarticle::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $item->expects($this->any())
            ->method('getUnitPrice')
            ->willReturn(
                $this->getMockBuilder(oxprice::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->addressFactory->expects($this->once())
            ->method('create')
            ->willReturn(
                $deliveryAddress = $this->getMockBuilder(oxaddress::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $deliveryAddress->expects($this->any())
            ->method('__get')
            ->willReturn(
                $this->getMockBuilder(oxfield::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->countryFactory->expects($this->any())
            ->method('create')
            ->willReturn(
                $this->getMockBuilder(oxcountry::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $oBasket->expects($this->any())
            ->method('getPaymentId')
            ->willReturn('oxpe_paypal-some_uuid');
        $oBasket->expects($this->any())
            ->method('getVouchers')
            ->willReturn([
                $discount = $this->getMockBuilder(oxvoucher::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            ]);
        $discount->dVoucherdiscount = 1;
        $paymentMethod->expects($this->at(0))
            ->method('__get')
            ->willReturn([
                'paymentMethod' => 'paypal',
                'variantId' => 'some_uuid',
            ]);
        $oUser->expects($this->once())
            ->method('getBasket')
            ->willReturn($oBasket);
        $oBasket->expects($this->once())
            ->method('getBasketCurrency')
            ->willReturn((object) ['name' => 'EUR']);
        $oUser->expects($this->at(0))
            ->method('__get')
            ->willReturn(
                $this->getMockBuilder(oxfield::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->paymentsApiClient->expects($this->once())
            ->method('createPaymentV2Request')
            ->willReturn(
                $response = $this->getMockBuilder(ResponseInterface::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $response->expects($this->once())
            ->method('getResponseEntity')
            ->willReturn(
                $responseEntity = $this->getMockBuilder(CreatePaymentV2Response::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $responseEntity->expects($this->at(0))
            ->method('__call')
            ->willReturn('http://some.domain/path');
        $responseEntity->expects($this->once())
            ->method('toArray')
            ->willReturn([]);
        $this->controller->getRedirectUrl();
    }

    public function testRedirectToThankYou()
    {
        $this->configHelper->expects($this->once())
            ->method('getIsRedirect')
            ->willReturn(false);
        $this->session->expects($this->at(0))
            ->method('getVariable')
            ->willReturn(null);
        $this->assertNotEmpty($this->controller->redirectToThankYou());
    }

    public function testPayeverGatewayReturn()
    {
        $this->config->expects($this->at(0))
            ->method('getRequestParameter')
            ->willReturn('some-payment-uuid');
        $this->config->expects($this->at(1))
            ->method('getRequestParameter')
            ->willReturn('finish');
        $this->paymentsApiClient->expects($this->once())
            ->method('retrievePaymentRequest')
            ->willReturn(
                $response = $this->getMockBuilder(ResponseInterface::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $response->expects($this->once())
            ->method('getResponseEntity')
            ->willReturn(
                $responseEntity = $this->getMockBuilder(RetrievePaymentResponse::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $responseEntity->expects($this->at(0))
            ->method('__call')
            ->willReturn(
                $result = $this->getMockBuilder(RetrievePaymentResultEntity::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $result->expects($this->at(0))
            ->method('__call')
            ->willReturn(
                $this->getMockBuilder(PaymentDetailsEntity::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $result->expects($this->at(3))
            ->method('__call')
            ->willReturn(Status::STATUS_ACCEPTED);
        $this->database->expects($this->at(0))
            ->method('GetOne')
            ->willReturn(1);
        $this->orderFactory->expects($this->once())
            ->method('create')
            ->willReturn(
                $this->getMockBuilder(oxorder::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->controller->payeverGatewayReturn();
    }

    public function testExecutePluginCommands()
    {
        $this->pluginsApiClient->expects($this->once())
            ->method('registerPlugin');
        $this->pluginCommandManager->expects($this->once())
            ->method('executePluginCommands');
        $this->controller->executePluginCommands();
    }
}
