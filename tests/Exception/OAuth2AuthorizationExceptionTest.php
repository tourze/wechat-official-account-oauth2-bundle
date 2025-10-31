<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\OAuth2AuthorizationException;

/**
 * @internal
 */
#[CoversClass(OAuth2AuthorizationException::class)]
final class OAuth2AuthorizationExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCreation(): void
    {
        $exception = new OAuth2AuthorizationException('Authorization failed');

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals('Authorization failed', $exception->getMessage());
    }

    public function testExceptionWithCode(): void
    {
        $exception = new OAuth2AuthorizationException('Authorization failed', 401);

        $this->assertEquals(401, $exception->getCode());
    }

    public function testExceptionConstants(): void
    {
        $this->assertEquals('invalid_authorization_code', OAuth2AuthorizationException::INVALID_AUTHORIZATION_CODE);
        $this->assertEquals('expired_authorization_code', OAuth2AuthorizationException::EXPIRED_AUTHORIZATION_CODE);
        $this->assertEquals('redirect_uri_mismatch', OAuth2AuthorizationException::REDIRECT_URI_MISMATCH);
        $this->assertEquals('invalid_refresh_token', OAuth2AuthorizationException::INVALID_REFRESH_TOKEN);
        $this->assertEquals('expired_refresh_token', OAuth2AuthorizationException::EXPIRED_REFRESH_TOKEN);
        $this->assertEquals('invalid_token', OAuth2AuthorizationException::INVALID_TOKEN);
        $this->assertEquals('invalid_code', OAuth2AuthorizationException::INVALID_CODE);
    }
}
