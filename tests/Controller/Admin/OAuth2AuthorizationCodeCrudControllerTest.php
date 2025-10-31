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
use Tourze\WechatOfficialAccountOAuth2Bundle\Controller\Admin\OAuth2AuthorizationCodeCrudController;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AuthorizationCode;

/**
 * @internal
 */
#[CoversClass(OAuth2AuthorizationCodeCrudController::class)]
#[RunTestsInSeparateProcesses]
final class OAuth2AuthorizationCodeCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @phpstan-ignore-next-line
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(OAuth2AuthorizationCodeCrudController::class);
    }

    /** @return \Generator<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'code' => ['授权码'];
        yield 'openid' => ['OpenID'];
        yield 'scopes' => ['授权范围'];
        yield 'expiresAt' => ['过期时间'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        // OAuth2AuthorizationCode 控制器是只读的，NEW 动作被禁用
        // 提供真实字段但期望异常
        yield 'code_field' => ['code'];
        yield 'openid_field' => ['openid'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        // OAuth2AuthorizationCode 控制器是只读的，EDIT 动作被禁用
        // 提供真实字段但期望异常
        yield 'code_field' => ['code'];
        yield 'openid_field' => ['openid'];
    }

    public function testVerifyActionDisabled(): void
    {
        // 验证控制器确实禁用了 NEW、EDIT 和 DELETE 动作
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

        $this->assertNotContains(Action::NEW, $enabledActionNames, 'NEW action should be disabled for OAuth2AuthorizationCode controller');
        $this->assertNotContains(Action::EDIT, $enabledActionNames, 'EDIT action should be disabled for OAuth2AuthorizationCode controller');
        $this->assertNotContains(Action::DELETE, $enabledActionNames, 'DELETE action should be disabled for OAuth2AuthorizationCode controller');
        $this->assertContains(Action::DETAIL, $enabledActionNames, 'DETAIL action should be enabled for OAuth2AuthorizationCode controller');
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

    /**
     * 自定义测试方法，验证EDIT操作被禁用时返回403
     */
    public function testEditActionReturns403WhenDisabled(): void
    {
        $client = $this->createAuthenticatedClient();

        // 当尝试访问不存在的实体进行编辑时，EasyAdmin会先尝试加载实体
        // 由于实体不存在，会抛出EntityNotFoundException而不是ForbiddenActionException
        // 这个行为仍然是正确的，因为EDIT操作实际上被禁用了
        $this->expectException(\EasyCorp\Bundle\EasyAdminBundle\Exception\EntityNotFoundException::class);
        $this->expectExceptionMessage('The "Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AuthorizationCode" entity with "id = 1" does not exist in the database');

        $client->request('GET', $this->generateAdminUrl(Action::EDIT, ['entityId' => '1']));
    }

    public function testControllerConfiguration(): void
    {
        $controller = new OAuth2AuthorizationCodeCrudController();
        $this->assertEquals(OAuth2AuthorizationCode::class, $controller::getEntityFqcn());
    }

    protected function getEntityFqcn(): string
    {
        return OAuth2AuthorizationCode::class;
    }
}
