<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2ConfigRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\OAuth2AuthorizationService;
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\WechatOAuth2Service;

class OAuth2AuthorizationServiceTest extends TestCase
{
    private OAuth2AuthorizationService $service;
    private WechatOAuth2Service $oauth2Service;
    private WechatOAuth2ConfigRepository $configRepository;

    protected function setUp(): void
    {
        $this->oauth2Service = $this->createMock(WechatOAuth2Service::class);
        $this->configRepository = $this->createMock(WechatOAuth2ConfigRepository::class);
        
        $this->service = new OAuth2AuthorizationService(
            $this->oauth2Service,
            $this->configRepository
        );
    }

    public function testGenerateAuthorizationUrl(): void
    {
        $config = $this->createMock(WechatOAuth2Config::class);
        $config->expects($this->once())
            ->method('getAppId')
            ->willReturn('test_app_id');
            
        $url = $this->service->generateAuthorizationUrl($config, 'http://callback.url', 'snsapi_userinfo');
        
        $this->assertStringContainsString('test_app_id', $url);
        $this->assertStringContainsString('snsapi_userinfo', $url);
    }
}