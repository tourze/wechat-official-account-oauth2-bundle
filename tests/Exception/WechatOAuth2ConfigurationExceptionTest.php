<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\WechatOAuth2ConfigurationException;

/**
 * @internal
 */
#[CoversClass(WechatOAuth2ConfigurationException::class)]
final class WechatOAuth2ConfigurationExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCreation(): void
    {
        $exception = new WechatOAuth2ConfigurationException('Configuration error');

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals('Configuration error', $exception->getMessage());
    }

    public function testExceptionForMissingConfig(): void
    {
        $exception = new WechatOAuth2ConfigurationException('AppId is required');

        $this->assertEquals('AppId is required', $exception->getMessage());
    }
}
