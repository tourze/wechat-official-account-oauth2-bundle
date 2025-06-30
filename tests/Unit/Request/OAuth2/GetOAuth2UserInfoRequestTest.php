<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit\Request\OAuth2;

use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\GetOAuth2UserInfoRequest;

class GetOAuth2UserInfoRequestTest extends TestCase
{
    private GetOAuth2UserInfoRequest $request;

    protected function setUp(): void
    {
        $this->request = new GetOAuth2UserInfoRequest();
    }

    public function testRequestProperties(): void
    {
        $this->request->setAccessToken('access_token_123');
        $this->assertEquals('access_token_123', $this->request->getAccessToken());

        $this->request->setOpenid('openid_123');
        $this->assertEquals('openid_123', $this->request->getOpenid());

        $this->request->setLang('zh_CN');
        $this->assertEquals('zh_CN', $this->request->getLang());
    }

    public function testRequestValidation(): void
    {
        // Test request validation
        $this->request->setAccessToken('token');
        $this->request->setOpenid('openid');
        $this->assertTrue($this->request->isValid());
    }
}