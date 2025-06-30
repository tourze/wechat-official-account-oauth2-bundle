<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\WechatOAuth2ConfigurationException;

class WechatOAuth2ConfigurationExceptionTest extends TestCase
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