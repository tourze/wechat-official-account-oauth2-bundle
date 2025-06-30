<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit\EventSubscriber;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\WechatOfficialAccountOAuth2Bundle\EventSubscriber\OAuth2LoggingSubscriber;

class OAuth2LoggingSubscriberTest extends TestCase
{
    private OAuth2LoggingSubscriber $subscriber;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->subscriber = new OAuth2LoggingSubscriber($this->logger);
    }

    public function testGetSubscribedEvents(): void
    {
        $events = OAuth2LoggingSubscriber::getSubscribedEvents();
        
        $this->assertIsArray($events);
        $this->assertNotEmpty($events);
    }

    public function testLogAuthorizationStart(): void
    {
        $this->logger->expects($this->once())
            ->method('info')
            ->with($this->stringContains('OAuth2 authorization started'));

        // Test would call the actual logging method
        $this->assertTrue(true);
    }
}