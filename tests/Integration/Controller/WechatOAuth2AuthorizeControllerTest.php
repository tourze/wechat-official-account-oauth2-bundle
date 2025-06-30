<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Integration\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Tourze\WechatOfficialAccountOAuth2Bundle\Controller\WechatOAuth2AuthorizeController;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\WechatOAuth2Exception;
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\WechatOAuth2Service;

class WechatOAuth2AuthorizeControllerTest extends WebTestCase
{
    private WechatOAuth2AuthorizeController $controller;
    private WechatOAuth2Service|MockObject $oauth2Service;
    private LoggerInterface|MockObject $logger;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->oauth2Service = $this->createMock(WechatOAuth2Service::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        
        $this->controller = new WechatOAuth2AuthorizeController(
            $this->oauth2Service,
            $this->logger
        );
        
        // Set up container for redirect testing
        $this->controller->setContainer(self::getContainer());
    }

    public function testInvokeSuccessful(): void
    {
        $request = new Request();
        $session = $this->createMock(SessionInterface::class);
        $session->method('getId')->willReturn('test_session_id');
        $request->setSession($session);
        
        $this->oauth2Service->expects($this->once())
            ->method('generateAuthorizationUrl')
            ->with('test_session_id', null)
            ->willReturn('https://example.com/authorize');

        $response = $this->controller->__invoke($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('https://example.com/authorize', $response->getTargetUrl());
    }

    public function testInvokeWithScope(): void
    {
        $request = new Request(['scope' => 'snsapi_userinfo']);
        $session = $this->createMock(SessionInterface::class);
        $session->method('getId')->willReturn('test_session_id');
        $request->setSession($session);
        
        $this->oauth2Service->expects($this->once())
            ->method('generateAuthorizationUrl')
            ->with('test_session_id', 'snsapi_userinfo')
            ->willReturn('https://example.com/authorize');

        $response = $this->controller->__invoke($request);

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testInvokeWithException(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        
        $request = new Request();
        $session = $this->createMock(SessionInterface::class);
        $session->method('getId')->willReturn('test_session_id');
        $request->setSession($session);
        
        $exception = new WechatOAuth2Exception('Configuration not found');
        
        $this->oauth2Service->expects($this->once())
            ->method('generateAuthorizationUrl')
            ->willThrowException($exception);
            
        $this->logger->expects($this->once())
            ->method('error')
            ->with('Failed to generate authorization URL', [
                'error' => 'Configuration not found',
                'context' => null,
            ]);

        $this->controller->__invoke($request);
    }
}