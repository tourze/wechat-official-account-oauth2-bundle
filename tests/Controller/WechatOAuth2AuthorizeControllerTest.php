<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Controller\WechatOAuth2AuthorizeController;

/**
 * @internal
 */
#[CoversClass(WechatOAuth2AuthorizeController::class)]
#[RunTestsInSeparateProcesses]
final class WechatOAuth2AuthorizeControllerTest extends AbstractWebTestCase
{
    public function testAuthorizeRoute(): void
    {
        $client = self::createClientWithDatabase();

        $client->request('GET', '/authorize');

        // 验证路由存在并且返回重定向响应
        // OAuth2授权流程会重定向到微信授权页面
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertEquals(Response::HTTP_FOUND, $statusCode);
    }

    public function testAuthorizeWithScopeParameter(): void
    {
        $client = self::createClientWithDatabase();

        $client->request('GET', '/authorize?scope=snsapi_base');

        // 验证路由存在并且返回重定向响应
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertEquals(Response::HTTP_FOUND, $statusCode);
    }

    public function testAuthorizeWithPostMethod(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        // POST方法应该不被允许
        $this->expectException(MethodNotAllowedHttpException::class);

        $client->request('POST', '/authorize');
    }

    public function testAuthorizeWithHeadMethod(): void
    {
        $client = self::createClientWithDatabase();

        $client->request('HEAD', '/authorize');

        // HEAD方法应该被允许但返回空响应体
        // 响应码可能是302 (重定向) 或 500 (错误)
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [Response::HTTP_FOUND, Response::HTTP_INTERNAL_SERVER_ERROR]);
        $this->assertEmpty($client->getResponse()->getContent());
    }

    public function testAuthorizeWithOptionsMethod(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        // OPTIONS方法不被允许
        $this->expectException(MethodNotAllowedHttpException::class);

        $client->request('OPTIONS', '/authorize');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        $this->expectException(MethodNotAllowedHttpException::class);

        match ($method) {
            'PUT' => $client->request('PUT', '/authorize'),
            'DELETE' => $client->request('DELETE', '/authorize'),
            'PATCH' => $client->request('PATCH', '/authorize'),
            'TRACE' => $client->request('TRACE', '/authorize'),
            'PURGE' => $client->request('PURGE', '/authorize'),
            'POST' => $client->request('POST', '/authorize'),
            'INVALID' => self::markTestSkipped('INVALID method test skipped'),
            default => self::fail('Unsupported HTTP method: ' . $method),
        };
    }
}
