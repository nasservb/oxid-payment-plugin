<?php

use Payever\ExternalIntegration\Products\Http\RequestEntity\ProductRequestEntity;
use Psr\Log\LoggerInterface;

class PayeverGalleryManagerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|oxconfig */
    protected $config;

    /** @var \PHPUnit_Framework_MockObject_MockObject|oxutilsfile */
    protected $fileUtil;

    /** @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface */
    protected $logger;

    /** @var PayeverGalleryManager */
    protected $manager;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->config = $this->getMockBuilder(oxconfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileUtil = $this->getMockBuilder(oxutilsfile::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager = (new PayeverGalleryManager())
            ->setConfig($this->config)
            ->setFileUtil($this->fileUtil)
            ->setLogger($this->logger)
            ->setSkipFs(true);
    }

    public function testGetGallery()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|oxarticle $product */
        $product = $this->getMockBuilder(oxarticle::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())
            ->method('getPictureGallery')
            ->willReturn(['Pics']);
        $this->manager->getGallery($product);
    }

    public function testGetGalleryCaseNoPics()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|oxarticle $product */
        $product = $this->getMockBuilder(oxarticle::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())
            ->method('getPictureGallery')
            ->willReturn([]);
        $this->manager->getGallery($product);
    }

    public function testAppendGallery()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|oxarticle $product */
        $product = $this->getMockBuilder(oxarticle::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|ProductRequestEntity $requestEntity */
        $requestEntity = $this->getMockBuilder(ProductRequestEntity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $requestEntity->expects($this->at(0))
            ->method('__call')
            ->willReturn([
                'https://some.host/b52f908e-4b69-40ff-ac07-22e4c183399e.png',
                'https://some.host/b62f908e-4b69-40ff-ac07-22e4c183399e.png',
                'https://some.host/b72f908e-4b69-40ff-ac07-22e4c183399e.png',
                'https://some.host/b82f908e-4b69-40ff-ac07-22e4c183399e.png',
            ]);
        $requestEntity->expects($this->at(0))
            ->method('__call')
            ->willReturn([
                'b52f908e-4b69-40ff-ac07-22e4c183399e',
                'b62f908e-4b69-40ff-ac07-22e4c183399e',
                'b72f908e-4b69-40ff-ac07-22e4c183399e',
                'b82f908e-4b69-40ff-ac07-22e4c183399e',
            ]);
        $requestEntity->expects($this->at(0))
            ->method('__call')
            ->willReturn([
                'b52f908e-4b69-40ff-ac07-22e4c183399e',
                'b62f908e-4b69-40ff-ac07-22e4c183399e.jpg',
                'b72f908e-4b69-40ff-ac07-22e4c183399e.jpg',
                'b82f908e-4b69-40ff-ac07-22e4c183399e.jpg',
            ]);
        $product->expects($this->once())
            ->method('getPictureGallery')
            ->willReturn([
                'Pics' => [
                    'https://some.host/b12f908e-4b69-40ff-ac07-22e4c183399e.png',
                    'https://some.host/b22f908e-4b69-40ff-ac07-22e4c183399e.png',
                    'https://some.host/b32f908e-4b69-40ff-ac07-22e4c183399e.png',
                    'https://some.host/b42f908e-4b69-40ff-ac07-22e4c183399e.png',
                    'https://some.host/b52f908e-4b69-40ff-ac07-22e4c183399e.png',
                    'https://some.host/b62f908e-4b69-40ff-ac07-22e4c183399e.png',
                ],
            ]);
        $this->fileUtil->expects($this->once())
            ->method('processFiles');
        $this->manager->appendGallery($product, $requestEntity);
    }
}
