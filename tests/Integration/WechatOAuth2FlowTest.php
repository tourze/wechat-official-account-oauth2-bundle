<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Controller\WechatOAuth2Controller;

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
        $reflectionClass = new \ReflectionClass(WechatOAuth2Controller::class);
        
        // 检查类级别的路由
        $classAttributes = $reflectionClass->getAttributes(\Symfony\Component\Routing\Attribute\Route::class);
        $this->assertCount(1, $classAttributes, 'Controller should have a Route attribute');
        
        // 检查 authorize 方法
        $authorizeMethod = $reflectionClass->getMethod('authorize');
        $authorizeAttributes = $authorizeMethod->getAttributes(\Symfony\Component\Routing\Attribute\Route::class);
        $this->assertCount(1, $authorizeAttributes, 'authorize method should have a Route attribute');
        
        // 检查 callback 方法
        $callbackMethod = $reflectionClass->getMethod('callback');
        $callbackAttributes = $callbackMethod->getAttributes(\Symfony\Component\Routing\Attribute\Route::class);
        $this->assertCount(1, $callbackAttributes, 'callback method should have a Route attribute');
    }
}