<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouteCollection;
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\AttributeControllerLoader;

class AttributeControllerLoaderTest extends TestCase
{
    private AttributeControllerLoader $loader;

    protected function setUp(): void
    {
        $this->loader = new AttributeControllerLoader();
    }

    public function testLoadRoutes(): void
    {
        $routes = $this->loader->load(null);
        
        $this->assertInstanceOf(RouteCollection::class, $routes);
        $this->assertGreaterThan(0, $routes->count());
    }

    public function testSupports(): void
    {
        $this->assertTrue($this->loader->supports(null, 'annotation'));
        $this->assertFalse($this->loader->supports(null, 'yaml'));
    }
}