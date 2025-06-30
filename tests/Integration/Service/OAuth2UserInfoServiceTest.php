<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2User;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2UserRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\OAuth2UserInfoService;
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\WechatOAuth2Service;

class OAuth2UserInfoServiceTest extends TestCase
{
    private OAuth2UserInfoService $service;
    private WechatOAuth2Service $oauth2Service;
    private WechatOAuth2UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->oauth2Service = $this->createMock(WechatOAuth2Service::class);
        $this->userRepository = $this->createMock(WechatOAuth2UserRepository::class);
        
        $this->service = new OAuth2UserInfoService(
            $this->oauth2Service,
            $this->userRepository
        );
    }

    public function testGetUserInfo(): void
    {
        $user = $this->createMock(WechatOAuth2User::class);
        $user->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('test_token');
            
        $userInfo = $this->service->getUserInfo($user);
        
        $this->assertIsArray($userInfo);
    }

    public function testUpdateUserInfo(): void
    {
        $user = $this->createMock(WechatOAuth2User::class);
        $userData = [
            'openid' => 'test_openid',
            'nickname' => 'Test User'
        ];
        
        $this->service->updateUserInfo($user, $userData);
        
        $this->assertTrue(true);
    }
}