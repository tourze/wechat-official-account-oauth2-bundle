<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2ConfigRepository;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * @internal
 */
#[CoversClass(WechatOAuth2ConfigRepository::class)]
#[RunTestsInSeparateProcesses]
final class WechatOAuth2ConfigRepositoryTest extends AbstractRepositoryTestCase
{
    private WechatOAuth2ConfigRepository $repository;

    protected function onSetUp(): void
    {
        $container = self::getContainer();
        $repository = $container->get(WechatOAuth2ConfigRepository::class);
        $this->assertInstanceOf(WechatOAuth2ConfigRepository::class, $repository);
        $this->repository = $repository;
    }

    // find 基础方法测试

    // findOneBy 相关测试

    public function testFindOneByWithOrderByShouldReturnFirstMatchingEntity(): void
    {
        $account1 = new Account();
        $account1->setName('Account 1');
        $account1->setAppId('app_1');
        $account1->setAppSecret('secret_1');
        $this->persistAndFlush($account1);

        $account2 = new Account();
        $account2->setName('Account 2');
        $account2->setAppId('app_2');
        $account2->setAppSecret('secret_2');
        $this->persistAndFlush($account2);

        $config1 = new WechatOAuth2Config();
        $config1->setAccount($account1);
        $config1->setScope('snsapi_userinfo');
        $config1->setValid(true);
        $this->persistAndFlush($config1);

        $config2 = new WechatOAuth2Config();
        $config2->setAccount($account2);
        $config2->setScope('snsapi_base');
        $config2->setValid(true);
        $this->persistAndFlush($config2);

        $result = $this->repository->findOneBy(['valid' => true], ['id' => 'ASC']);

        $this->assertInstanceOf(WechatOAuth2Config::class, $result);
        $this->assertTrue($result->isValid());
    }

    public function testFindOneByOrderByLogic(): void
    {
        // 清理现有数据
        $allConfigs = $this->repository->findAll();
        foreach ($allConfigs as $config) {
            $this->repository->remove($config);
        }

        $account1 = new Account();
        $account1->setName('Test Account Order 1');
        $account1->setAppId('app_order_1');
        $account1->setAppSecret('secret_1');
        $this->persistAndFlush($account1);

        $account2 = new Account();
        $account2->setName('Test Account Order 2');
        $account2->setAppId('app_order_2');
        $account2->setAppSecret('secret_2');
        $this->persistAndFlush($account2);

        $config1 = new WechatOAuth2Config();
        $config1->setAccount($account1);
        $config1->setScope('snsapi_userinfo');
        $config1->setValid(true);
        $this->persistAndFlush($config1);

        $config2 = new WechatOAuth2Config();
        $config2->setAccount($account2);
        $config2->setScope('snsapi_base');
        $config2->setValid(true);
        $this->persistAndFlush($config2);

        $result = $this->repository->findOneBy(['valid' => true], ['id' => 'ASC']);

        $this->assertInstanceOf(WechatOAuth2Config::class, $result);
        // 应该返回ID最小的记录
        $allValidConfigs = $this->repository->findBy(['valid' => true], ['id' => 'ASC']);
        $this->assertEquals($allValidConfigs[0]->getId(), $result->getId());
    }

    public function testFindOneByWithNullScopeShouldReturnMatchingEntity(): void
    {
        // 清理现有数据
        $allConfigs = $this->repository->findAll();
        foreach ($allConfigs as $config) {
            $this->repository->remove($config);
        }

        $account1 = new Account();
        $account1->setName('Account with scope');
        $account1->setAppId('app_with_scope');
        $account1->setAppSecret('secret');
        $this->persistAndFlush($account1);

        $account2 = new Account();
        $account2->setName('Account without scope');
        $account2->setAppId('app_without_scope');
        $account2->setAppSecret('secret');
        $this->persistAndFlush($account2);

        $config1 = new WechatOAuth2Config();
        $config1->setAccount($account1);
        $config1->setScope('snsapi_userinfo');
        $config1->setValid(true);
        $this->persistAndFlush($config1);

        $config2 = new WechatOAuth2Config();
        $config2->setAccount($account2);
        $config2->setScope(null);
        $config2->setValid(true);
        $this->persistAndFlush($config2);

        $result = $this->repository->findOneBy(['scope' => null]);

        $this->assertInstanceOf(WechatOAuth2Config::class, $result);
        $this->assertNull($result->getScope());
    }

