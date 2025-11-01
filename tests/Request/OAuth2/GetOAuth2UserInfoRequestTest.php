<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Request\OAuth2;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\GetOAuth2UserInfoRequest;

/**
 * @internal
 */
#[CoversClass(GetOAuth2UserInfoRequest::class)]
final class GetOAuth2UserInfoRequestTest extends RequestTestCase
{
    private GetOAuth2UserInfoRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new GetOAuth2UserInfoRequest();
    }

    public function testRequestProperties(): void
    {
        $this->request->setOauthAccessToken('access_token_123');
        $this->assertEquals('access_token_123', $this->request->getOauthAccessToken());

        $this->request->setOpenId('openid_123');
        $this->assertEquals('openid_123', $this->request->getOpenId());

        $this->request->setLang('zh_CN');
        $this->assertEquals('zh_CN', $this->request->getLang());
    }

    public function testRequestPath(): void
    {
        $this->assertEquals('https://api.weixin.qq.com/sns/userinfo', $this->request->getRequestPath());
    }

    public function testRequestMethod(): void
    {
        $this->assertEquals('GET', $this->request->getRequestMethod());
    }
}
