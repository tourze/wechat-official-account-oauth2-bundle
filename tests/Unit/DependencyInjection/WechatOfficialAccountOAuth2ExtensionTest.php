<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\WechatOfficialAccountOAuth2Bundle\DependencyInjection\WechatOfficialAccountOAuth2Extension;

class WechatOfficialAccountOAuth2ExtensionTest extends TestCase
{
    private WechatOfficialAccountOAuth2Extension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new WechatOfficialAccountOAuth2Extension();
        $this->container = new ContainerBuilder();
    }

    public function testLoad(): void
    {
        $configs = [];
        $this->extension->load($configs, $this->container);
        
        // Verify that services are loaded
        $this->assertTrue($this->container->hasDefinition('tourze.wechat_official_account_oauth2.service'));
    }
}