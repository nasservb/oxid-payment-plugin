<?php

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PayeverSyncQueueConsumeCommandTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverActionQueueManager */
    protected $actionQueueManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverSynchronizationManager */
    protected $synchronizationManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface */
    protected $logger;

    /** @var PayeverSyncQueueConsumeCommand */
    protected $command;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->actionQueueManager = $this->getMockBuilder(PayeverActionQueueManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->synchronizationManager = $this->getMockBuilder(PayeverSynchronizationManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->synchronizationManager->expects($this->any())
            ->method('setIsInstantMode')
            ->willReturn($this->synchronizationManager);
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->command = (new PayeverSyncQueueConsumeCommand())
            ->setActionQueueManager($this->actionQueueManager)
            ->setSynchronizationManager($this->synchronizationManager)
            ->setLogger($this->logger);
    }

    public function testExecute()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|InputInterface $input */
        $input = $this->getMockBuilder(InputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|OutputInterface $output */
        $output = $this->getMockBuilder(OutputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->synchronizationManager->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->actionQueueManager->expects($this->once())
            ->method('getItems')
            ->willReturn([
                $item = $this->getMockBuilder(payeversynchronizationqueue::class)
                    ->disableOriginalConstructor()
                    ->getMock(),
                $this->getMockBuilder(payeversynchronizationqueue::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            ]);
        $item->expects($this->once())
            ->method('__get')
            ->willReturn($field = new \stdClass());
        $field->rawValue = \json_encode(['some-data']);
        $this->command->run($input, $output);
    }

    public function testExecuteCaseRetry()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|InputInterface $input */
        $input = $this->getMockBuilder(InputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|OutputInterface $output */
        $output = $this->getMockBuilder(OutputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->synchronizationManager->expects($this->any())
            ->method('isEnabled')
            ->willReturn(true);
        $this->actionQueueManager->expects($this->once())
            ->method('getItems')
            ->willReturn([
                $item = $this->getMockBuilder(payeversynchronizationqueue::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            ]);
        $item->expects($this->once())
            ->method('__get')
            ->willReturn($field = new \stdClass());
        $field->rawValue = \json_encode(['some-data']);
        $this->synchronizationManager->expects($this->at(1))
            ->method('handleAction')
            ->willThrowException(new \Exception());
        $this->command->run($input, $output);
    }

    public function testExecuteCaseMaxRetries()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|InputInterface $input */
        $input = $this->getMockBuilder(InputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|OutputInterface $output */
        $output = $this->getMockBuilder(OutputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->synchronizationManager->expects($this->any())
            ->method('isEnabled')
            ->willReturn(true);
        $this->actionQueueManager->expects($this->once())
            ->method('getItems')
            ->willReturn([
                $item = $this->getMockBuilder(oxarticle::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            ]);
        $item->expects($this->at(2))
            ->method('getFieldData')
            ->willReturn(2);
        $this->synchronizationManager->expects($this->any())
            ->method('handleAction')
            ->willThrowException(new \Exception());
        $this->command->run($input, $output);
    }

    public function testExecuteCaseDisabled()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|InputInterface $input */
        $input = $this->getMockBuilder(InputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|OutputInterface $output */
        $output = $this->getMockBuilder(OutputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->synchronizationManager->expects($this->any())
            ->method('isEnabled')
            ->willReturn(false);
        $this->command->run($input, $output);
    }
}
