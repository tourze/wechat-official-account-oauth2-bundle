<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AccessToken;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2User;

class OAuth2AccessTokenTest extends TestCase
{
    private OAuth2AccessToken $token;

    protected function setUp(): void
    {
        $this->token = new OAuth2AccessToken();
    }

    public function testBasicProperties(): void
    {
        $this->token->setAccessToken('test_token');
        $this->assertEquals('test_token', $this->token->getAccessToken());

        $this->token->setRefreshToken('refresh_token');
        $this->assertEquals('refresh_token', $this->token->getRefreshToken());

        $this->token->setExpiresIn(3600);
        $this->assertEquals(3600, $this->token->getExpiresIn());
    }

    public function testUserRelation(): void
    {
        $user = $this->createMock(WechatOAuth2User::class);
        $this->token->setUser($user);
        $this->assertSame($user, $this->token->getUser());
    }
}