<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\OAuth2InvalidArgumentException;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\WechatOAuth2Exception;

/**
 * @internal
 */
#[CoversClass(OAuth2InvalidArgumentException::class)]
final class OAuth2InvalidArgumentExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCreation(): void
    {
        $exception = new OAuth2InvalidArgumentException('Test message');

        $this->assertInstanceOf(WechatOAuth2Exception::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testExceptionWithCode(): void
    {
        $exception = new OAuth2InvalidArgumentException('Test message', 400);

        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
    }
}
