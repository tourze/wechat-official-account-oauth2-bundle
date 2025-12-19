<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\ActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Controller\Admin\WechatOAuth2StateCrudController;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2State;

/**
 * @internal
 */
#[CoversClass(WechatOAuth2StateCrudController::class)]
#[RunTestsInSeparateProcesses]
final class WechatOAuth2StateCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(WechatOAuth2StateCrudController::class);
    }

    /** @return \Generator<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'state' => ['状态参数'];
        yield 'expiresTime' => ['过期时间'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        // WechatOAuth2State 控制器是只读的，NEW 动作被禁用
        // 提供真实字段但期望异常
        yield 'state_field' => ['state'];
        yield 'expiresTime_field' => ['expiresTime'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        // WechatOAuth2State 控制器是只读的，EDIT 动作被禁用
        // 提供真实字段但期望异常
        yield 'state_field' => ['state'];
        yield 'expiresTime_field' => ['expiresTime'];
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

        $this->assertNotContains(Action::NEW, $enabledActionNames, 'NEW action should be disabled for WechatOAuth2State controller');
        $this->assertNotContains(Action::EDIT, $enabledActionNames, 'EDIT action should be disabled for WechatOAuth2State controller');
        $this->assertNotContains(Action::DELETE, $enabledActionNames, 'DELETE action should be disabled for WechatOAuth2State controller');
        $this->assertContains(Action::DETAIL, $enabledActionNames, 'DETAIL action should be enabled for WechatOAuth2State controller');
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

        // 尝试从Fixtures中获取现有的实体ID
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $repository = $entityManager->getRepository(WechatOAuth2State::class);
        $entity = $repository->findOneBy([]);

        // 如果没有现有实体，跳过此测试（因为控制器配置为只读，不应该有实体）
        if (null === $entity) {
            self::markTestSkipped('No WechatOAuth2State entities available for testing edit action prohibition');
        }

        $this->expectException(ForbiddenActionException::class);
        $this->expectExceptionMessage('You don\'t have enough permissions to run the "edit" action');

        $client->request('GET', $this->generateAdminUrl(Action::EDIT, ['entityId' => $entity->getId()]));
    }

    public function testControllerConfiguration(): void
    {
        $controller = new WechatOAuth2StateCrudController();
        $this->assertEquals(WechatOAuth2State::class, $controller::getEntityFqcn());
    }

    protected function getEntityFqcn(): string
    {
        return WechatOAuth2State::class;
    }
}
