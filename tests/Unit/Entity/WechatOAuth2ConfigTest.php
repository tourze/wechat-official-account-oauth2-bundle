<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;

class WechatOAuth2ConfigTest extends TestCase
{
    private WechatOAuth2Config $config;

    protected function setUp(): void
    {
        $this->config = new WechatOAuth2Config();
    }

    public function testBasicProperties(): void
    {
        $this->config->setAppId('test_app_id');
        $this->assertEquals('test_app_id', $this->config->getAppId());

        $this->config->setAppSecret('test_app_secret');
        $this->assertEquals('test_app_secret', $this->config->getAppSecret());

        $this->config->setName('Test Config');
        $this->assertEquals('Test Config', $this->config->getName());
    }

    public function testActiveStatus(): void
    {
        $this->config->setActive(true);
        $this->assertTrue($this->config->isActive());

        $this->config->setActive(false);
        $this->assertFalse($this->config->isActive());
    }
}