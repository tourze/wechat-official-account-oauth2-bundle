<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Menu;

use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Menu\WechatOAuth2MenuProvider;

/**
 * @internal
 */
#[CoversClass(WechatOAuth2MenuProvider::class)]
#[RunTestsInSeparateProcesses]
final class WechatOAuth2MenuProviderTest extends AbstractEasyAdminMenuTestCase
{
    private WechatOAuth2MenuProvider $menuProvider;

    #[Test]
    public function testServiceIsAvailableInContainer(): void
    {
        $this->assertInstanceOf(WechatOAuth2MenuProvider::class, $this->menuProvider);
    }

    #[Test]
    public function testMenuProviderCreatesCorrectStructure(): void
    {
        $menuFactory = new MenuFactory();
        $rootItem = new MenuItem('root', $menuFactory);

        ($this->menuProvider)($rootItem);

        $this->assertTrue($rootItem->hasChildren());

        $oauth2Menu = $rootItem->getChild('微信OAuth2');
        $this->assertNotNull($oauth2Menu);
        $this->assertEquals('fab fa-weixin', $oauth2Menu->getAttribute('icon'));

        // 验证子菜单
        $this->assertTrue($oauth2Menu->hasChildren());
        $this->assertCount(5, $oauth2Menu->getChildren());

        // 验证具体的子菜单项
        $this->assertNotNull($oauth2Menu->getChild('OAuth2配置'));
        $this->assertNotNull($oauth2Menu->getChild('OAuth2用户'));
        $this->assertNotNull($oauth2Menu->getChild('访问令牌'));
        $this->assertNotNull($oauth2Menu->getChild('授权码'));
        $this->assertNotNull($oauth2Menu->getChild('状态参数'));
    }

    protected function onSetUp(): void
    {
        $this->menuProvider = self::getService(WechatOAuth2MenuProvider::class);
    }
}
