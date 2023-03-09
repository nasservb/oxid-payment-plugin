<?php

class PayeverInventoryIteratorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|oxarticle */
    protected $item;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverInventoryTransformer */
    protected $inventoryTransformer;

    /** @var PayeverInventoryIterator */
    protected $iterator;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->item = $this->getMockBuilder(oxarticle::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inventoryTransformer = $this->getMockBuilder(PayeverInventoryTransformer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->iterator = new PayeverInventoryIterator([$this->item]);
        $this->iterator->setInventoryTransformer($this->inventoryTransformer);
    }

    public function testCurrent()
    {
        $this->inventoryTransformer->expects($this->once())
            ->method('transformCreatedOxidIntoPayever')
            ->willReturn($this->item);
        $this->assertNotEmpty($this->iterator->current());
    }
}
