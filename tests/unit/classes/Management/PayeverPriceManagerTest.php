<?php

use Payever\ExternalIntegration\Core\Http\MessageEntity\GetCurrenciesResultEntity;
use Payever\ExternalIntegration\Core\Http\Response;
use Payever\ExternalIntegration\Core\Http\ResponseEntity\GetCurrenciesResponse;
use Payever\ExternalIntegration\Payments\PaymentsApiClient;
use Payever\ExternalIntegration\Products\Http\RequestEntity\ProductRequestEntity;
use Psr\Log\LoggerInterface;

class PayeverPriceManagerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|oxconfig */
    protected $config;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverConfigHelper */
    protected $configHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface */
    protected $logger;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverPriceFactory */
    protected $priceFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PaymentsApiClient */
    protected $paymentsApiClient;

    /** @var PayeverPriceManager */
    protected $manager;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->config = $this->getMockBuilder(oxconfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configHelper = $this->getMockBuilder(PayeverConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceFactory = $this->getMockBuilder(PayeverPriceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentsApiClient = $this->getMockBuilder(PaymentsApiClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager = (new PayeverPriceManager())
            ->setConfig($this->config)
            ->setConfigHelper($this->configHelper)
            ->setLogger($this->logger)
            ->setPriceFactory($this->priceFactory)
            ->setPaymentsApiClient($this->paymentsApiClient);
    }

    public function testGetCurrencyName()
    {
        $this->config->expects($this->once())
            ->method('getActShopCurrencyObject')
            ->willReturn(
                $currencyCarrier = new \stdClass()
            );
        $currencyCarrier->name = 'EUR';
        $this->assertEquals($currencyCarrier->name, $this->manager->getCurrencyName());
    }

    public function testSetPrice()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|oxarticle $product */
        $product = $this->getMockBuilder(oxarticle::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|ProductRequestEntity $requestEntity */
        $requestEntity = $this->getMockBuilder(ProductRequestEntity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configHelper->expects($this->once())
            ->method('isOxidCurrencyRateSource')
            ->willReturn(true);
        $requestEntity->expects($this->at(0))
            ->method('__call')
            ->willReturn('EUR');
        $requestEntity->expects($this->at(1))
            ->method('__call')
            ->willReturn('EUR');
        $requestEntity->expects($this->at(2))
            ->method('__call')
            ->willReturn(1.1);
        $requestEntity->expects($this->at(3))
            ->method('__call')
            ->willReturn(true);
        $requestEntity->expects($this->at(4))
            ->method('__call')
            ->willReturn(1.0);
        $this->priceFactory->expects($this->any())
            ->method('create')
            ->willReturn(
                $this->getMockBuilder(oxprice::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->manager->setPrice($product, $requestEntity);
    }

    public function testSetPriceCaseGBP()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|oxarticle $product */
        $product = $this->getMockBuilder(oxarticle::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|ProductRequestEntity $requestEntity */
        $requestEntity = $this->getMockBuilder(ProductRequestEntity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configHelper->expects($this->once())
            ->method('isOxidCurrencyRateSource')
            ->willReturn(true);
        $requestEntity->expects($this->at(0))
            ->method('__call')
            ->willReturn('GBP');
        $requestEntity->expects($this->at(1))
            ->method('__call')
            ->willReturn('GBP');
        $requestEntity->expects($this->at(2))
            ->method('__call')
            ->willReturn(1.1);
        $this->priceFactory->expects($this->once())
            ->method('create')
            ->willReturn(
                $this->getMockBuilder(oxprice::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->manager->setPrice($product, $requestEntity);
    }

    public function testSetPriceCaseCHF()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|oxarticle $product */
        $product = $this->getMockBuilder(oxarticle::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|ProductRequestEntity $requestEntity */
        $requestEntity = $this->getMockBuilder(ProductRequestEntity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configHelper->expects($this->once())
            ->method('isOxidCurrencyRateSource')
            ->willReturn(true);
        $requestEntity->expects($this->at(0))
            ->method('__call')
            ->willReturn('CHF');
        $requestEntity->expects($this->at(1))
            ->method('__call')
            ->willReturn('CHF');
        $requestEntity->expects($this->at(2))
            ->method('__call')
            ->willReturn(1.1);
        $this->priceFactory->expects($this->once())
            ->method('create')
            ->willReturn(
                $this->getMockBuilder(oxprice::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->manager->setPrice($product, $requestEntity);
    }

    public function testSetPriceCasePayeverCurrencyRateSource()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|oxarticle $product */
        $product = $this->getMockBuilder(oxarticle::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|ProductRequestEntity $requestEntity */
        $requestEntity = $this->getMockBuilder(ProductRequestEntity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configHelper->expects($this->once())
            ->method('isOxidCurrencyRateSource')
            ->willReturn(false);
        $this->paymentsApiClient->expects($this->once())
            ->method('getCurrenciesRequest')
            ->willReturn(
                $response = $this->getMockBuilder(Response::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $response->expects($this->once())
            ->method('getResponseEntity')
            ->willReturn(
                $responseEntity = $this->getMockBuilder(GetCurrenciesResponse::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $responseEntity->expects($this->once())
            ->method('__call')
            ->willReturn([
                'EUR' => $currencyResultEntity = $this->getMockBuilder(GetCurrenciesResultEntity::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            ]);
        $currencyResultEntity->expects($this->once())
            ->method('__call')
            ->willReturn(1);
        $requestEntity->expects($this->at(0))
            ->method('__call')
            ->willReturn('EUR');
        $requestEntity->expects($this->at(1))
            ->method('__call')
            ->willReturn('EUR');
        $requestEntity->expects($this->at(2))
            ->method('__call')
            ->willReturn(1.1);
        $requestEntity->expects($this->at(3))
            ->method('__call')
            ->willReturn(true);
        $requestEntity->expects($this->at(4))
            ->method('__call')
            ->willReturn(1.0);
        $this->priceFactory->expects($this->any())
            ->method('create')
            ->willReturn(
                $this->getMockBuilder(oxprice::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->manager->setPrice($product, $requestEntity);
    }

    public function testSetVat()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|oxarticle $product */
        $product = $this->getMockBuilder(oxarticle::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|ProductRequestEntity $requestEntity */
        $requestEntity = $this->getMockBuilder(ProductRequestEntity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager->setVat($product, $requestEntity);
    }
}
