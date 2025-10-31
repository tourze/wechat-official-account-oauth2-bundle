<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\EventSubscriber;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\EventSubscriber\OAuth2SecurityEventSubscriber;

/**
 * @internal
 */
#[CoversClass(OAuth2SecurityEventSubscriber::class)]
#[RunTestsInSeparateProcesses]
final class OAuth2SecurityEventSubscriberTest extends AbstractEventSubscriberTestCase
{
    protected function onSetUp(): void        // 此测试不需要数据库操作或额外初始化
    {
    }

    public function testGetSubscribedEvents(): void
    {
        $events = OAuth2SecurityEventSubscriber::getSubscribedEvents();

        $this->assertNotEmpty($events);
    }

    public function testSubscriberInstantiation(): void
    {
        $subscriber = self::getService(OAuth2SecurityEventSubscriber::class);
        $this->assertInstanceOf(OAuth2SecurityEventSubscriber::class, $subscriber);
    }

    public function testOnKernelRequestWithProtectedPathAndMissingToken(): void
    {
        $subscriber = self::getService(OAuth2SecurityEventSubscriber::class);

        // Create mock request for protected path without token
        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')->willReturn('/oauth2/userinfo');

        // Mock headers without Authorization
        /* 使用 HeaderBag 具体类而非接口的原因：
         * 1. Request::$headers 属性明确要求 HeaderBag 类型，不能使用接口
         * 2. HeaderBag 是 Symfony 框架中专门处理 HTTP 头部的标准类
         * 3. 没有对应的接口，HeaderBag 本身就是最佳实现
         */
        $headers = $this->createMock(HeaderBag::class);
        $headers->method('get')->with('Authorization')->willReturn(null);
        $request->headers = $headers;

        // Mock query without access_token
        /* 使用真实的 InputBag 实例而非 mock 的原因：
         * 1. InputBag 类是 final 的，不能被 PHPUnit mock
         * 2. Request::$query 属性明确要求 InputBag 类型
         * 3. InputBag 是简单的数据容器类，使用真实实例是安全的
         */
        $query = new InputBag([]);
        $request->query = $query;

        // Create mock event
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->onKernelRequest($event);

        // Assert response is set with error
        $response = $event->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(401, $response->getStatusCode());

        $responseContent = $response->getContent();
        $content = json_decode(false !== $responseContent ? $responseContent : '{}', true);
        $this->assertIsArray($content);
        $this->assertSame('invalid_token', $content['error']);
        $this->assertSame('Access token is required', $content['error_description']);
    }

    public function testOnKernelRequestWithProtectedPathAndBearerToken(): void
    {
        $subscriber = self::getService(OAuth2SecurityEventSubscriber::class);

        // Create mock request for protected path with Bearer token
        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')->willReturn('/oauth2/userinfo');

        // Mock headers with Authorization Bearer token
        /* 使用 HeaderBag 具体类而非接口的原因：
         * 1. Request::$headers 属性明确要求 HeaderBag 类型，不能使用接口
         * 2. HeaderBag 是 Symfony 框架中专门处理 HTTP 头部的标准类
         * 3. 没有对应的接口，HeaderBag 本身就是最佳实现
         */
        $headers = $this->createMock(HeaderBag::class);
        $headers->method('get')->with('Authorization')->willReturn('Bearer test_access_token');
        $request->headers = $headers;

        // Mock query
        /* 使用真实的 InputBag 实例而非 mock 的原因：
         * 1. InputBag 类是 final 的，不能被 PHPUnit mock
         * 2. Request::$query 属性明确要求 InputBag 类型
         * 3. InputBag 是简单的数据容器类，使用真实实例是安全的
         */
        $query = new InputBag([]);
        $request->query = $query;

        // Create mock event
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->onKernelRequest($event);

        // Assert response is set with not implemented error (since validation is not implemented yet)
        $response = $event->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(501, $response->getStatusCode());

        $responseContent = $response->getContent();
        $content = json_decode(false !== $responseContent ? $responseContent : '{}', true);
        $this->assertIsArray($content);
        $this->assertSame('not_implemented', $content['error']);
        $this->assertSame('Token validation not implemented', $content['error_description']);
    }

