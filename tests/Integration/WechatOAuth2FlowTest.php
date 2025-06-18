<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Controller\WechatOAuth2AuthorizeController;

/**
 * 微信OAuth2流程测试
 */
class WechatOAuth2FlowTest extends TestCase
{
    /**
     * 测试控制器路由定义
     */
    public function testControllerRoutesAreDefined(): void
    {
        $authorizeReflection = new \ReflectionClass(WechatOAuth2AuthorizeController::class);
        
        // 检查 __invoke 方法的路由
        $invokeMethod = $authorizeReflection->getMethod('__invoke');
        $methodAttributes = $invokeMethod->getAttributes(\Symfony\Component\Routing\Attribute\Route::class);
        $this->assertCount(1, $methodAttributes, '__invoke method should have a Route attribute');
        
        // 验证这是一个可调用控制器
        $this->assertTrue($authorizeReflection->hasMethod('__invoke'), 'Controller should have __invoke method');
    }
}