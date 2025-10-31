<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\OAuth2Exception;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\WechatOAuth2ConfigurationException;
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\WechatOAuth2Service;

/**
 * WechatOAuth2Service 单元测试类
 *
 * 单元测试说明：
 * 本测试类使用Mock对象隔离外部依赖，专注于测试服务的业务逻辑。
 *
 * 设计原则：
 * - 使用Mock对象隔离外部依赖
 * - 测试各种场景（成功、失败、边界情况）
 * - 确保方法的行为符合预期
 * - 保持测试的独立性和可重复性
 *
 * @internal
 */
#[CoversClass(WechatOAuth2Service::class)]
#[RunTestsInSeparateProcesses]
final class WechatOAuth2ServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 初始化测试环境
    }

    public function testServiceCanBeInstantiated(): void
    {
        // 验证服务可以从容器中获取
        $service = self::getService(WechatOAuth2Service::class);
        $this->assertInstanceOf(WechatOAuth2Service::class, $service);
    }

    public function testCleanupExpiredStatesReturnsInteger(): void
    {
        // 获取服务实例
        $service = self::getService(WechatOAuth2Service::class);

        // 测试清理过期状态
        $result = $service->cleanupExpiredStates();

        // 结果应该是非负整数
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testRefreshExpiredTokensReturnsInteger(): void
    {
        // 获取服务实例
        $service = self::getService(WechatOAuth2Service::class);

        // 测试刷新过期令牌
        $result = $service->refreshExpiredTokens();

        // 结果应该是非负整数
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testGenerateAuthorizationUrl(): void
    {
        // 获取服务实例
        $service = self::getService(WechatOAuth2Service::class);

        // 测试生成授权URL
        try {
            $url = $service->generateAuthorizationUrl();
            $this->assertIsString($url);
            $this->assertStringContainsString('https://open.weixin.qq.com/connect/oauth2/authorize', $url);
        } catch (WechatOAuth2ConfigurationException $e) {
            // 如果没有配置，则跳过测试
            self::markTestSkipped('No valid Wechat OAuth2 configuration found: ' . $e->getMessage());
        }
    }

    public function testHandleCallback(): void
    {
        // 获取服务实例
        $service = self::getService(WechatOAuth2Service::class);

        // 测试处理回调 - 由于需要真实的code和state，这里测试异常情况
        $this->expectException(OAuth2Exception::class);
        $this->expectExceptionMessage('Invalid or expired state');

        $service->handleCallback('invalid_code', 'invalid_state');
    }

    public function testRefreshToken(): void
    {
        // 获取服务实例
        $service = self::getService(WechatOAuth2Service::class);

        // 测试刷新令牌 - 使用不存在的openid
        $result = $service->refreshToken('non_existent_openid');

        // 应该返回false，因为用户不存在
        $this->assertIsBool($result);
        $this->assertFalse($result);
    }

    public function testValidateAccessToken(): void
    {
        // 获取服务实例
        $service = self::getService(WechatOAuth2Service::class);

        // 测试验证访问令牌 - 使用无效的token
        $result = $service->validateAccessToken('invalid_token', 'invalid_openid');

        // 应该返回false
        $this->assertIsBool($result);
        $this->assertFalse($result);
    }
}
