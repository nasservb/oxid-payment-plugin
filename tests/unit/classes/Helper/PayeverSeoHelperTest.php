<?php

class PayeverSeoHelperTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverCategoryHelper */
    protected $categoryHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverConfigHelper */
    protected $configHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|oxconfig */
    protected $config;

    /** @var \PHPUnit_Framework_MockObject_MockObject|oxSeoEncoderArticle */
    protected $productSeoEncoder;

    /** @var PayeverSeoHelper */
    protected $helper;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->categoryHelper = $this->getMockBuilder(PayeverCategoryHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configHelper = $this->getMockBuilder(PayeverConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder(oxconfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productSeoEncoder = $this->getMockBuilder(oxSeoEncoderArticle::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper = (new PayeverSeoHelper())
            ->setCategoryHelper($this->categoryHelper)
            ->setConfigHelper($this->configHelper)
            ->setConfig($this->config)
            ->setProductSeoEncoder($this->productSeoEncoder);
    }

    public function testProcessProductSeo()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|oxarticle $product */
        $product = $this->getMockBuilder(oxarticle::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config->expects($this->once())
            ->method('getShopId')
            ->willReturn('oxidstandardshop');
        $this->categoryHelper->expects($this->once())
            ->method('getDefaultCategory')
            ->willReturn(
                $this->getMockBuilder(oxcategory::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->configHelper->expects($this->once())
            ->method('getLanguageIds')
            ->willReturn([0]);
        $this->productSeoEncoder->expects($this->once())
            ->method('markAsExpired');
        $this->productSeoEncoder->expects($this->once())
            ->method('addSeoEntry');
        $this->helper->processProductSeo($product);
    }
}
