<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\ActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Controller\Admin\WechatOAuth2UserCrudController;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2User;

/**
 * @internal
 */
#[CoversClass(WechatOAuth2UserCrudController::class)]
#[RunTestsInSeparateProcesses]
final class WechatOAuth2UserCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @phpstan-ignore-next-line
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(WechatOAuth2UserCrudController::class);
    }

    /** @return \Generator<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'openid' => ['OpenID'];
        yield 'nickname' => ['昵称'];
    }

    /**
     * WechatOAuth2User 控制器禁用了 NEW 动作
     * 我们提供一个特殊的标记，测试方法会检测并跳过
     * @return \Generator<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        // WechatOAuth2User 控制器禁用了 NEW 动作
        // 提供真实字段但期望异常
        yield 'openid_field' => ['openid'];
        yield 'nickname_field' => ['nickname'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        yield 'openid' => ['openid'];
        yield 'nickname' => ['nickname'];
        yield 'sex' => ['sex'];
    }

    public function testVerifyActionDisabled(): void
    {
        // 验证控制器确实禁用了 NEW 和 DELETE 动作，但保留了 DETAIL 动作
        $controller = $this->getControllerService();
        $actions = Actions::new();
        $controller->configureActions($actions);

        $indexPageActions = $actions->getAsDto(Crud::PAGE_INDEX)->getActions();
        $enabledActionNames = [];
        foreach ($indexPageActions as $action) {
            if ($action instanceof ActionDto) {
                $enabledActionNames[] = $action->getName();
            }
        }

        $this->assertNotContains(Action::NEW, $enabledActionNames, 'NEW action should be disabled for WechatOAuth2User controller');
        $this->assertNotContains(Action::DELETE, $enabledActionNames, 'DELETE action should be disabled for WechatOAuth2User controller');
        $this->assertContains(Action::DETAIL, $enabledActionNames, 'DETAIL action should be enabled for WechatOAuth2User controller');
    }

    /**
     * 自定义测试方法，验证NEW操作被禁用时返回403
     */
    public function testNewActionReturns403WhenDisabled(): void
    {
        $client = $this->createAuthenticatedClient();

        $this->expectException(ForbiddenActionException::class);
        $this->expectExceptionMessage('You don\'t have enough permissions to run the "new" action');

        $client->request('GET', $this->generateAdminUrl(Action::NEW));
    }

    public function testControllerConfiguration(): void
    {
        $controller = new WechatOAuth2UserCrudController();
        $this->assertEquals(WechatOAuth2User::class, $controller::getEntityFqcn());
    }

    protected function getEntityFqcn(): string
    {
        return WechatOAuth2User::class;
    }
}
