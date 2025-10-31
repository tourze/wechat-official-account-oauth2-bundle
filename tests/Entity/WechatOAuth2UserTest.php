<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2User;

/**
 * @internal
 */
#[CoversClass(WechatOAuth2User::class)]
final class WechatOAuth2UserTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new WechatOAuth2User();
    }

    /** @return iterable<string, array{string, mixed}> */
    public static function propertiesProvider(): iterable
    {
        return [
            'openId' => ['openId', 'test_value'],
            'accessToken' => ['accessToken', 'test_value'],
            'refreshToken' => ['refreshToken', 'test_value'],
            'expiresIn' => ['expiresIn', 123],
        ];
    }

    private WechatOAuth2User $user;

    private WechatOAuth2Config $config;

    public function testBasicProperties(): void
    {
        $this->user->setOpenId('test_openid');
        $this->assertEquals('test_openid', $this->user->getOpenId());

        $this->user->setUnionId('test_unionid');
        $this->assertEquals('test_unionid', $this->user->getUnionId());

        $this->user->setNickname('Test User');
        $this->assertEquals('Test User', $this->user->getNickname());
    }

    public function testUserInfo(): void
    {
        $this->user->setSex(1);
        $this->assertEquals(1, $this->user->getSex());
        $this->assertEquals('男', $this->user->getSexText());

        $this->user->setSex(2);
        $this->assertEquals('女', $this->user->getSexText());

        $this->user->setSex(0);
        $this->assertEquals('未知', $this->user->getSexText());

        $this->user->setProvince('Beijing');
        $this->assertEquals('Beijing', $this->user->getProvince());

        $this->user->setCity('Beijing');
        $this->assertEquals('Beijing', $this->user->getCity());

        $this->user->setCountry('China');
        $this->assertEquals('China', $this->user->getCountry());

        $this->user->setHeadimgurl('https://example.com/avatar.jpg');
        $this->assertEquals('https://example.com/avatar.jpg', $this->user->getHeadimgurl());
    }

    public function testPrivilege(): void
    {
        $privilege = ['privilege1', 'privilege2'];
        $this->user->setPrivilege($privilege);
        $this->assertEquals($privilege, $this->user->getPrivilege());
    }

    public function testTokenManagement(): void
    {
        $this->user->setAccessToken('access_token_123');
        $this->assertEquals('access_token_123', $this->user->getAccessToken());

        $this->user->setRefreshToken('refresh_token_123');
        $this->assertEquals('refresh_token_123', $this->user->getRefreshToken());

        $this->user->setExpiresIn(7200);
        $this->assertEquals(7200, $this->user->getExpiresIn());

        // Check that expires_time is set correctly
        $expectedExpiry = new \DateTime('+7200 seconds');
        $actualExpiry = $this->user->getAccessTokenExpiresTime();

        // Allow 1 second difference for test execution time
        $this->assertLessThan(1, abs($expectedExpiry->getTimestamp() - $actualExpiry->getTimestamp()));
    }

    public function testTokenExpiration(): void
    {
        // Set token to expire in future
        $this->user->setExpiresIn(3600);
        $this->assertFalse($this->user->isTokenExpired());

        // Manually set expiration to past
        $reflection = new \ReflectionClass($this->user);
        $property = $reflection->getProperty('accessTokenExpiresTime');
        $property->setAccessible(true);
        $property->setValue($this->user, new \DateTime('-1 hour'));

        $this->assertTrue($this->user->isTokenExpired());
    }

    public function testScope(): void
    {
        $this->user->setScope('snsapi_userinfo');
        $this->assertEquals('snsapi_userinfo', $this->user->getScope());
    }

    public function testRawData(): void
    {
        $rawData = [
            'openid' => 'test_openid',
            'nickname' => 'Test User',
            'extra_field' => 'extra_value',
        ];

        $this->user->setRawData($rawData);
        $this->assertEquals($rawData, $this->user->getRawData());
    }

    public function testConfig(): void
    {
        $this->assertSame($this->config, $this->user->getConfig());

        // 使用具体的 WechatOAuth2Config 类进行 Mock（第二次使用）
        // 理由1：与 setUp 中相同，WechatOAuth2Config 是 Entity 类，无相应接口
        // 理由2：测试配置切换功能，Mock 可以创建不同的配置实例
        // 理由3：确保测试的独立性，不依赖配置对象的具体实现
        // 替代方案评估：可以使用工厂模式创建配置，但对于单元测试过于复杂
        $newConfig = $this->createMock(WechatOAuth2Config::class);
        $this->user->setConfig($newConfig);
        $this->assertSame($newConfig, $this->user->getConfig());
    }

    protected function setUp(): void
    {
        parent::setUp();

        // 使用具体的 WechatOAuth2Config 类进行 Mock
        // 理由1：WechatOAuth2Config 是 Entity 类，没有定义相应的接口
        // 理由2：测试需要一个配置对象来初始化用户，Mock 可以简化测试设置
        // 理由3：避免在测试中依赖完整的配置对象创建和数据库依赖
        // 替代方案评估：可以创建真实的配置实例，但会增加测试复杂性和外部依赖
        $this->config = $this->createMock(WechatOAuth2Config::class);
        $this->user = new WechatOAuth2User();
        $this->user->setConfig($this->config);
    }
}
