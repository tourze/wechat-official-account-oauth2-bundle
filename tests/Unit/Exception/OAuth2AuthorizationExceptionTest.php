<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\OAuth2AuthorizationException;

class OAuth2AuthorizationExceptionTest extends TestCase
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
}