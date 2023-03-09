<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PayeverModuleActivateCommandTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|oxModule */
    protected $module;

    /** @var PayeverModuleActivateCommand */
    protected $command;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->module = $this->getMockBuilder(oxmodule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->command = (new PayeverModuleActivateCommand())
            ->setModule($this->module);
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
        $this->module->expects($this->once())
            ->method('isActive')
            ->willReturn(true);
        $this->command->run($input, $output);
    }
}
