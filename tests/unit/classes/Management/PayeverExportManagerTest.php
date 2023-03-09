<?php

use Payever\ExternalIntegration\Inventory\InventoryApiClient;
use Payever\ExternalIntegration\Products\ProductsApiClient;
use Psr\Log\LoggerInterface;

class PayeverExportManagerTest extends \PHPUnit\Framework\TestCase
{
    use DatabaseMockTrait;

    /** @var \PHPUnit_Framework_MockObject_MockObject|oxconfig */
    protected $config;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverConfigHelper */
    protected $configHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface */
    protected $logger;

    /** @var \PHPUnit_Framework_MockObject_MockObject|oxlang */
    protected $language;

    /** @var \PHPUnit_Framework_MockObject_MockObject|InventoryApiClient */
    protected $inventoryApiClient;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ProductsApiClient */
    protected $productsApiClient;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverArticleListCollectionFactory */
    protected $articleListCollectionFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverSubscriptionManager */
    protected $subscriptionManager;

    /** @var PayeverExportManager */
    protected $manager;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->buildDatabaseMock();
        $this->config = $this->getMockBuilder(oxconfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configHelper = $this->getMockBuilder(PayeverConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->language = $this->getMockBuilder(oxlang::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inventoryApiClient = $this->getMockBuilder(InventoryApiClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productsApiClient = $this->getMockBuilder(ProductsApiClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->articleListCollectionFactory = $this->getMockBuilder(PayeverArticleListCollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subscriptionManager = $this->getMockBuilder(PayeverSubscriptionManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager = (new PayeverExportManager())
            ->setConfig($this->config)
            ->setConfigHelper($this->configHelper)
            ->setDatabase($this->database)
            ->setLanguage($this->language)
            ->setInventoryApiClient($this->inventoryApiClient)
            ->setProductsApiClient($this->productsApiClient)
            ->setArticleListCollectionFactory($this->articleListCollectionFactory)
            ->setLogger($this->logger)
            ->setSubscriptionManager($this->subscriptionManager);
    }

    public function testExport()
    {
        $this->configHelper->expects($this->once())
            ->method('isProductsSyncEnabled')
            ->willReturn(true);
        $this->configHelper->expects($this->once())
            ->method('isProductsOutwardSyncEnabled')
            ->willReturn(true);
        $this->articleListCollectionFactory->expects($this->any())
            ->method('create')
            ->willReturn(
                $collection = $this->getMockBuilder(oxarticlelist::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $collection->expects($this->any())
            ->method('getBaseObject')
            ->willReturn(
                $listObject = $this->getMockBuilder(oxarticle::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $listObject->expects($this->any())
            ->method('isMultilang')
            ->willReturn(true);
        $this->database->expects($this->any())
            ->method('getOne')
            ->willReturn(1);
        $collection->expects($this->once())
            ->method('getArray')
            ->willReturn([$listObject]);
        $this->productsApiClient->expects($this->once())
            ->method('exportProducts')
            ->willReturn(1);
        $this->inventoryApiClient->expects($this->once())
            ->method('exportInventory')
            ->willReturn(1);
        $this->manager->export(0, 0);
        $this->assertNull($this->manager->getNextPage());
        $this->assertEquals(1, $this->manager->getAggregate());
        $this->assertEmpty($this->manager->getErrors());
    }

    public function testExportCaseOutwardSyncDisabled()
    {
        $this->configHelper->expects($this->once())
            ->method('isProductsSyncEnabled')
            ->willReturn(true);
        $this->configHelper->expects($this->once())
            ->method('isProductsOutwardSyncEnabled')
            ->willReturn(false);
        $this->assertFalse($this->manager->export(0, 0));
    }

    public function testExportCaseException()
    {
        $this->configHelper->expects($this->once())
            ->method('isProductsSyncEnabled')
            ->willThrowException(new \Exception());
        $this->manager->export(0, 0);
    }
}
