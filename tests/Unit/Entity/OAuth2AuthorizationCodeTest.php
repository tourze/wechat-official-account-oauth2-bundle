<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AuthorizationCode;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2User;

class OAuth2AuthorizationCodeTest extends TestCase
{
    private OAuth2AuthorizationCode $authCode;

    protected function setUp(): void
    {
        $this->authCode = new OAuth2AuthorizationCode();
    }

    public function testBasicProperties(): void
    {
        $this->authCode->setCode('auth_code_123');
        $this->assertEquals('auth_code_123', $this->authCode->getCode());

        $this->authCode->setState('state_123');
        $this->assertEquals('state_123', $this->authCode->getState());

        $this->authCode->setScope('snsapi_userinfo');
        $this->assertEquals('snsapi_userinfo', $this->authCode->getScope());
    }

    public function testExpirationTime(): void
    {
        $expireTime = new \DateTime('+10 minutes');
        $this->authCode->setExpiresAt($expireTime);
        $this->assertEquals($expireTime, $this->authCode->getExpiresAt());
    }
}