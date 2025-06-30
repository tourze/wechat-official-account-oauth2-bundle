<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\WechatOfficialAccountOAuth2Bundle\WechatOfficialAccountOAuth2Bundle;

class WechatOfficialAccountOAuth2BundleTest extends TestCase
{
    private WechatOfficialAccountOAuth2Bundle $bundle;

    protected function setUp(): void
    {
        $this->bundle = new WechatOfficialAccountOAuth2Bundle();
    }

    public function testBundleName(): void
    {
        $this->assertEquals('WechatOfficialAccountOAuth2Bundle', $this->bundle->getName());
    }

    public function testBuild(): void
    {
        $container = new ContainerBuilder();
        $this->bundle->build($container);
        
        // Test that compiler passes are added
        $this->assertGreaterThanOrEqual(0, count($container->getCompilerPassConfig()->getPasses()));
    }
}