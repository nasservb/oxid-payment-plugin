<?php

use Payever\ExternalIntegration\Products\Http\RequestEntity\ProductRequestEntity;
use Payever\ExternalIntegration\Products\Http\MessageEntity\ProductCategoryEntity;

class PayeverCategoryManagerTest extends \PHPUnit\Framework\TestCase
{
    use DatabaseMockTrait;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverCategoryFactory */
    protected $categoryFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverCategoryHelper */
    protected $categoryHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverConfigHelper */
    protected $configHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Object2CategoryFactory */
    protected $object2CategoryFactory;

    /** @var PayeverCategoryManager */
    protected $manager;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->buildDatabaseMock();
        $this->categoryFactory = $this->getMockBuilder(PayeverCategoryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryHelper = $this->getMockBuilder(PayeverCategoryHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configHelper = $this->getMockBuilder(PayeverConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->object2CategoryFactory = $this->getMockBuilder(Object2CategoryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager = (new PayeverCategoryManager())
            ->setDatabase($this->database)
            ->setCategoryFactory($this->categoryFactory)
            ->setCategoryHelper($this->categoryHelper)
            ->setConfigHelper($this->configHelper)
            ->setObject2CategoryFactory($this->object2CategoryFactory);
    }

    public function testGetCategoryNames()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|oxarticle $product */
        $product = $this->getMockBuilder(oxarticle::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())
            ->method('getCategoryIds')
            ->willReturn([1]);
        $this->categoryFactory->expects($this->once())
            ->method('create')
            ->willReturn(
                $category = $this->getMockBuilder(oxcategory::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $category->expects($this->once())
            ->method('getTitle')
            ->willReturn('some-category-name');
        $this->assertNotEmpty($this->manager->getCategoryNames($product));
    }

    public function testSetCategories()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|oxarticle $product */
        $product = $this->getMockBuilder(oxarticle::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|ProductRequestEntity $requestEntity */
        $requestEntity = $this->getMockBuilder(ProductRequestEntity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryHelper->expects($this->at(0))
            ->method('getDefaultCategory')
            ->willReturn(
                $this->getMockBuilder(oxcategory::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $requestEntity->expects($this->once())
            ->method('__call')
            ->willReturn([
                $requestCategoryEntityMatched = $this->getMockBuilder(ProductCategoryEntity::class)
                    ->disableOriginalConstructor()
                    ->getMock(),
                $requestCategoryEntity = $this->getMockBuilder(ProductCategoryEntity::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            ]);
        $requestCategoryEntityMatched->expects($this->once())
            ->method('__call')
            ->willReturn('some-title');
        $requestCategoryEntity->expects($this->once())
            ->method('__call')
            ->willReturn('some-other-title');
        $this->categoryHelper->expects($this->at(1))
            ->method('getCategoryByTitle')
            ->willReturn(
                $this->getMockBuilder(oxcategory::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->categoryFactory->expects($this->once())
            ->method('create')
            ->willReturn(
                $this->getMockBuilder(oxcategory::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->configHelper->expects($this->once())
            ->method('getLanguageIds')
            ->willReturn([0]);
        $this->object2CategoryFactory->expects($this->once())
            ->method('create')
            ->willReturn(
                $this->getMockBuilder(oxBase::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->configHelper->expects($this->once())
            ->method('generateUID')
            ->willReturn('some-uid');
        $this->manager->setCategories($product, $requestEntity);
    }
}
