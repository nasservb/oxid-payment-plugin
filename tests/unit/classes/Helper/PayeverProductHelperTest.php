<?php

class PayeverProductHelperTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverArticleFactory */
    protected $articleFactory;

    /** @var PayeverProductHelper */
    protected $helper;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->articleFactory = $this->getMockBuilder(PayeverArticleFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper = (new PayeverProductHelper())
            ->setArticleFactory($this->articleFactory);
    }

    public function testGetProductById()
    {
        $this->articleFactory->expects($this->once())
            ->method('create')
            ->willReturn(
                $this->getMockBuilder(oxarticle::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->assertNotEmpty($this->helper->getProductById('some-uid'));
    }

    public function testGetProductBySku()
    {
        $this->articleFactory->expects($this->once())
            ->method('create')
            ->willReturn(
                $this->getMockBuilder(oxarticle::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->assertNotEmpty($this->helper->getProductBySku('some-sku'));
    }
}
