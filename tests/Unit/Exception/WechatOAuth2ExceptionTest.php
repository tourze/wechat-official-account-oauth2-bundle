<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\WechatOAuth2Exception;

class WechatOAuth2ExceptionTest extends TestCase
{
    public function testExceptionCreation(): void
    {
        $exception = new WechatOAuth2Exception('General OAuth2 error');
        
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals('General OAuth2 error', $exception->getMessage());
    }

    public function testExceptionInheritance(): void
    {
        $exception = new WechatOAuth2Exception('Test error');
        
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }
}