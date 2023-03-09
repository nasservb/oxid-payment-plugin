<?php

class payeverProductsImportTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|oxconfig */
    protected $config;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverImportManager */
    protected $importManager;

    /** @var payeverProductsImport */
    protected $controller;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->config = $this->getMockBuilder(oxconfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->importManager = $this->getMockBuilder(PayeverImportManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = (new payeverProductsImport())
            ->setImportManager($this->importManager)
            ->setEnableOutput(false);
        $this->controller->setConfig($this->config);
    }

    public function testImportRender()
    {
        $this->importManager->expects($this->once())
            ->method('import');
        $this->controller->import();
        $this->controller->render();
    }

    public function testImportRenderCaseErrors()
    {
        $this->importManager->expects($this->once())
            ->method('import');
        $this->importManager->expects($this->once())
            ->method('getErrors')
            ->willReturn(['some' => 'error']);
        $this->controller->import();
        $this->controller->render();
    }
}