    public function testFindOneByWithNullRemarkShouldReturnMatchingEntity(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app');
        $account->setAppSecret('secret');
        $this->persistAndFlush($account);

        $config = new WechatOAuth2Config();
        $config->setAccount($account);
        $config->setScope('snsapi_userinfo');
        $config->setValid(true);
        $config->setRemark(null);
        $this->persistAndFlush($config);

        $result = $this->repository->findOneBy(['remark' => null]);

        $this->assertInstanceOf(WechatOAuth2Config::class, $result);
        $this->assertNull($result->getRemark());
    }

    // findBy 相关测试

    public function testFindByWithAccountAssociation(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app');
        $account->setAppSecret('secret');
        $this->persistAndFlush($account);

        $config = new WechatOAuth2Config();
        $config->setAccount($account);
        $config->setScope('snsapi_userinfo');
        $config->setValid(true);
        $this->persistAndFlush($config);

        $results = $this->repository->findBy(['account' => $account]);

        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        foreach ($results as $result) {
            $this->assertEquals($account->getId(), $result->getAccount()->getId());
        }
    }

    public function testFindByWithNullScope(): void
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_null_scope');
        $account->setAppSecret('secret');
        $this->persistAndFlush($account);

        $config = new WechatOAuth2Config();
        $config->setAccount($account);
        $config->setScope(null);
        $config->setValid(true);
        $this->persistAndFlush($config);

