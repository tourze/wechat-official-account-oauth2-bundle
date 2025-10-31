<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\AdminMenu;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private AdminMenu $adminMenu;

    protected function onSetUp(): void
    {
        $this->adminMenu = self::getService(AdminMenu::class);
    }

    public function testServiceIsConfigured(): void
    {
        $this->assertInstanceOf(AdminMenu::class, $this->adminMenu);
    }

    public function testImplementsMenuProviderInterface(): void
    {
        $this->assertInstanceOf(MenuProviderInterface::class, $this->adminMenu);
    }

    public function testIsReadonly(): void
    {
        $reflection = new \ReflectionClass(AdminMenu::class);
        $this->assertTrue($reflection->isReadOnly());
    }

    public function testMenuStructure(): void
    {
        $rootMenu = $this->createMock(ItemInterface::class);
        $oauth2Menu = $this->createMock(ItemInterface::class);
        $configMenu = $this->createMock(ItemInterface::class);
        $userMenu = $this->createMock(ItemInterface::class);
        $tokenMenu = $this->createMock(ItemInterface::class);
        $authCodeMenu = $this->createMock(ItemInterface::class);
        $stateMenu = $this->createMock(ItemInterface::class);

        // Configure setUri method for all menu items to support method chaining
        $configMenu->method('setUri')->willReturnSelf();
        $userMenu->method('setUri')->willReturnSelf();
        $tokenMenu->method('setUri')->willReturnSelf();
        $authCodeMenu->method('setUri')->willReturnSelf();
        $stateMenu->method('setUri')->willReturnSelf();

        $rootMenu->expects($this->exactly(2))
            ->method('getChild')
            ->with('微信OAuth2')
            ->willReturnOnConsecutiveCalls(null, $oauth2Menu)
        ;

        $rootMenu->expects($this->once())
            ->method('addChild')
            ->with('微信OAuth2')
            ->willReturn($oauth2Menu)
        ;

        $oauth2Menu->expects($this->exactly(5))
            ->method('addChild')
            ->willReturnMap([
                ['OAuth2配置', $configMenu],
                ['OAuth2用户', $userMenu],
                ['OAuth2令牌', $tokenMenu],
                ['OAuth2授权码', $authCodeMenu],
                ['OAuth2状态', $stateMenu],
            ])
        ;

        $configMenu->expects($this->once())->method('setAttribute')->with('icon', 'fas fa-cog');

        $userMenu->expects($this->once())->method('setAttribute')->with('icon', 'fas fa-users');

        $tokenMenu->expects($this->once())->method('setAttribute')->with('icon', 'fas fa-key');

        $authCodeMenu->expects($this->once())->method('setAttribute')->with('icon', 'fas fa-qrcode');

        $stateMenu->expects($this->once())->method('setAttribute')->with('icon', 'fas fa-chart-line');

        // Execute the service
        ($this->adminMenu)($rootMenu);
    }

    public function testMenuWithExistingOAuth2Menu(): void
    {
        $rootMenu = $this->createMock(ItemInterface::class);
        $existingOAuth2Menu = $this->createMock(ItemInterface::class);
        $configMenu = $this->createMock(ItemInterface::class);
        $userMenu = $this->createMock(ItemInterface::class);
        $tokenMenu = $this->createMock(ItemInterface::class);
        $authCodeMenu = $this->createMock(ItemInterface::class);
        $stateMenu = $this->createMock(ItemInterface::class);

        // Configure setUri method for all menu items to support method chaining
        $configMenu->method('setUri')->willReturnSelf();
        $userMenu->method('setUri')->willReturnSelf();
        $tokenMenu->method('setUri')->willReturnSelf();
        $authCodeMenu->method('setUri')->willReturnSelf();
        $stateMenu->method('setUri')->willReturnSelf();

        $rootMenu->expects($this->exactly(2))
            ->method('getChild')
            ->with('微信OAuth2')
            ->willReturn($existingOAuth2Menu)
        ;

        $rootMenu->expects($this->never())
            ->method('addChild')
        ;

        $existingOAuth2Menu->expects($this->exactly(5))
            ->method('addChild')
            ->willReturnMap([
                ['OAuth2配置', $configMenu],
                ['OAuth2用户', $userMenu],
                ['OAuth2令牌', $tokenMenu],
                ['OAuth2授权码', $authCodeMenu],
                ['OAuth2状态', $stateMenu],
            ])
        ;

        $configMenu->expects($this->once())->method('setAttribute')->with('icon', 'fas fa-cog');

        $userMenu->expects($this->once())->method('setAttribute')->with('icon', 'fas fa-users');

        $tokenMenu->expects($this->once())->method('setAttribute')->with('icon', 'fas fa-key');

        $authCodeMenu->expects($this->once())->method('setAttribute')->with('icon', 'fas fa-qrcode');

        $stateMenu->expects($this->once())->method('setAttribute')->with('icon', 'fas fa-chart-line');

        // Execute the service
        ($this->adminMenu)($rootMenu);
    }

    public function testMenuHandlesNullOAuth2Menu(): void
    {
        // Mock a scenario where getChild returns null after adding
        $menuMock = $this->createMock(ItemInterface::class);
        $menuMock->expects($this->exactly(2))
            ->method('getChild')
            ->with('微信OAuth2')
            ->willReturnOnConsecutiveCalls(null, null)
        ;
        $menuMock->expects($this->once())
            ->method('addChild')
            ->with('微信OAuth2')
        ;

        // Execute the service - should handle null gracefully
        ($this->adminMenu)($menuMock);

        // Verify that the service executed successfully by checking the service state
        $this->assertInstanceOf(AdminMenu::class, $this->adminMenu);
    }
}
