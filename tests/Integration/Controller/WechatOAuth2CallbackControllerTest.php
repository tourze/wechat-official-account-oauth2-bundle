<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Integration\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tourze\WechatOfficialAccountOAuth2Bundle\Controller\WechatOAuth2CallbackController;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2User;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\WechatOAuth2Exception;
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\WechatOAuth2Service;

class WechatOAuth2CallbackControllerTest extends WebTestCase
{
    private WechatOAuth2CallbackController $controller;
    private WechatOAuth2Service|MockObject $oauth2Service;
    private LoggerInterface|MockObject $logger;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->oauth2Service = $this->createMock(WechatOAuth2Service::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        
        $this->controller = new WechatOAuth2CallbackController(
            $this->oauth2Service,
            $this->logger
        );
        
        // Set up container for redirect and template testing
        $this->controller->setContainer(self::getContainer());
    }

    public function testInvokeWithError(): void
    {
        $request = new Request([
            'error' => 'access_denied',
            'error_description' => 'User denied access'
        ]);
        
        $this->logger->expects($this->once())
            ->method('warning')
            ->with('OAuth2 authorization denied', [
                'error' => 'access_denied',
                'error_description' => 'User denied access',
            ]);

        $response = $this->controller->__invoke($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testInvokeWithInvalidParameters(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        
        $request = new Request(['code' => 'test_code']); // Missing state
        
        $this->controller->__invoke($request);
    }

    public function testInvokeSuccessful(): void
    {
        $request = new Request([
            'code' => 'test_code',
            'state' => 'test_state'
        ]);
        
        $user = $this->createMock(WechatOAuth2User::class);
        $user->method('getOpenid')->willReturn('test_openid');
        $user->method('getUnionid')->willReturn('test_unionid');
        
        $this->oauth2Service->expects($this->once())
            ->method('handleCallback')
            ->with('test_code', 'test_state')
            ->willReturn($user);
            
        $this->logger->expects($this->once())
            ->method('info')
            ->with('Wechat OAuth2 login successful', [
                'openid' => 'test_openid',
                'unionid' => 'test_unionid',
            ]);

        $response = $this->controller->__invoke($request);

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testInvokeWithOAuth2Exception(): void
    {
        $request = new Request([
            'code' => 'test_code',
            'state' => 'test_state'
        ]);
        
        $exception = new WechatOAuth2Exception('Invalid state');
        
        $this->oauth2Service->expects($this->once())
            ->method('handleCallback')
            ->willThrowException($exception);
            
        $this->logger->expects($this->once())
            ->method('error')
            ->with('OAuth2 callback failed', [
                'error' => 'Invalid state',
                'context' => null,
            ]);

        $response = $this->controller->__invoke($request);

        $this->assertEquals(200, $response->getStatusCode());
    }
}