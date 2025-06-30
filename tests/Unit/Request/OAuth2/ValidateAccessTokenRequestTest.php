<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit\Request\OAuth2;

use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\ValidateAccessTokenRequest;

class ValidateAccessTokenRequestTest extends TestCase
{
    private ValidateAccessTokenRequest $request;

    protected function setUp(): void
    {
        $this->request = new ValidateAccessTokenRequest();
    }

    public function testRequestProperties(): void
    {
        $this->request->setAccessToken('access_token_123');
        $this->assertEquals('access_token_123', $this->request->getAccessToken());

        $this->request->setOpenid('openid_123');
        $this->assertEquals('openid_123', $this->request->getOpenid());
    }

}