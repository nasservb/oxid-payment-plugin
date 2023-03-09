<?php

class PayeverCategoryHelperTest extends \PHPUnit\Framework\TestCase
{
    use DatabaseMockTrait;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverCategoryFactory */
    protected $categoryFactory;

    /** @var PayeverCategoryHelper */
    protected $helper;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->buildDatabaseMock();
        $this->categoryFactory = $this->getMockBuilder(PayeverCategoryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper = (new PayeverCategoryHelper)
            ->setDatabase($this->database)
            ->setCategoryFactory($this->categoryFactory);
    }

    public function testGetCategoryByTitle()
    {
        $this->database->expects($this->once())
            ->method('getRow')
            ->willReturn([0 => 'some-uid']);
        $this->categoryFactory->expects($this->once())
            ->method('create')
            ->willReturn(
                $this->getMockBuilder(oxcategory::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->assertNotEmpty($this->helper->getCategoryByTitle('some-title'));
    }

    public function testGetDefaultCategory()
    {
        $this->database->expects($this->once())
            ->method('getRow')
            ->willReturn(['oxid' => 'some-uid']);
        $this->categoryFactory->expects($this->once())
            ->method('create')
            ->willReturn(
                $this->getMockBuilder(oxcategory::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->assertNotEmpty($this->helper->getDefaultCategory());
    }
}
