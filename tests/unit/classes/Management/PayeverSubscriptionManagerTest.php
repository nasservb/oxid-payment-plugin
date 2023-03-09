<?php

use Payever\ExternalIntegration\Core\Http\Response;
use Payever\ExternalIntegration\Core\PseudoRandomStringGenerator;
use Payever\ExternalIntegration\ThirdParty\Http\ResponseEntity\SubscriptionResponseEntity;
use Payever\ExternalIntegration\ThirdParty\ThirdPartyApiClient;
use Psr\Log\LoggerInterface;

class PayeverSubscriptionManagerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|PayeverConfigHelper */
    protected $configHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ThirdPartyApiClient */
    protected $thirdPartyClient;

    /** @var \PHPUnit_Framework_MockObject_MockObject|PseudoRandomStringGenerator */
    protected $randomSourceGenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface */
    protected $logger;

    /** @var PayeverSubscriptionManager */
    protected $manager;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->configHelper = $this->getMockBuilder(PayeverConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->thirdPartyClient = $this->getMockBuilder(ThirdPartyApiClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->randomSourceGenerator = $this->getMockBuilder(PseudoRandomStringGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager = (new PayeverSubscriptionManager())
            ->setConfigHelper($this->configHelper)
            ->setThirdPartyApiClient($this->thirdPartyClient)
            ->setRandomSourceGenerator($this->randomSourceGenerator)
            ->setLogger($this->logger);
    }

    public function testToggleSubscription()
    {
        $this->thirdPartyClient->expects($this->once())
            ->method('subscribe');
        $this->thirdPartyClient->expects($this->once())
            ->method('getSubscriptionStatus')
            ->willReturn(
                $subscriptionRecordResponse = $this->getMockBuilder(Response::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $subscriptionRecordResponse->expects($this->once())
            ->method('getResponseEntity')
            ->willReturn(
                $this->getMockBuilder(SubscriptionResponseEntity::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $this->manager->toggleSubscription(true);
    }

    public function testToggleSubscriptionCaseUnsubscribe()
    {
        $this->thirdPartyClient->expects($this->once())
            ->method('unsubscribe');
        $this->manager->toggleSubscription(false);
    }

    public function testToggleSubscriptionCaseException()
    {
        $this->thirdPartyClient->expects($this->once())
            ->method('subscribe')
            ->willThrowException(new \Exception());
        $this->manager->toggleSubscription(true);
    }

    public function testGetSupportedActions()
    {
        $this->assertNotEmpty($this->manager->getSupportedActions());
    }
}