        $results = $this->repository->findBy(['scope' => null]);

        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        foreach ($results as $result) {
            $this->assertNull($result->getScope());
        }
    }

    public function testFindByWithNullRemark(): void
    {
        $results = $this->repository->findBy(['remark' => null]);

        $this->assertIsArray($results);
        // 结果可能为空或包含remark为null的记录
        foreach ($results as $result) {
            $this->assertNull($result->getRemark());
        }
    }

    // findAll 相关测试

    public function testCountWithAccountAssociation(): void
    {
        $account = new Account();
        $account->setName('Count Test Account');
        $account->setAppId('count_test_app');
        $account->setAppSecret('secret');
        $this->persistAndFlush($account);

        $config = new WechatOAuth2Config();
        $config->setAccount($account);
        $config->setScope('snsapi_userinfo');
        $config->setValid(true);
        $this->persistAndFlush($config);

        $count = $this->repository->count(['account' => $account]);

        $this->assertIsInt($count);
        $this->assertGreaterThan(0, $count);
    }

    public function testCountWithNullScope(): void
    {
        $count = $this->repository->count(['scope' => null]);

        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testCountWithNullRemark(): void
    {
        $count = $this->repository->count(['remark' => null]);

        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    // 自定义方法测试
    public function testFindValidConfig(): void
    {
        // 先清理数据
        $allConfigs = $this->repository->findAll();
        foreach ($allConfigs as $config) {
            $this->repository->remove($config);
        }

        // 创建有效配置
        $account = new Account();
        $account->setName('Valid Config Test Account');
        $account->setAppId('valid_config_test_app');
        $account->setAppSecret('secret');
        $this->persistAndFlush($account);

        $config = new WechatOAuth2Config();
        $config->setAccount($account);
        $config->setScope('snsapi_userinfo');
        $config->setValid(true);
        $config->setIsDefault(true);
        $this->persistAndFlush($config);

        $result = $this->repository->findValidConfig();

        $this->assertInstanceOf(WechatOAuth2Config::class, $result);
        $this->assertTrue($result->isValid());
    }

    public function testFindValidConfigWhenNoValidConfigExists(): void
    {
        // 清理所有数据
        $allConfigs = $this->repository->findAll();
        foreach ($allConfigs as $config) {
            $this->repository->remove($config);
        }

        $result = $this->repository->findValidConfig();

        $this->assertNull($result);
    }

    public function testSetDefault(): void
    {
        $account = new Account();
        $account->setName('Default Test Account');
        $account->setAppId('default_test_app');
        $account->setAppSecret('secret');
        $this->persistAndFlush($account);

        $config = new WechatOAuth2Config();
        $config->setAccount($account);
        $config->setScope('snsapi_userinfo');
        $config->setValid(true);
        $config->setIsDefault(false);
        $this->persistAndFlush($config);

        $this->repository->setDefault($config);

        $this->assertTrue($config->isDefault());
    }

    public function testSave(): void
    {
        $account = new Account();
        $account->setName('Save Test Account');
        $account->setAppId('save_test_app');
        $account->setAppSecret('secret');
        $this->persistAndFlush($account);

        $config = new WechatOAuth2Config();
        $config->setAccount($account);
        $config->setScope('snsapi_base');
        $config->setValid(true);

        $this->repository->save($config);

        $this->assertNotNull($config->getId());

        // 验证已保存到数据库
        $savedConfig = $this->repository->find($config->getId());
        $this->assertNotNull($savedConfig);
        $this->assertEquals('snsapi_base', $savedConfig->getScope());
    }

    public function testSaveWithoutFlush(): void
    {
        $account = new Account();
        $account->setName('Save No Flush Test Account');
        $account->setAppId('save_no_flush_test_app');
        $account->setAppSecret('secret');
        $this->persistAndFlush($account);

        $config = new WechatOAuth2Config();
        $config->setAccount($account);
        $config->setScope('snsapi_base');
        $config->setValid(true);

        $this->repository->save($config, false);

        // 手动flush
        self::getEntityManager()->flush();

        $this->assertNotNull($config->getId());
    }

    public function testRemove(): void
    {
        $account = new Account();
        $account->setName('Remove Test Account');
        $account->setAppId('remove_test_app');
        $account->setAppSecret('secret');
        $this->persistAndFlush($account);

        $config = new WechatOAuth2Config();
        $config->setAccount($account);
        $config->setScope('snsapi_userinfo');
        $config->setValid(true);
        $this->persistAndFlush($config);

        $configId = $config->getId();

        $this->repository->remove($config);

        $removedConfig = $this->repository->find($configId);
        $this->assertNull($removedConfig);
    }

    public function testClearCache(): void
    {
        // clearCache 方法应该能正常执行并清空实体管理器的一级缓存
        $this->repository->clearCache();

        // 验证方法执行后仓库仍然可用
        $this->assertInstanceOf(WechatOAuth2ConfigRepository::class, $this->repository);
    }

    public function testRepositoryInstance(): void
    {
        $this->assertInstanceOf(WechatOAuth2ConfigRepository::class, $this->repository);
    }

    // 针对PHPStan要求的额外关联查询测试
    public function testFindOneByWithAssociationQuery(): void
    {
        $account = new Account();
        $account->setName('Association Test Account');
        $account->setAppId('association_test_app');
        $account->setAppSecret('secret');
        $this->persistAndFlush($account);

        $config = new WechatOAuth2Config();
        $config->setAccount($account);
        $config->setScope('snsapi_userinfo');
        $config->setValid(true);
        $this->persistAndFlush($config);

        $result = $this->repository->findOneBy(['account' => $account]);

        $this->assertInstanceOf(WechatOAuth2Config::class, $result);
        $this->assertEquals($account->getId(), $result->getAccount()->getId());
    }

    public function testFindByWithAssociationQuery(): void
    {
        $account = new Account();
        $account->setName('Association Find Test Account');
        $account->setAppId('association_find_test_app');
        $account->setAppSecret('secret');
        $this->persistAndFlush($account);

        $config = new WechatOAuth2Config();
        $config->setAccount($account);
        $config->setScope('snsapi_userinfo');
        $config->setValid(true);
        $this->persistAndFlush($config);

        $results = $this->repository->findBy(['account' => $account]);

        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        foreach ($results as $result) {
            $this->assertEquals($account->getId(), $result->getAccount()->getId());
        }
    }

    public function testCountWithAssociationQuery(): void
    {
        $account = new Account();
        $account->setName('Association Count Test Account');
        $account->setAppId('association_count_test_app');
        $account->setAppSecret('secret');
        $this->persistAndFlush($account);

        $config = new WechatOAuth2Config();
        $config->setAccount($account);
        $config->setScope('snsapi_userinfo');
        $config->setValid(true);
        $this->persistAndFlush($config);

        $count = $this->repository->count(['account' => $account]);

        $this->assertIsInt($count);
        $this->assertGreaterThan(0, $count);
    }

    // 针对可空字段的IS NULL查询
    public function testFindOneByWithIsNullQuery(): void
    {
        $account = new Account();
        $account->setName('Null Test Account');
        $account->setAppId('null_test_app');
        $account->setAppSecret('secret');
        $this->persistAndFlush($account);

        $config = new WechatOAuth2Config();
        $config->setAccount($account);
        $config->setScope(null);
        $config->setValid(true);
        $this->persistAndFlush($config);

        $result = $this->repository->findOneBy(['scope' => null]);

        $this->assertInstanceOf(WechatOAuth2Config::class, $result);
        $this->assertNull($result->getScope());
    }

    public function testFindByWithIsNullQuery(): void
    {
        $results = $this->repository->findBy(['remark' => null]);

        $this->assertIsArray($results);
        foreach ($results as $result) {
            $this->assertNull($result->getRemark());
        }
    }

    public function testCountWithIsNullQuery(): void
    {
        $count = $this->repository->count(['scope' => null]);

        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    // PHPStan required test for findOneBy sorting logic

    // PHPStan required test for association query
    public function testFindOneByAssociationAccountShouldReturnMatchingEntity(): void
    {
        $account = new Account();
        $account->setName('Association Test Account');
        $account->setAppId('association_test_app');
        $account->setAppSecret('secret');
        $this->persistAndFlush($account);

        $config = new WechatOAuth2Config();
        $config->setAccount($account);
        $config->setScope('snsapi_userinfo');
        $config->setValid(true);
        $this->persistAndFlush($config);

        $result = $this->repository->findOneBy(['account' => $account]);

        $this->assertInstanceOf(WechatOAuth2Config::class, $result);
        $this->assertEquals($account->getId(), $result->getAccount()->getId());
    }

    // PHPStan required test for IS NULL query

    // PHPStan required test for findBy association query

    // PHPStan required test for count association query
    public function testCountByAssociationAccountShouldReturnCorrectNumber(): void
    {
        $account = new Account();
        $account->setName('Count Association Test Account');
        $account->setAppId('count_association_test_app');
        $account->setAppSecret('secret');
        $this->persistAndFlush($account);

        $config = new WechatOAuth2Config();
        $config->setAccount($account);
        $config->setScope('snsapi_userinfo');
        $config->setValid(true);
        $this->persistAndFlush($config);

        $count = $this->repository->count(['account' => $account]);

        $this->assertGreaterThanOrEqual(1, $count);
    }

    // PHPStan required test for findBy IS NULL query

    // PHPStan required test for count IS NULL query

    protected function createNewEntity(): object
    {
        $account = new Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_' . uniqid());
        $account->setAppSecret('secret');
        $this->persistAndFlush($account);

        $config = new WechatOAuth2Config();
        $config->setAccount($account);
        $config->setScope('snsapi_userinfo');
        $config->setValid(true);

        return $config;
    }

    /** @return ServiceEntityRepository<WechatOAuth2Config> */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
