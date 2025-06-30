<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit\Request\OAuth2;

use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\RefreshAccessTokenRequest;

class RefreshAccessTokenRequestTest extends TestCase
{
    private RefreshAccessTokenRequest $request;

    protected function setUp(): void
    {
        $this->request = new RefreshAccessTokenRequest();
    }

    public function testRequestProperties(): void
    {
        $this->request->setAppId('test_app_id');
        $this->assertEquals('test_app_id', $this->request->getAppId());

        $this->request->setRefreshToken('refresh_token_123');
        $this->assertEquals('refresh_token_123', $this->request->getRefreshToken());

        $this->request->setGrantType('refresh_token');
        $this->assertEquals('refresh_token', $this->request->getGrantType());
    }

}