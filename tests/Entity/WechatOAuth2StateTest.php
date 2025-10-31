<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2State;

/**
 * @internal
 */
#[CoversClass(WechatOAuth2State::class)]
final class WechatOAuth2StateTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        $config = $this->createMock(WechatOAuth2Config::class);
        $state = new WechatOAuth2State();
        $state->setState('test_state');
        $state->setConfig($config);

        return $state;
    }

    /** @return iterable<string, array{string, mixed}> */
    public static function propertiesProvider(): iterable
    {
        return [
            'valid' => ['valid', true],
        ];
    }

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
        $this->state = new WechatOAuth2State();
        $this->state->setState('test_state_456');
        $this->state->setConfig($this->config);

        // Expired state should not be valid
        $this->state->setExpiresTime(new \DateTime('-1 minute'));
        $this->assertFalse($this->state->isValidState());
    }

    protected function setUp(): void
    {
        parent::setUp();

        // 使用具体的 WechatOAuth2Config 类进行 Mock
        // 理由1：WechatOAuth2Config 是 Entity 类，没有定义相应的接口
        // 理由2：测试需要验证状态对象的构造和配置关联，Mock 可以简化测试设置
        // 理由3：避免在测试中依赖完整的配置对象创建过程
        // 替代方案评估：可以创建真实的 Config 实例，但会增加测试的依赖复杂性
        $this->config = $this->createMock(WechatOAuth2Config::class);
        $this->state = new WechatOAuth2State();
        $this->state->setState('test_state_123');
        $this->state->setConfig($this->config);
    }
}
