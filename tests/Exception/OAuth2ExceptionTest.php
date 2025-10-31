<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\OAuth2Exception;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\WechatOAuth2Exception;

/**
 * @internal
 */
#[CoversClass(OAuth2Exception::class)]
class OAuth2ExceptionTest extends AbstractExceptionTestCase
{
    public function testCreation(): void
    {
        $exception = new OAuth2Exception('Test message', 123);

        self::assertSame('Test message', $exception->getMessage());
        self::assertSame(123, $exception->getCode());
    }

    public function testCreationWithContext(): void
    {
        $context = ['key' => 'value'];
        $exception = new OAuth2Exception('Test message', 0, null, $context);

        self::assertSame($context, $exception->getContext());
    }

    public function testCreationWithPrevious(): void
    {
        $previous = new \RuntimeException('Previous');
        $exception = new OAuth2Exception('Test message', 0, $previous);

        self::assertSame($previous, $exception->getPrevious());
    }
}
