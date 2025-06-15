<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2User;

class WechatOAuth2UserTest extends TestCase
{
    private WechatOAuth2User $user;
    private WechatOAuth2Config $config;

    public function testBasicProperties(): void
    {
        $this->user->setOpenid('test_openid');
        $this->assertEquals('test_openid', $this->user->getOpenid());

        $this->user->setUnionid('test_unionid');
        $this->assertEquals('test_unionid', $this->user->getUnionid());

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
            'extra_field' => 'extra_value'
        ];

        $this->user->setRawData($rawData);
        $this->assertEquals($rawData, $this->user->getRawData());
    }

    public function testConfig(): void
    {
        $this->assertSame($this->config, $this->user->getConfig());

        $newConfig = $this->createMock(WechatOAuth2Config::class);
        $this->user->setConfig($newConfig);
        $this->assertSame($newConfig, $this->user->getConfig());
    }

    protected function setUp(): void
    {
        $this->config = $this->createMock(WechatOAuth2Config::class);
        $this->user = new WechatOAuth2User();
        $this->user->setConfig($this->config);
    }
}