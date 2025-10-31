<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AccessToken;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AuthorizationCode;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\OAuth2AuthorizationException;
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\OAuth2AuthorizationService;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * OAuth2授权服务集成测试
 *
 * @internal
 */
#[CoversClass(OAuth2AuthorizationService::class)]
#[RunTestsInSeparateProcesses]
final class OAuth2AuthorizationServiceTest extends AbstractIntegrationTestCase
{
    private OAuth2AuthorizationService $service;

    public function testBuildWechatAuthUrl(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_secret');

        $url = $this->service->buildWechatAuthUrl(
            $account,
            'snsapi_base',
            'https://example.com/callback'
        );

        $this->assertStringContainsString('open.weixin.qq.com/connect/oauth2/authorize', $url);
        $this->assertStringContainsString('appid=test_app_id', $url);
        $this->assertStringContainsString('scope=snsapi_base', $url);
        $this->assertStringContainsString('redirect_uri=', $url);
        $this->assertStringContainsString('#wechat_redirect', $url);
    }

    protected function onSetUp(): void
    {
        $service = self::getContainer()->get(OAuth2AuthorizationService::class);
        $this->assertInstanceOf(OAuth2AuthorizationService::class, $service);
        $this->service = $service;
    }

    public function testCreateAuthorizationCode(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_secret');

        $openid = 'test_openid';
        $unionid = 'test_unionid';
        $redirectUri = 'https://example.com/callback';
        $scope = 'snsapi_userinfo';
        $state = 'test_state';

        $authCode = $this->service->createAuthorizationCode(
            $account,
            $openid,
            $unionid,
            $redirectUri,
            $scope,
            $state
        );

        $this->assertInstanceOf(OAuth2AuthorizationCode::class, $authCode);
        $this->assertNotNull($authCode->getCode());
        $this->assertStringStartsWith('AC_', $authCode->getCode());
        $this->assertSame($openid, $authCode->getOpenId());
        $this->assertSame($unionid, $authCode->getUnionId());
        $this->assertSame($redirectUri, $authCode->getRedirectUri());
        $this->assertSame($scope, $authCode->getScopes());
        $this->assertSame($state, $authCode->getState());
        $this->assertSame($account, $authCode->getWechatAccount());
        $this->assertInstanceOf(\DateTimeInterface::class, $authCode->getExpiresAt());
    }

    public function testExchangeCodeForTokenWithValidCode(): void
    {
        // Create a mock authorization code
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_secret');

        $authCode = $this->service->createAuthorizationCode(
            $account,
            'test_openid',
            'test_unionid',
            'https://example.com/callback',
            'snsapi_userinfo',
            'test_state'
        );

        $code = $authCode->getCode();
        $this->assertNotNull($code);
        $accessToken = $this->service->exchangeCodeForToken(
            $code,
            'https://example.com/callback',
            $account
        );

        $this->assertInstanceOf(OAuth2AccessToken::class, $accessToken);
        $this->assertNotNull($accessToken->getAccessToken());
        $this->assertNotNull($accessToken->getRefreshToken());
        $this->assertStringStartsWith('AT_', $accessToken->getAccessToken());
        $this->assertStringStartsWith('RT_', $accessToken->getRefreshToken());
        $this->assertSame('test_openid', $accessToken->getOpenId());
        $this->assertSame('test_unionid', $accessToken->getUnionId());
        $this->assertSame('snsapi_userinfo', $accessToken->getScopes());
        $this->assertSame($account, $accessToken->getWechatAccount());
        $this->assertInstanceOf(\DateTimeInterface::class, $accessToken->getAccessTokenExpiresAt());
        $this->assertInstanceOf(\DateTimeInterface::class, $accessToken->getRefreshTokenExpiresAt());
    }

    public function testExchangeCodeForTokenWithInvalidCode(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_secret');

        $this->expectException(OAuth2AuthorizationException::class);
        $this->expectExceptionMessage('无效的授权码');

        $this->service->exchangeCodeForToken(
            'invalid_code',
            'https://example.com/callback',
            $account
        );
    }

