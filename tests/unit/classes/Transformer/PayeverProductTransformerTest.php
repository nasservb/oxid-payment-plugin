<?php

use Payever\ExternalIntegration\Products\Enum\ProductTypeEnum;
use Payever\ExternalIntegration\Products\Http\RequestEntity\ProductRemovedRequestEntity;
use Payever\ExternalIntegration\Products\Http\RequestEntity\ProductRequestEntity;

class PayeverProductTransformerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverCategoryFactory */
    protected $categoryFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverConfigHelper */
    protected $configHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|oxconfig */
    protected $config;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverProductHelper */
    protected $productHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverCategoryManager */
    protected $categoryManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverPriceManager */
    protected $priceManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverShippingManager */
    protected $shippingManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverGalleryManager */
    protected $galleryManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverOptionManager */
    protected $optionManager;

    /** @var PayeverProductTransformer */
    protected $transformer;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->categoryFactory = $this->getMockBuilder(PayeverCategoryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configHelper = $this->getMockBuilder(PayeverConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder(oxconfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productHelper = $this->getMockBuilder(PayeverProductHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryManager = $this->getMockBuilder(PayeverCategoryManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceManager = $this->getMockBuilder(PayeverPriceManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shippingManager = $this->getMockBuilder(PayeverShippingManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->galleryManager = $this->getMockBuilder(PayeverGalleryManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionManager = $this->getMockBuilder(PayeverOptionManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transformer = (new PayeverProductTransformer())
            ->setCategoryFactory($this->categoryFactory)
            ->setConfigHelper($this->configHelper)
            ->setConfig($this->config)
            ->setProductHelper($this->productHelper)
            ->setCategoryManager($this->categoryManager)
            ->setPriceManager($this->priceManager)
            ->setShippingManager($this->shippingManager)
            ->setGalleryManager($this->galleryManager)
            ->setOptionManager($this->optionManager);
    }

    public function testIsTypeSupported()
    {
        $this->assertTrue($this->transformer->isTypeSupported(ProductTypeEnum::TYPE_PHYSICAL));
    }

    public function testTransformFromOxidIntoPayever()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|oxarticle $product */
        $product = $this->getMockBuilder(oxarticle::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())
            ->method('getCategoryIds')
            ->willReturn(['1']);
        $this->categoryFactory->expects($this->once())
            ->method('create')
            ->willReturn(
                $category = $this->getMockBuilder(oxcategory::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $product->expects($this->once())
            ->method('getVariants')
            ->willReturn(
                [
                    $this->getMockBuilder(oxarticle::class)
                        ->disableOriginalConstructor()
                        ->getMock()
                ]
            );
        $this->transformer->transformFromOxidIntoPayever($product);
    }

    public function testTransformFromOxidIntoPayeverCaseDigitalProduct()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|oxarticle $product */
        $product = $this->getMockBuilder(oxarticle::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->at(4))
            ->method('getFieldData')
            ->willReturn(true);
        $product->expects($this->once())
            ->method('getCategoryIds')
            ->willReturn(['1']);
        $this->categoryFactory->expects($this->once())
            ->method('create')
            ->willReturn(
                $category = $this->getMockBuilder(oxcategory::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $product->expects($this->once())
            ->method('getVariants')
            ->willReturn(
                [
                    $this->getMockBuilder(oxarticle::class)
                        ->disableOriginalConstructor()
                        ->getMock()
                ]
            );
        $this->transformer->transformFromOxidIntoPayever($product);
    }

    public function testTransformFromPayeverIntoOxid()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ProductRequestEntity $requestEntity */
        $requestEntity = $this->getMockBuilder(ProductRequestEntity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productHelper->expects($this->any())
            ->method('getProductBySku')
            ->willReturn(
                $this->getMockBuilder(oxarticle::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->configHelper->expects($this->any())
            ->method('getLanguageIds')
            ->willReturn([0, 1]);
        $requestEntity->expects($this->at(6))
            ->method('__call')
            ->willReturn([
                $this->getMockBuilder(ProductRequestEntity::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            ]);
        $this->transformer->transformFromPayeverIntoOxid($requestEntity);
    }

    public function testTransformRemovedOxidIntoPayever()
    {
        $this->assertNotNull(
            $this->transformer->transformRemovedOxidIntoPayever(
                $this->getMockBuilder(oxarticle::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            )
        );
    }

    public function testTransformRemovedPayeverIntoOxid()
    {
        $this->productHelper->expects($this->once())
            ->method('getProductBySku')
            ->willReturn(
                $this->getMockBuilder(oxarticle::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->assertNotNull(
            $this->transformer->transformRemovedPayeverIntoOxid(
                $this->getMockBuilder(ProductRemovedRequestEntity::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            )
        );
    }
}
