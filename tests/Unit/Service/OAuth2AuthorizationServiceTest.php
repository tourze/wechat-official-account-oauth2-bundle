<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AuthorizationCode;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\OAuth2AccessTokenRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\OAuth2AuthorizationCodeRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2ConfigRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\OAuth2AuthorizationService;
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\WechatOAuth2Service;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * OAuth2授权服务单元测试
 */
class OAuth2AuthorizationServiceTest extends TestCase
{
    private EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject $entityManager;
    private WechatOAuth2Service|\PHPUnit\Framework\MockObject\MockObject $wechatOAuth2Service;
    private OAuth2AccessTokenRepository|\PHPUnit\Framework\MockObject\MockObject $accessTokenRepository;
    private OAuth2AuthorizationCodeRepository|\PHPUnit\Framework\MockObject\MockObject $authorizationCodeRepository;
    private WechatOAuth2ConfigRepository|\PHPUnit\Framework\MockObject\MockObject $oauth2ConfigRepository;
    private OAuth2AuthorizationService $service;

    public function testBuildWechatAuthUrl(): void
    {
        $account = new Account();
        $account->setAppId('test_app_id');

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

    public function testCreateAuthorizationCode(): void
    {
        $account = new Account();
        $account->setAppId('test_app_id');

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(OAuth2AuthorizationCode::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $authCode = $this->service->createAuthorizationCode(
            $account,
            'test_openid',
            'test_unionid',
            'https://example.com/callback',
            'snsapi_base',
            'test_state'
        );

        $this->assertInstanceOf(OAuth2AuthorizationCode::class, $authCode);
        $this->assertEquals('test_openid', $authCode->getOpenid());
        $this->assertEquals('test_unionid', $authCode->getUnionid());
        $this->assertEquals('https://example.com/callback', $authCode->getRedirectUri());
        $this->assertEquals('snsapi_base', $authCode->getScopes());
        $this->assertEquals('test_state', $authCode->getState());
        $this->assertEquals($account, $authCode->getWechatAccount());
        $code = $authCode->getCode();
        $this->assertIsString($code);
        $this->assertStringStartsWith('AC_', $code);
        $this->assertFalse($authCode->isExpired());
    }

    public function testExchangeCodeForTokenWithValidCode(): void
    {
        $account = new Account();
        $account->setAppId('test_app_id');

        $authCode = new OAuth2AuthorizationCode();
        $authCode->setCode('test_code');
        $authCode->setOpenid('test_openid');
        $authCode->setRedirectUri('https://example.com/callback');
        $authCode->setScopes('snsapi_base');
        $authCode->setWechatAccount($account);
        $authCode->setExpiresAt(new \DateTime('+10 minutes'));
        $authCode->setUsed(false);

        $this->authorizationCodeRepository->expects($this->once())
            ->method('findOneBy')
            ->with([
                'code' => 'test_code',
                'wechatAccount' => $account,
                'used' => false
            ])
            ->willReturn($authCode);

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $accessToken = $this->service->exchangeCodeForToken(
            'test_code',
            'https://example.com/callback',
            $account
        );

        $this->assertEquals('test_openid', $accessToken->getOpenid());
        $this->assertEquals('snsapi_base', $accessToken->getScopes());
        $this->assertEquals($account, $accessToken->getWechatAccount());
        $accessTokenStr = $accessToken->getAccessToken();
        $refreshTokenStr = $accessToken->getRefreshToken();
        $this->assertIsString($accessTokenStr);
        $this->assertIsString($refreshTokenStr);
        $this->assertStringStartsWith('AT_', $accessTokenStr);
        $this->assertStringStartsWith('RT_', $refreshTokenStr);
        $this->assertTrue($authCode->isUsed());
    }

    public function testExchangeCodeForTokenWithInvalidCode(): void
    {
        $account = new Account();
        $account->setAppId('test_app_id');

        $this->authorizationCodeRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('无效的授权码');

        $this->service->exchangeCodeForToken(
            'invalid_code',
            'https://example.com/callback',
            $account
        );
    }

    public function testExchangeCodeForTokenWithExpiredCode(): void
    {
        $account = new Account();
        $account->setAppId('test_app_id');

        $authCode = new OAuth2AuthorizationCode();
        $authCode->setCode('test_code');
        $authCode->setExpiresAt(new \DateTime('-10 minutes')); // 过期的授权码
        $authCode->setUsed(false);

        $this->authorizationCodeRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn($authCode);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('授权码已过期');

        $this->service->exchangeCodeForToken(
            'test_code',
            'https://example.com/callback',
            $account
        );
    }

    public function testExchangeCodeForTokenWithMismatchedRedirectUri(): void
    {
        $account = new Account();
        $account->setAppId('test_app_id');

        $authCode = new OAuth2AuthorizationCode();
        $authCode->setCode('test_code');
        $authCode->setRedirectUri('https://example.com/callback');
        $authCode->setExpiresAt(new \DateTime('+10 minutes'));
        $authCode->setUsed(false);

        $this->authorizationCodeRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn($authCode);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('重定向URI不匹配');

        $this->service->exchangeCodeForToken(
            'test_code',
            'https://different.com/callback', // 不匹配的URI
            $account
        );
    }

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->wechatOAuth2Service = $this->createMock(WechatOAuth2Service::class);
        $this->accessTokenRepository = $this->createMock(OAuth2AccessTokenRepository::class);
        $this->authorizationCodeRepository = $this->createMock(OAuth2AuthorizationCodeRepository::class);
        $this->oauth2ConfigRepository = $this->createMock(WechatOAuth2ConfigRepository::class);

        $this->service = new OAuth2AuthorizationService(
            $this->entityManager,
            $this->wechatOAuth2Service,
            $this->accessTokenRepository,
            $this->authorizationCodeRepository,
            $this->oauth2ConfigRepository
        );
    }
}