    public function testExchangeCodeForTokenWithWrongRedirectUri(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_secret');

        $authCode = $this->service->createAuthorizationCode(
            $account,
            'test_openid',
            'test_unionid',
            'https://example.com/callback',
            'snsapi_userinfo',
            'test_state'
        );

        $this->expectException(OAuth2AuthorizationException::class);
        $this->expectExceptionMessage('重定向URI不匹配');

        $code = $authCode->getCode();
        $this->assertNotNull($code);
        $this->service->exchangeCodeForToken(
            $code,
            'https://different.com/callback',
            $account
        );
    }

    public function testRefreshAccessTokenWithValidRefreshToken(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_secret');

        // Create initial token
        $authCode = $this->service->createAuthorizationCode(
            $account,
            'test_openid',
            'test_unionid',
            'https://example.com/callback',
            'snsapi_userinfo',
            'test_state'
        );

        $code = $authCode->getCode();
        $this->assertNotNull($code);
        $originalToken = $this->service->exchangeCodeForToken(
            $code,
            'https://example.com/callback',
            $account
        );

        // Refresh the token
        $refreshToken = $originalToken->getRefreshToken();
        $this->assertNotNull($refreshToken);
        $newToken = $this->service->refreshAccessToken(
            $refreshToken,
            $account
        );

        $this->assertInstanceOf(OAuth2AccessToken::class, $newToken);
        $this->assertNotNull($newToken->getAccessToken());
        $this->assertNotNull($newToken->getRefreshToken());
        $this->assertStringStartsWith('AT_', $newToken->getAccessToken());
        $this->assertStringStartsWith('RT_', $newToken->getRefreshToken());
        $this->assertNotSame($originalToken->getAccessToken(), $newToken->getAccessToken());
        $this->assertNotSame($originalToken->getRefreshToken(), $newToken->getRefreshToken());
        $this->assertSame($originalToken->getOpenId(), $newToken->getOpenId());
        $this->assertSame($originalToken->getUnionId(), $newToken->getUnionId());
        $this->assertSame($originalToken->getScopes(), $newToken->getScopes());
        $this->assertTrue($originalToken->isRevoked());
    }

    public function testRefreshAccessTokenWithInvalidRefreshToken(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_secret');

        $this->expectException(OAuth2AuthorizationException::class);
        $this->expectExceptionMessage('无效的刷新令牌');

        $this->service->refreshAccessToken(
            'invalid_refresh_token',
            $account
        );
    }

    public function testRevokeTokenWithAccessToken(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_secret');

        // Create token
        $authCode = $this->service->createAuthorizationCode(
            $account,
            'test_openid',
            'test_unionid',
            'https://example.com/callback',
            'snsapi_userinfo',
            'test_state'
        );

        $code = $authCode->getCode();
        $this->assertNotNull($code);
        $token = $this->service->exchangeCodeForToken(
            $code,
            'https://example.com/callback',
            $account
        );

        // Revoke by access token
        $accessToken = $token->getAccessToken();
        $this->assertNotNull($accessToken);
        $this->service->revokeToken(
            $accessToken,
            $account,
            'access_token'
        );

        // Verify token is revoked
        $this->assertTrue($token->isRevoked());
    }

    public function testRevokeTokenWithRefreshToken(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_secret');

        // Create token
        $authCode = $this->service->createAuthorizationCode(
            $account,
            'test_openid',
            'test_unionid',
            'https://example.com/callback',
            'snsapi_userinfo',
            'test_state'
        );

        $code = $authCode->getCode();
        $this->assertNotNull($code);
        $token = $this->service->exchangeCodeForToken(
            $code,
            'https://example.com/callback',
            $account
        );

        // Revoke by refresh token
        $refreshToken = $token->getRefreshToken();
        $this->assertNotNull($refreshToken);
        $this->service->revokeToken(
            $refreshToken,
            $account,
            'refresh_token'
        );

        // Verify token is revoked
        $this->assertTrue($token->isRevoked());
    }

    public function testRevokeTokenWithNonExistentToken(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_secret');

        // 撤销不存在的 token 应该正常执行而不抛异常
        $this->service->revokeToken(
            'non_existent_token',
            $account
        );

        // 验证服务对象仍然可用，说明方法正常执行完毕
        $this->assertInstanceOf(OAuth2AuthorizationService::class, $this->service);
    }
}
