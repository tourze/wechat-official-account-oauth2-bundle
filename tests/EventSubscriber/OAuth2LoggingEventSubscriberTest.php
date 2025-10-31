<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\EventSubscriber;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\EventSubscriber\OAuth2LoggingEventSubscriber;

/**
 * @internal
 */
#[CoversClass(OAuth2LoggingEventSubscriber::class)]
#[RunTestsInSeparateProcesses]
final class OAuth2LoggingEventSubscriberTest extends AbstractEventSubscriberTestCase
{
    protected function onSetUp(): void        // 此测试不需要数据库操作或额外初始化
    {
    }

    public function testGetSubscribedEvents(): void
    {
        $events = OAuth2LoggingEventSubscriber::getSubscribedEvents();

        $this->assertNotEmpty($events);
    }

    public function testSubscriberInstantiation(): void
    {
        $subscriber = self::getService(OAuth2LoggingEventSubscriber::class);
        $this->assertInstanceOf(OAuth2LoggingEventSubscriber::class, $subscriber);
    }

    public function testOnKernelRequestWithOAuth2Path(): void
    {
        $subscriber = self::getService(OAuth2LoggingEventSubscriber::class);

        // Create mock request
        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')->willReturn('/oauth2/authorize');
        $request->method('getMethod')->willReturn('GET');
        $request->method('getClientIp')->willReturn('127.0.0.1');

        // Mock query parameters
        /* 使用真实的 InputBag 实例而非 mock 的原因：
         * 1. InputBag 类是 final 的，不能被 PHPUnit mock
         * 2. Request::$query 属性明确要求 InputBag 类型
         * 3. InputBag 是简单的数据容器类，使用真实实例是安全的
         */
        $query = new InputBag([
            'client_id' => 'test_client_id',
            'response_type' => 'code',
        ]);
        $request->query = $query;

        // Mock request parameters
        $request->method('get')->willReturnMap([
            ['client_id', null, 'test_client_id'],
            ['grant_type', null, null],
        ]);

        // Mock headers
        /* 使用 HeaderBag 具体类而非接口的原因：
         * 1. Request::$headers 属性明确要求 HeaderBag 类型，不能使用接口
         * 2. HeaderBag 是 Symfony 框架中专门处理 HTTP 头部的标准类
         * 3. 没有对应的接口，HeaderBag 本身就是最佳实现
         */
        $headers = $this->createMock(HeaderBag::class);
        $headers->method('get')->with('User-Agent')->willReturn('Test User Agent');
        $request->headers = $headers;

        // Create mock event
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        // Assert logger is called
        // Note: We cannot easily test the actual logging call in integration tests
        // since the real logger service is used. This test verifies the method
        // completes without errors for OAuth2 paths.

        $subscriber->onKernelRequest($event);
    }

    public function testOnKernelRequestWithNonOAuth2Path(): void
    {
        $subscriber = self::getService(OAuth2LoggingEventSubscriber::class);

        // Create mock request with non-OAuth2 path
        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('getPathInfo')->willReturn('/api/users');

        // Create mock event
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        // The subscriber should call getPathInfo to check if it's an OAuth2 path
        // For non-OAuth2 paths, it should do nothing else

        $subscriber->onKernelRequest($event);
    }

    public function testOnKernelResponseWithSuccessfulOAuth2Response(): void
    {
        $subscriber = self::getService(OAuth2LoggingEventSubscriber::class);

        // Create mock request
        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')->willReturn('/oauth2/token');
        $request->method('getMethod')->willReturn('POST');
        $request->method('getClientIp')->willReturn('127.0.0.1');
        $request->method('get')->with('client_id')->willReturn('test_client');

        // Mock query parameters
        /* 使用真实的 InputBag 实例而非 mock 的原因：
         * 1. InputBag 类是 final 的，不能被 PHPUnit mock
         * 2. Request::$query 属性明确要求 InputBag 类型
         * 3. InputBag 是简单的数据容器类，使用真实实例是安全的
         */
        $query = new InputBag([]);
        $request->query = $query;

        // Create mock response
        $response = $this->createMock(Response::class);
        $response->method('getStatusCode')->willReturn(200);

        // Create mock event
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);

        // Assert logger is called with info level
        // Note: We cannot easily test the actual logging call in integration tests

        $subscriber->onKernelResponse($event);
    }

    public function testOnKernelResponseWithErrorOAuth2Response(): void
    {
        $subscriber = self::getService(OAuth2LoggingEventSubscriber::class);

        // Create mock request
        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')->willReturn('/oauth2/token');
        $request->method('getMethod')->willReturn('POST');
        $request->method('getClientIp')->willReturn('127.0.0.1');
        $request->method('get')->with('client_id')->willReturn('test_client');

        // Mock query parameters
        /* 使用真实的 InputBag 实例而非 mock 的原因：
         * 1. InputBag 类是 final 的，不能被 PHPUnit mock
         * 2. Request::$query 属性明确要求 InputBag 类型
         * 3. InputBag 是简单的数据容器类，使用真实实例是安全的
         */
        $query = new InputBag([]);
        $request->query = $query;

        // Create mock response with error
        $response = $this->createMock(Response::class);
        $response->method('getStatusCode')->willReturn(400);
        $response->method('getContent')->willReturn(json_encode([
            'error' => 'invalid_request',
            'error_description' => 'Missing required parameter',
        ]));

        // Create mock event
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);

        // Assert logger is called with warning level for response
        // Note: We cannot easily test the actual logging call in integration tests

        $subscriber->onKernelResponse($event);
    }

    public function testOnKernelResponseWithNonOAuth2Path(): void
    {
        $subscriber = self::getService(OAuth2LoggingEventSubscriber::class);

        // Create mock request with non-OAuth2 path
        $request = $this->createMock(Request::class);
        $request->expects($this->once())->method('getPathInfo')->willReturn('/api/users');

        $response = $this->createMock(Response::class);

        // Create mock event
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);

        // The subscriber should call getPathInfo to check if it's an OAuth2 path
        // For non-OAuth2 paths, it should do nothing else

        $subscriber->onKernelResponse($event);
    }

    public function testGetSubscribedEventsReturnsCorrectEvents(): void
    {
        $events = OAuth2LoggingEventSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::REQUEST, $events);
        $this->assertArrayHasKey(KernelEvents::RESPONSE, $events);

        $this->assertEquals(['onKernelRequest', 5], $events[KernelEvents::REQUEST]);
        $this->assertEquals(['onKernelResponse', 5], $events[KernelEvents::RESPONSE]);
    }
}