    public function testOnKernelRequestWithProtectedPathAndQueryToken(): void
    {
        $subscriber = self::getService(OAuth2SecurityEventSubscriber::class);

        // Create mock request for protected path with query token
        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')->willReturn('/oauth2/introspect');

        // Mock headers without Authorization
        /* 使用 HeaderBag 具体类而非接口的原因：
         * 1. Request::$headers 属性明确要求 HeaderBag 类型，不能使用接口
         * 2. HeaderBag 是 Symfony 框架中专门处理 HTTP 头部的标准类
         * 3. 没有对应的接口，HeaderBag 本身就是最佳实现
         */
        $headers = $this->createMock(HeaderBag::class);
        $headers->method('get')->with('Authorization')->willReturn(null);
        $request->headers = $headers;

        // Mock query with access_token
        /* 使用真实的 InputBag 实例而非 mock 的原因：
         * 1. InputBag 类是 final 的，不能被 PHPUnit mock
         * 2. Request::$query 属性明确要求 InputBag 类型
         * 3. InputBag 是简单的数据容器类，使用真实实例是安全的
         */
        $query = new InputBag(['access_token' => 'test_query_token']);
        $request->query = $query;

        // Create mock event
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->onKernelRequest($event);

        // Assert response is set with not implemented error
        $response = $event->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(501, $response->getStatusCode());

        $responseContent = $response->getContent();
        $content = json_decode(false !== $responseContent ? $responseContent : '{}', true);
        $this->assertIsArray($content);
        $this->assertSame('not_implemented', $content['error']);
    }

    public function testOnKernelRequestWithNonProtectedPath(): void
    {
        $subscriber = self::getService(OAuth2SecurityEventSubscriber::class);

        // Create mock request for non-protected path
        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')->willReturn('/oauth2/authorize');

        // Create mock event
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->onKernelRequest($event);

        // Assert no response is set (request continues normally)
        $this->assertNull($event->getResponse());
    }

    public function testOnKernelRequestWithInvalidBearerFormat(): void
    {
        $subscriber = self::getService(OAuth2SecurityEventSubscriber::class);

        // Create mock request for protected path with invalid Bearer format
        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')->willReturn('/oauth2/userinfo');

        // Mock headers with invalid Authorization format
        /* 使用 HeaderBag 具体类而非接口的原因：
         * 1. Request::$headers 属性明确要求 HeaderBag 类型，不能使用接口
         * 2. HeaderBag 是 Symfony 框架中专门处理 HTTP 头部的标准类
         * 3. 没有对应的接口，HeaderBag 本身就是最佳实现
         */
        $headers = $this->createMock(HeaderBag::class);
        $headers->method('get')->with('Authorization')->willReturn('Basic dGVzdDp0ZXN0');
        $request->headers = $headers;

        // Mock query without access_token
        /* 使用真实的 InputBag 实例而非 mock 的原因：
         * 1. InputBag 类是 final 的，不能被 PHPUnit mock
         * 2. Request::$query 属性明确要求 InputBag 类型
         * 3. InputBag 是简单的数据容器类，使用真实实例是安全的
         */
        $query = new InputBag([]);
        $request->query = $query;

        // Create mock event
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->onKernelRequest($event);

        // Assert response is set with missing token error
        $response = $event->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(401, $response->getStatusCode());

        $responseContent = $response->getContent();
        $content = json_decode(false !== $responseContent ? $responseContent : '{}', true);
        $this->assertIsArray($content);
        $this->assertSame('invalid_token', $content['error']);
    }

    public function testGetSubscribedEventsReturnsCorrectEvents(): void
    {
        $events = OAuth2SecurityEventSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::REQUEST, $events);
        $this->assertEquals(['onKernelRequest', 10], $events[KernelEvents::REQUEST]);
    }
}
