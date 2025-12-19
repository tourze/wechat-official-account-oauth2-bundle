<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\EventSubscriber;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
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
        $this->assertInstanceOf(OAuth2LoggingEventSubscriber::class, $subscriber);

        // Use real Request object instead of mock
        $request = Request::create(
            '/oauth2/authorize',
            'GET',
            [
                'client_id' => 'test_client_id',
                'response_type' => 'code',
            ],
            [],
            [],
            [
                'REMOTE_ADDR' => '127.0.0.1',
                'HTTP_USER_AGENT' => 'Test User Agent',
            ]
        );

        // Use real Kernel from container
        $kernel = self::getContainer()->get('kernel');
        $this->assertInstanceOf(HttpKernelInterface::class, $kernel);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        // This test verifies the method completes without errors for OAuth2 paths.
        $subscriber->onKernelRequest($event);
    }

    public function testOnKernelRequestWithNonOAuth2Path(): void
    {
        $subscriber = self::getService(OAuth2LoggingEventSubscriber::class);
        $this->assertInstanceOf(OAuth2LoggingEventSubscriber::class, $subscriber);

        // Use real Request object with non-OAuth2 path
        $request = Request::create('/api/users', 'GET');

        // Use real Kernel from container
        $kernel = self::getContainer()->get('kernel');
        $this->assertInstanceOf(HttpKernelInterface::class, $kernel);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        // For non-OAuth2 paths, it should do nothing
        $subscriber->onKernelRequest($event);
    }

    public function testOnKernelResponseWithSuccessfulOAuth2Response(): void
    {
        $subscriber = self::getService(OAuth2LoggingEventSubscriber::class);
        $this->assertInstanceOf(OAuth2LoggingEventSubscriber::class, $subscriber);

        // Use real Request object
        $request = Request::create(
            '/oauth2/token',
            'POST',
            ['client_id' => 'test_client'],
            [],
            [],
            ['REMOTE_ADDR' => '127.0.0.1']
        );

        // Create real response
        $response = new Response('', 200);

        // Use real Kernel from container
        $kernel = self::getContainer()->get('kernel');
        $this->assertInstanceOf(HttpKernelInterface::class, $kernel);
        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);

        // This test verifies the method completes without errors
        $subscriber->onKernelResponse($event);
    }

    public function testOnKernelResponseWithErrorOAuth2Response(): void
    {
        $subscriber = self::getService(OAuth2LoggingEventSubscriber::class);
        $this->assertInstanceOf(OAuth2LoggingEventSubscriber::class, $subscriber);

        // Use real Request object
        $request = Request::create(
            '/oauth2/token',
            'POST',
            ['client_id' => 'test_client'],
            [],
            [],
            ['REMOTE_ADDR' => '127.0.0.1']
        );

        // Create real response with error
        $jsonContent = json_encode([
            'error' => 'invalid_request',
            'error_description' => 'Missing required parameter',
        ]);
        $response = new Response(
            $jsonContent !== false ? $jsonContent : '',
            400
        );

        // Use real Kernel from container
        $kernel = self::getContainer()->get('kernel');
        $this->assertInstanceOf(HttpKernelInterface::class, $kernel);
        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);

        // This test verifies the method completes without errors
        $subscriber->onKernelResponse($event);
    }

    public function testOnKernelResponseWithNonOAuth2Path(): void
    {
        $subscriber = self::getService(OAuth2LoggingEventSubscriber::class);
        $this->assertInstanceOf(OAuth2LoggingEventSubscriber::class, $subscriber);

        // Use real Request object with non-OAuth2 path
        $request = Request::create('/api/users', 'GET');
        $response = new Response('', 200);

        // Use real Kernel from container
        $kernel = self::getContainer()->get('kernel');
        $this->assertInstanceOf(HttpKernelInterface::class, $kernel);
        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);

        // For non-OAuth2 paths, it should do nothing
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
