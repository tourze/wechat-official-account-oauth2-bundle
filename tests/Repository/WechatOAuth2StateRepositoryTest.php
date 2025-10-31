<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2State;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2StateRepository;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * @internal
 */
#[CoversClass(WechatOAuth2StateRepository::class)]
#[RunTestsInSeparateProcesses]
final class WechatOAuth2StateRepositoryTest extends AbstractRepositoryTestCase
{
    private WechatOAuth2StateRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(WechatOAuth2StateRepository::class);
    }

    public function testRepositoryInstance(): void
    {
        $this->assertInstanceOf(WechatOAuth2StateRepository::class, $this->repository);
    }

    // find 方法测试

    // findAll 方法测试

    // findBy 方法测试

    // findOneBy 方法测试

    public function testFindOneByOrderByClause(): void
    {
        // 清理数据以确保测试的准确性
        self::getEntityManager()->createQuery('DELETE FROM Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2State')->execute();

        $uniqueState1 = 'findone_a_' . uniqid();
        $uniqueState2 = 'findone_z_' . uniqid();

        $state1 = $this->createTestEntity($uniqueState1);
        $state1->setValid(true);
        $state2 = $this->createTestEntity($uniqueState2);
        $state2->setValid(true);

        self::getEntityManager()->persist($state1);
        self::getEntityManager()->persist($state2);
        self::getEntityManager()->flush();

        $result = $this->repository->findOneBy(['valid' => true], ['state' => 'DESC']);

        $this->assertInstanceOf(WechatOAuth2State::class, $result);
        $this->assertEquals($uniqueState2, $result->getState());
    }

    // 可空字段 IS NULL 查询测试
    public function testFindBySessionIdNull(): void
    {
        $state = $this->createTestEntity();
        // sessionId 默认为 null

        self::getEntityManager()->persist($state);
        self::getEntityManager()->flush();

        $result = $this->repository->findBy(['sessionId' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    public function testFindByUsedTimeNull(): void
    {
        $state = $this->createTestEntity();
        // usedTime 默认为 null

        self::getEntityManager()->persist($state);
        self::getEntityManager()->flush();

        $result = $this->repository->findBy(['usedTime' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    public function testCountSessionIdNull(): void
    {
        $state = $this->createTestEntity();
        // sessionId 默认为 null

        self::getEntityManager()->persist($state);
        self::getEntityManager()->flush();

        $result = $this->repository->count(['sessionId' => null]);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    public function testCountUsedTimeNull(): void
    {
        $state = $this->createTestEntity();
        // usedTime 默认为 null

        self::getEntityManager()->persist($state);
        self::getEntityManager()->flush();

        $result = $this->repository->count(['usedTime' => null]);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    // 关联查询测试
    public function testFindByConfigAssociation(): void
    {
        $config = $this->createTestConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $state = new WechatOAuth2State();
        $state->setState('test-state-for-config');
        $state->setConfig($config);
        self::getEntityManager()->persist($state);
        self::getEntityManager()->flush();

        $result = $this->repository->findBy(['config' => $config]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    public function testFindOneByConfigAssociation(): void
    {
        $config = $this->createTestConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $state = new WechatOAuth2State();
        $state->setState('unique-config-state');
        $state->setConfig($config);
        self::getEntityManager()->persist($state);
        self::getEntityManager()->flush();

        $result = $this->repository->findOneBy(['config' => $config]);

        $this->assertInstanceOf(WechatOAuth2State::class, $result);
        $this->assertEquals($config->getId(), $result->getConfig()->getId());
    }

    public function testCountByConfigAssociation(): void
    {
        $config = $this->createTestConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $state = new WechatOAuth2State();
        $state->setState('count-config-state');
        $state->setConfig($config);
        self::getEntityManager()->persist($state);
        self::getEntityManager()->flush();

        $result = $this->repository->count(['config' => $config]);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    // 自定义方法测试
    public function testCleanupExpiredStates(): void
    {
        $result = $this->repository->cleanupExpiredStates();

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testFindValidState(): void
    {
        $result = $this->repository->findValidState('nonexistent_state');

        $this->assertNull($result);
    }

    public function testFindValidStateWithExistingValidState(): void
    {
        $state = $this->createTestEntity('valid-state');
        $state->setValid(true);
        // 设置未来的过期时间
        $state->setExpiresTime(new \DateTimeImmutable('+1 hour'));

        self::getEntityManager()->persist($state);
        self::getEntityManager()->flush();

        $result = $this->repository->findValidState('valid-state');

        $this->assertInstanceOf(WechatOAuth2State::class, $result);
        $this->assertEquals('valid-state', $result->getState());
    }

    public function testFindUnusedBySessionId(): void
    {
        $result = $this->repository->findUnusedBySessionId('nonexistent_session');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFindUnusedBySessionIdWithExistingSession(): void
    {
        $state = $this->createTestEntity();
        $state->setSessionId('test-session');
        $state->setValid(true);
        // 设置未来的过期时间
        $state->setExpiresTime(new \DateTimeImmutable('+1 hour'));

        self::getEntityManager()->persist($state);
        self::getEntityManager()->flush();

        $result = $this->repository->findUnusedBySessionId('test-session');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('test-session', $result[0]->getSessionId());
    }

    // save 和 remove 方法测试
    public function testSaveWithFlush(): void
    {
        $state = $this->createTestEntity();

        $this->repository->save($state, true);

        $this->assertNotNull($state->getId());

        // 验证实体已保存到数据库
        $found = $this->repository->find($state->getId());
        $this->assertInstanceOf(WechatOAuth2State::class, $found);
    }

    public function testSaveWithoutFlush(): void
    {
        $state = $this->createTestEntity();

        $this->repository->save($state, false);

        // 手动 flush
        self::getEntityManager()->flush();

        $this->assertNotNull($state->getId());
    }

    // PHPStan required test for findOneBy sorting logic

    // PHPStan required test for association query
    public function testFindOneByAssociationConfigShouldReturnMatchingEntity(): void
    {
        $config = $this->createTestConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $state = new WechatOAuth2State();
        $state->setState('association_test_state');
        $state->setConfig($config);
        self::getEntityManager()->persist($state);
        self::getEntityManager()->flush();

        $result = $this->repository->findOneBy(['config' => $config]);

        $this->assertInstanceOf(WechatOAuth2State::class, $result);
        $this->assertEquals($config->getId(), $result->getConfig()->getId());
    }

    // PHPStan required test for IS NULL query

    // PHPStan required test for findBy association query

    // PHPStan required test for count association query
    public function testCountByAssociationConfigShouldReturnCorrectNumber(): void
    {
        $config = $this->createTestConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $state1 = new WechatOAuth2State();
        $state1->setState('count_association_state_1');
        $state1->setConfig($config);
        $state2 = new WechatOAuth2State();
        $state2->setState('count_association_state_2');
        $state2->setConfig($config);
        self::getEntityManager()->persist($state1);
        self::getEntityManager()->persist($state2);
        self::getEntityManager()->flush();

        $count = $this->repository->count(['config' => $config]);

        $this->assertGreaterThanOrEqual(2, $count);
    }

    // PHPStan required test for findBy IS NULL query

    // PHPStan required test for count IS NULL query

    private function createTestEntity(?string $state = null): WechatOAuth2State
    {
        if (null === $state) {
            $state = 'test-state-' . uniqid();
        }

        $config = $this->createTestConfig();
        self::getEntityManager()->persist($config);

        $stateEntity = new WechatOAuth2State();
        $stateEntity->setState($state);
        $stateEntity->setConfig($config);

        return $stateEntity;
    }

    private function createTestConfig(): WechatOAuth2Config
    {
        $uniqueId = uniqid('test-app-', true);

        $account = new Account();
        $account->setAppId($uniqueId);
        $account->setAppSecret('test-app-secret-' . $uniqueId);
        $account->setName('Test Account ' . $uniqueId);
        self::getEntityManager()->persist($account);

        $config = new WechatOAuth2Config();
        $config->setAccount($account);

        return $config;
    }

    protected function createNewEntity(): object
    {
        return $this->createTestEntity();
    }

    /** @return ServiceEntityRepository<WechatOAuth2State> */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
