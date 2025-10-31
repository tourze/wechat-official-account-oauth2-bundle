<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\WechatOAuth2ApiException;

/**
 * @internal
 */
#[CoversClass(WechatOAuth2ApiException::class)]
final class WechatOAuth2ApiExceptionTest extends AbstractExceptionTestCase
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

    public function testExceptionWithApiUrl(): void
    {
        $apiUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token';
        $exception = new WechatOAuth2ApiException('API request failed', 40001, null, $apiUrl);

        $this->assertEquals($apiUrl, $exception->getApiUrl());
    }

    public function testExceptionWithApiResponse(): void
    {
        $apiResponse = [
            'errcode' => 40001,
            'errmsg' => 'invalid credential',
        ];
        $exception = new WechatOAuth2ApiException('API request failed', 40001, null, null, $apiResponse);

        $this->assertEquals($apiResponse, $exception->getApiResponse());
    }

    public function testExceptionWithFullParameters(): void
    {
        $apiUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token';
        $apiResponse = [
            'errcode' => 40001,
            'errmsg' => 'invalid credential',
        ];
        $previous = new \Exception('Previous exception');

        $exception = new WechatOAuth2ApiException(
            'API request failed',
            40001,
            $previous,
            $apiUrl,
            $apiResponse
        );

        $this->assertEquals('API request failed', $exception->getMessage());
        $this->assertEquals(40001, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertEquals($apiUrl, $exception->getApiUrl());
        $this->assertEquals($apiResponse, $exception->getApiResponse());
    }
}
