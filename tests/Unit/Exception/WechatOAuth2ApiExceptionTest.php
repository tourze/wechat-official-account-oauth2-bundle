<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\WechatOAuth2ApiException;

class WechatOAuth2ApiExceptionTest extends TestCase
{
    public function testExceptionCreation(): void
    {
        $exception = new WechatOAuth2ApiException('API request failed');
        
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals('API request failed', $exception->getMessage());
    }

    public function testExceptionWithErrorCode(): void
    {
        $exception = new WechatOAuth2ApiException('Invalid access token', 40001);
        
        $this->assertEquals(40001, $exception->getCode());
        $this->assertEquals('Invalid access token', $exception->getMessage());
    }
}