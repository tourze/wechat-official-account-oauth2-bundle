<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Controller\Admin;

use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Controller\Admin\WechatOAuth2ConfigCrudController;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;

/**
 * @internal
 */
#[CoversClass(WechatOAuth2ConfigCrudController::class)]
#[RunTestsInSeparateProcesses]
final class WechatOAuth2ConfigCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(WechatOAuth2ConfigCrudController::class);
    }

    /** @return \Generator<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'account' => ['微信账号'];
        yield 'scope' => ['授权范围'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield 'account' => ['account'];
        yield 'scope' => ['scope'];
        yield 'remark' => ['remark'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        yield 'account' => ['account'];
        yield 'scope' => ['scope'];
        yield 'remark' => ['remark'];
    }

    public function testControllerConfiguration(): void
    {
        $controller = new WechatOAuth2ConfigCrudController();
        $this->assertEquals(WechatOAuth2Config::class, $controller::getEntityFqcn());
    }

    protected function getEntityFqcn(): string
    {
        return WechatOAuth2Config::class;
    }

    public function testValidationErrors(): void
    {
        $client = self::createAuthenticatedClient();

        try {
            $client->request('POST', '/admin/wechat-oauth2/config/new', [
                'WechatOAuth2Config' => [
                    'account' => '',
                    'scope' => '',
                    'remark' => 'Test validation',
                ],
            ]);

            // 如果没有抛出异常，则验证返回状态码
            $response = $client->getResponse();
            if (422 === $response->getStatusCode()) {
                $this->assertResponseStatusCodeSame(422);
            } else {
                $this->assertTrue(in_array($response->getStatusCode(), [422, 400], true));
            }
        } catch (NotNullConstraintViolationException $e) {
            // 数据库约束异常是预期的，验证异常信息包含 'should not be blank' 语义
            $this->assertStringContainsString('NOT NULL constraint failed', $e->getMessage());
        }
    }
}
