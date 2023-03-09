<?php

require_once dirname(__FILE__) . '/../../mock/classes/ActionHandler/PayeverAbstractActionHandlerMock.php';

use Payever\ExternalIntegration\ThirdParty\Action\ActionPayload;
use Payever\ExternalIntegration\ThirdParty\Action\ActionResult;
use Psr\Log\LoggerInterface;

class PayeverAbstractActionHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface */
    protected $logger;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ActionResult */
    protected $actionResult;

    /** @var PayeverAbstractActionHandlerMock */
    protected $actionHandler;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->actionHandler = (new PayeverAbstractActionHandlerMock())
            ->setLogger($this->logger);
        $this->actionResult = $this->getMockBuilder(ActionResult::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testHandle()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ActionPayload $actionPayload */
        $actionPayload = $this->getMockBuilder(ActionPayload::class)
            ->disableOriginalConstructor()
            ->getMock();
        $actionPayload->expects($this->once())
            ->method('getPayloadEntity')
            ->willReturn(
                $requestEntity = $this->getMockBuilder(
                    Payever\ExternalIntegration\Core\Http\RequestEntity::class
                )
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $requestEntity->expects($this->at(0))
            ->method('__call')
            ->willReturn('some-sku');
        $this->actionHandler->handle(
            $actionPayload,
            $this->actionResult
        );
    }

    public function testHandleCaseInvalidEntity()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ActionPayload $actionPayload */
        $actionPayload = $this->getMockBuilder(ActionPayload::class)
            ->disableOriginalConstructor()
            ->getMock();
        $actionPayload->expects($this->once())
            ->method('getPayloadEntity')
            ->willReturn(
                $this->getMockBuilder(
                    Payever\ExternalIntegration\Core\Http\RequestEntity::class
                )
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->actionResult->expects($this->once())
            ->method('incrementSkipped');
        $this->actionHandler->handle(
            $actionPayload,
            $this->actionResult
        );
    }

    public function testHandleCaseException()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ActionPayload $actionPayload */
        $actionPayload = $this->getMockBuilder(ActionPayload::class)
            ->disableOriginalConstructor()
            ->getMock();
        $actionPayload->expects($this->once())
            ->method('getPayloadEntity')
            ->willReturn(
                $requestEntity = $this->getMockBuilder(
                    Payever\ExternalIntegration\Core\Http\RequestEntity::class
                )
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $requestEntity->expects($this->at(0))
            ->method('__call')
            ->willReturn('some-sku');
        $this->actionHandler->processCallback = function () {
            throw new \Exception();
        };
        $this->actionResult->expects($this->once())
            ->method('incrementSkipped');
        $this->actionHandler->handle(
            $actionPayload,
            $this->actionResult
        );
    }
}
