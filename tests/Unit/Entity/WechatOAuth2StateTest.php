<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2State;

class WechatOAuth2StateTest extends TestCase
{
    private WechatOAuth2Config $config;
    private WechatOAuth2State $state;

    public function testConstructor(): void
    {
        $this->assertEquals('test_state_123', $this->state->getState());
        $this->assertSame($this->config, $this->state->getConfig());
        $this->assertInstanceOf(\DateTimeInterface::class, $this->state->getExpiresTime());
        $this->assertFalse($this->state->isUsed());
        $this->assertNull($this->state->getUsedTime());
    }

    public function testSessionId(): void
    {
        $this->assertNull($this->state->getSessionId());

        $this->state->setSessionId('session_123');
        $this->assertEquals('session_123', $this->state->getSessionId());

        $this->state->setSessionId(null);
        $this->assertNull($this->state->getSessionId());
    }

    public function testMarkAsUsed(): void
    {
        $this->assertFalse($this->state->isUsed());
        $this->assertNull($this->state->getUsedTime());

        $this->state->markAsUsed();

        $this->assertTrue($this->state->isUsed());
        $this->assertInstanceOf(\DateTimeInterface::class, $this->state->getUsedTime());
    }

    public function testExpiresTime(): void
    {
        $futureDate = new \DateTime('+10 minutes');
        $this->state->setExpiresTime($futureDate);

        $this->assertEquals($futureDate, $this->state->getExpiresTime());
        $this->assertFalse($this->state->isExpired());

        $pastDate = new \DateTime('-10 minutes');
        $this->state->setExpiresTime($pastDate);

        $this->assertTrue($this->state->isExpired());
    }

    public function testIsValidState(): void
    {
        // Fresh state should be valid
        $this->assertTrue($this->state->isValidState());

        // Used state should not be valid
        $this->state->markAsUsed();
        $this->assertFalse($this->state->isValidState());

        // Reset for expiration test
        $this->state = new WechatOAuth2State('test_state_456', $this->config);

        // Expired state should not be valid
        $this->state->setExpiresTime(new \DateTime('-1 minute'));
        $this->assertFalse($this->state->isValidState());
    }

    protected function setUp(): void
    {
        $this->config = $this->createMock(WechatOAuth2Config::class);
        $this->state = new WechatOAuth2State('test_state_123', $this->config);
    }
}