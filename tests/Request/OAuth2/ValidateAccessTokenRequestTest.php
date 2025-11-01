<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Request\OAuth2;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\ValidateAccessTokenRequest;

/**
 * @internal
 */
#[CoversClass(ValidateAccessTokenRequest::class)]
final class ValidateAccessTokenRequestTest extends RequestTestCase
{
    private ValidateAccessTokenRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new ValidateAccessTokenRequest();
    }

    public function testRequestProperties(): void
    {
        $this->request->setAccessToken('access_token_123');
        $this->assertEquals('access_token_123', $this->request->getAccessToken());

        $this->request->setOpenId('openid_123');
        $this->assertEquals('openid_123', $this->request->getOpenId());
    }
}
