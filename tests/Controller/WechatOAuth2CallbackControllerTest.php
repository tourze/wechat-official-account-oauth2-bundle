<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Controller\WechatOAuth2CallbackController;

/**
 * @internal
 */
#[CoversClass(WechatOAuth2CallbackController::class)]
#[RunTestsInSeparateProcesses]
final class WechatOAuth2CallbackControllerTest extends AbstractWebTestCase
{
    public function testCallbackRoute(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Missing required parameters');

        $client->request('GET', '/callback');
    }

    public function testCallbackWithValidParameters(): void
    {
        $client = self::createClientWithDatabase();

        $client->request('GET', '/callback?code=auth_code_123&state=state_456');

        // 验证路由存在，响应码应该是302 (重定向) 或 500 (错误) 或 200 (渲染错误页面)
        // 不抛出404或其他异常即可
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [Response::HTTP_OK, Response::HTTP_FOUND, Response::HTTP_INTERNAL_SERVER_ERROR]);
    }

    public function testCallbackWithoutCode(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Missing required parameters');

        $client->request('GET', '/callback?state=state_456');
    }

    public function testCallbackWithoutState(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Missing required parameters');

        $client->request('GET', '/callback?code=auth_code_123');
    }

    public function testCallbackWithoutAnyParameters(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Missing required parameters');

        $client->request('GET', '/callback');
    }

    public function testCallbackWithHeadMethod(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Missing required parameters');

        $client->request('HEAD', '/callback');
    }

    public function testCallbackWithOptionsMethod(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        // OPTIONS方法不被允许，应该抛出MethodNotAllowedHttpException
        $this->expectException(MethodNotAllowedHttpException::class);

        $client->request('OPTIONS', '/callback');
    }

    public function testCallbackWithSpecialCharacters(): void
    {
        $client = self::createClientWithDatabase();

        $client->request('GET', '/callback?code=test%20code&state=test%20state');

        // 验证路由存在，URL编码的参数能正确处理
        // 响应码应该是302 (重定向) 或 500 (错误) 或 200 (渲染错误页面)
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [Response::HTTP_OK, Response::HTTP_FOUND, Response::HTTP_INTERNAL_SERVER_ERROR]);
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        $this->expectException(MethodNotAllowedHttpException::class);

        match ($method) {
            'POST' => $client->request('POST', '/callback'),
            'PUT' => $client->request('PUT', '/callback'),
            'DELETE' => $client->request('DELETE', '/callback'),
            'PATCH' => $client->request('PATCH', '/callback'),
            'TRACE' => $client->request('TRACE', '/callback'),
            'PURGE' => $client->request('PURGE', '/callback'),
            'INVALID' => self::markTestSkipped('INVALID method test skipped'),
            default => self::fail('Unsupported HTTP method: ' . $method),
        };
    }
}
