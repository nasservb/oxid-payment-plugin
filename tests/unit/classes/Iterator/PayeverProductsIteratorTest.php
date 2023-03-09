<?php

class PayeverProductsIteratorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|oxarticle */
    protected $item;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverProductTransformer */
    protected $productTransformer;

    /** @var PayeverProductsIterator */
    protected $iterator;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->item = $this->getMockBuilder(oxarticle::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productTransformer = $this->getMockBuilder(PayeverProductTransformer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->iterator = new PayeverProductsIterator([$this->item]);
        $this->iterator->setProductTransformer($this->productTransformer);
    }

    public function testCurrent()
    {
        $this->productTransformer->expects($this->once())
            ->method('transformFromOxidIntoPayever')
            ->willReturn($this->item);
        $this->assertNotEmpty($this->iterator->current());
    }
}
