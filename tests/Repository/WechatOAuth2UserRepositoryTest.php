<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Persisters\Exception\UnrecognizedField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2User;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2UserRepository;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * @internal
 */
#[CoversClass(WechatOAuth2UserRepository::class)]
#[RunTestsInSeparateProcesses]
final class WechatOAuth2UserRepositoryTest extends AbstractRepositoryTestCase
{
    private WechatOAuth2UserRepository $repository;

    private WechatOAuth2Config $config;

    private Account $account;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(WechatOAuth2UserRepository::class);

        // 创建测试用的Account和Config
        $this->account = new Account();
        $this->account->setAppId('test_app_id_' . uniqid());
        $this->account->setAppSecret('test_app_secret_' . uniqid());
        $this->account->setName('Test Account');

        $this->config = new WechatOAuth2Config();
        $this->config->setAccount($this->account);
        $this->config->setScope('snsapi_userinfo');
        $this->config->setValid(true);
        $this->config->setIsDefault(false);

        $this->persistAndFlush($this->account);
        $this->persistAndFlush($this->config);
    }

    public function testRepositoryInstance(): void
    {
        $this->assertInstanceOf(WechatOAuth2UserRepository::class, $this->repository);
    }

    // ==================== find 方法测试 ====================

    // ==================== findAll 方法测试 ====================

    // ==================== findBy 方法测试 ====================

    // ==================== findOneBy 方法测试 ====================

    public function testFindOneByWithOrderByShouldReturnCorrectEntity(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'nickname' => 'B User']);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'nickname' => 'A User']);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->findOneBy([], ['nickname' => 'ASC']);

        $this->assertInstanceOf(WechatOAuth2User::class, $result);
        $this->assertEquals('A User', $result->getNickname());
    }

    public function testFindOneByWithInvalidFieldShouldHandleGracefully(): void
    {
        // 测试使用无效的字段名，应该抛出异常（这是更实用的测试）
        $this->expectException(UnrecognizedField::class);
        $this->repository->findOneBy(['nonexistent_field' => 'test']);
    }

    // ==================== 关联查询测试 ====================

    public function testFindByWithConfigAssociationShouldWork(): void
    {
        $user = $this->createWechatUser(['openid' => 'test_openid']);
        $this->persistAndFlush($user);

        $result = $this->repository->findBy(['config' => $this->config]);

        $this->assertGreaterThanOrEqual(1, count($result));
        $this->assertEquals($this->config->getId(), $result[0]->getConfig()->getId());
    }

    public function testCountWithConfigAssociationShouldWork(): void
    {
        $user = $this->createWechatUser(['openid' => 'test_openid']);
        $this->persistAndFlush($user);

        $result = $this->repository->count(['config' => $this->config]);

        $this->assertGreaterThanOrEqual(1, $result);
    }

    public function testFindOneByWithConfigAssociationShouldWork(): void
    {
        $user = $this->createWechatUser(['openid' => 'test_openid']);
        $this->persistAndFlush($user);

        $result = $this->repository->findOneBy(['config' => $this->config]);

        $this->assertInstanceOf(WechatOAuth2User::class, $result);
        $this->assertEquals($this->config->getId(), $result->getConfig()->getId());
    }

    // ==================== IS NULL 查询测试 ====================

    public function testFindByWithNullUnionidShouldWork(): void
    {
        $this->cleanupAllData();

        $user1 = $this->createWechatUser(['openid' => 'openid1', 'unionid' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'unionid' => 'test_unionid']);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->findBy(['unionid' => null]);

        $this->assertGreaterThanOrEqual(1, count($result));
        $this->assertNull($result[0]->getUnionId());
    }

    public function testCountWithNullUnionidShouldWork(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'unionid' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'unionid' => 'test_unionid']);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->count(['unionid' => null]);

        $this->assertGreaterThanOrEqual(1, $result);
    }

    public function testFindByWithNullNicknameShouldWork(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'nickname' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'nickname' => 'Test User']);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->findBy(['nickname' => null]);

        $this->assertGreaterThanOrEqual(1, count($result));
        $this->assertNull($result[0]->getNickname());
    }

    public function testFindOneByWithNullUnionidShouldWork(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'unionid' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'unionid' => 'test_unionid']);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->findOneBy(['unionid' => null]);

        $this->assertInstanceOf(WechatOAuth2User::class, $result);
        $this->assertNull($result->getUnionId());
    }

    public function testFindOneByWithNullNicknameShouldWork(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'nickname' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'nickname' => 'Test User']);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->findOneBy(['nickname' => null]);

        $this->assertInstanceOf(WechatOAuth2User::class, $result);
        $this->assertNull($result->getNickname());
    }

    public function testCountWithNullNicknameShouldWork(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'nickname' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'nickname' => 'Test User']);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->count(['nickname' => null]);

        $this->assertGreaterThanOrEqual(1, $result);
    }

    public function testFindByWithNullSexShouldWork(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'sex' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'sex' => 1]);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->findBy(['sex' => null]);

        $this->assertGreaterThanOrEqual(1, count($result));
        $this->assertNull($result[0]->getSex());
    }

    public function testCountWithNullSexShouldWork(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'sex' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'sex' => 1]);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->count(['sex' => null]);

        $this->assertGreaterThanOrEqual(1, $result);
    }

    public function testFindByWithNullProvinceShouldWork(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'province' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'province' => 'Beijing']);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->findBy(['province' => null]);

        $this->assertGreaterThanOrEqual(1, count($result));
        $this->assertNull($result[0]->getProvince());
    }

    public function testCountWithNullProvinceShouldWork(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'province' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'province' => 'Beijing']);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->count(['province' => null]);

        $this->assertGreaterThanOrEqual(1, $result);
    }

    public function testFindByWithNullCityShouldWork(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'city' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'city' => 'Beijing']);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->findBy(['city' => null]);

        $this->assertGreaterThanOrEqual(1, count($result));
        $this->assertNull($result[0]->getCity());
    }

    public function testCountWithNullCityShouldWork(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'city' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'city' => 'Beijing']);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->count(['city' => null]);

        $this->assertGreaterThanOrEqual(1, $result);
    }

    public function testFindByWithNullCountryShouldWork(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'country' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'country' => 'China']);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->findBy(['country' => null]);

        $this->assertGreaterThanOrEqual(1, count($result));
        $this->assertNull($result[0]->getCountry());
    }

    public function testCountWithNullCountryShouldWork(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'country' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'country' => 'China']);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->count(['country' => null]);

        $this->assertGreaterThanOrEqual(1, $result);
    }

    public function testFindByWithNullHeadimurlShouldWork(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'headimgurl' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'headimgurl' => 'https://example.com/avatar.jpg']);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->findBy(['headimgurl' => null]);

        $this->assertGreaterThanOrEqual(1, count($result));
        $this->assertNull($result[0]->getHeadimgurl());
    }

    public function testCountWithNullHeadimurlShouldWork(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'headimgurl' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'headimgurl' => 'https://example.com/avatar.jpg']);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->count(['headimgurl' => null]);

        $this->assertGreaterThanOrEqual(1, $result);
    }

    public function testFindByWithNullPrivilegeShouldWork(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'privilege' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'privilege' => ['privilege1']]);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->findBy(['privilege' => null]);

        $this->assertGreaterThanOrEqual(1, count($result));
        $this->assertNull($result[0]->getPrivilege());
    }

    public function testCountWithNullPrivilegeShouldWork(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'privilege' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'privilege' => ['privilege1']]);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->count(['privilege' => null]);

        $this->assertGreaterThanOrEqual(1, $result);
    }

    public function testFindByWithNullScopeShouldWork(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'scope' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'scope' => 'snsapi_userinfo']);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->findBy(['scope' => null]);

        $this->assertGreaterThanOrEqual(1, count($result));
        $this->assertNull($result[0]->getScope());
    }

    public function testCountWithNullScopeShouldWork(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'scope' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'scope' => 'snsapi_userinfo']);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->count(['scope' => null]);

        $this->assertGreaterThanOrEqual(1, $result);
    }

    public function testFindByWithNullRawDataShouldWork(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'rawData' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'rawData' => ['key' => 'value']]);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->findBy(['rawData' => null]);

        $this->assertGreaterThanOrEqual(1, count($result));
        $this->assertNull($result[0]->getRawData());
    }

    public function testCountWithNullRawDataShouldWork(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'rawData' => null]);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'rawData' => ['key' => 'value']]);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->count(['rawData' => null]);

        $this->assertGreaterThanOrEqual(1, $result);
    }

    // ==================== 自定义方法测试 ====================

    public function testFindByOpenid(): void
    {
        $user = $this->createWechatUser(['openid' => 'test_openid']);
        $this->persistAndFlush($user);

        $result = $this->repository->findByOpenid('test_openid');

        $this->assertInstanceOf(WechatOAuth2User::class, $result);
        $this->assertEquals('test_openid', $result->getOpenId());

        $nullResult = $this->repository->findByOpenid('nonexistent_openid');
        $this->assertNull($nullResult);
    }

    public function testFindByUnionid(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'openid1', 'unionid' => 'test_unionid']);
        $user2 = $this->createWechatUser(['openid' => 'openid2', 'unionid' => 'test_unionid']);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $result = $this->repository->findByUnionid('test_unionid');

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(WechatOAuth2User::class, $result);

        $emptyResult = $this->repository->findByUnionid('nonexistent_unionid');
        $this->assertIsArray($emptyResult);
        $this->assertEmpty($emptyResult);
    }

    public function testFindExpiredTokenUsers(): void
    {
        // 创建当前时间的用户
        $currentUser = $this->createWechatUser([
            'openid' => 'current_openid',
            'accessToken' => 'current_token',
            'refreshToken' => 'current_refresh_token',
            'expiresIn' => 7200, // 2小时未过期
        ]);
        $this->persistAndFlush($currentUser);

        // 由于无法在测试中直接操作过期时间，我们测试方法存在和返回类型
        $result = $this->repository->findExpiredTokenUsers();

        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(WechatOAuth2User::class, $result);
    }

    public function testCleanupExpiredData(): void
    {
        // 创建当前用户数据
        $currentUser = $this->createWechatUser([
            'openid' => 'current_openid',
            'accessToken' => 'current_token',
            'refreshToken' => 'current_refresh_token',
            'expiresIn' => 7200,
        ]);
        $this->persistAndFlush($currentUser);

        // 测试清理方法的返回值类型和范围
        $result = $this->repository->cleanupExpiredData(30);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testUpdateOrCreate(): void
    {
        $userData = [
            'openid' => 'test_openid',
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'expires_in' => 7200,
            'scope' => 'snsapi_userinfo',
            'unionid' => 'test_unionid',
            'nickname' => 'Test User',
            'sex' => 1,
            'province' => 'Beijing',
            'city' => 'Beijing',
            'country' => 'China',
            'headimgurl' => 'https://example.com/avatar.jpg',
            'privilege' => ['privilege1', 'privilege2'],
        ];

        // 测试创建新用户
        $user = $this->repository->updateOrCreate($userData, $this->config);
        $this->persistAndFlush($user);

        $this->assertInstanceOf(WechatOAuth2User::class, $user);
        $this->assertEquals('test_openid', $user->getOpenId());
        $this->assertEquals('Test User', $user->getNickname());
        $this->assertEquals($this->config->getId(), $user->getConfig()->getId());

        // 测试更新现有用户
        $userData['nickname'] = 'Updated User';
        $updatedUser = $this->repository->updateOrCreate($userData, $this->config);
        $this->persistAndFlush($updatedUser);

        $this->assertEquals($user->getId(), $updatedUser->getId());
        $this->assertEquals('Updated User', $updatedUser->getNickname());
    }

    // ==================== save 和 remove 方法测试 ====================

    public function testSave(): void
    {
        $user = $this->createWechatUser(['openid' => 'test_save_openid']);

        $this->repository->save($user);

        $found = $this->repository->findByOpenid('test_save_openid');
        $this->assertInstanceOf(WechatOAuth2User::class, $found);
        $this->assertEquals('test_save_openid', $found->getOpenId());
    }

    public function testSaveWithoutFlush(): void
    {
        $user = $this->createWechatUser(['openid' => 'test_save_no_flush']);

        $this->repository->save($user, false);
        self::getEntityManager()->flush();

        $found = $this->repository->findByOpenid('test_save_no_flush');
        $this->assertInstanceOf(WechatOAuth2User::class, $found);
    }

    public function testRemove(): void
    {
        $user = $this->createWechatUser(['openid' => 'test_remove_openid']);
        $this->persistAndFlush($user);

        $this->repository->remove($user);

        $found = $this->repository->findByOpenid('test_remove_openid');
        $this->assertNull($found);
    }

    // PHPStan required test for findOneBy sorting logic

    // PHPStan required test for association query
    public function testFindOneByAssociationConfigShouldReturnMatchingEntity(): void
    {
        $user = $this->createWechatUser(['openid' => 'association_test_openid']);
        $this->persistAndFlush($user);

        $result = $this->repository->findOneBy(['config' => $this->config]);

        $this->assertInstanceOf(WechatOAuth2User::class, $result);
        $this->assertEquals($this->config->getId(), $result->getConfig()->getId());
    }

    // PHPStan required test for IS NULL query

    // PHPStan required test for findBy association query

    // PHPStan required test for count association query
    public function testCountByAssociationConfigShouldReturnCorrectNumber(): void
    {
        $user1 = $this->createWechatUser(['openid' => 'count_association_test_1']);
        $user2 = $this->createWechatUser(['openid' => 'count_association_test_2']);
        $this->persistAndFlush($user1);
        $this->persistAndFlush($user2);

        $count = $this->repository->count(['config' => $this->config]);

        $this->assertGreaterThanOrEqual(2, $count);
    }

    // PHPStan required test for findBy IS NULL query

    // PHPStan required test for count IS NULL query

    // ==================== 辅助方法 ====================

    private function cleanupAllData(): void
    {
        $em = self::getEntityManager();
        $em->createQuery('DELETE FROM Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2User')->execute();
        $em->createQuery('DELETE FROM Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config')->execute();
        $em->createQuery('DELETE FROM WechatOfficialAccountBundle\Entity\Account')->execute();
        $em->clear();

        $this->persistAndFlush($this->account);
        $this->persistAndFlush($this->config);
    }

    /** @param array<string, mixed> $data */
    private function createWechatUser(array $data = []): WechatOAuth2User
    {
        $defaults = [
            'config' => $this->config,
            'openid' => 'test_openid_' . uniqid(),
            'accessToken' => 'access_token_' . uniqid(),
            'refreshToken' => 'refresh_token_' . uniqid(),
            'expiresIn' => 7200,
        ];

        $mergedData = array_merge($defaults, $data);

        $user = new WechatOAuth2User();

        $this->setRequiredFields($user, $mergedData);
        $this->setOptionalFields($user, $mergedData);

        return $user;
    }

    /** @param array<string, mixed> $mergedData */
    private function setRequiredFields(WechatOAuth2User $user, array $mergedData): void
    {
        $config = $mergedData['config'];
        $this->assertInstanceOf(WechatOAuth2Config::class, $config);
        $user->setConfig($config);

        $openid = $mergedData['openid'];
        $this->assertIsString($openid);
        $user->setOpenId($openid);

        $accessToken = $mergedData['accessToken'];
        $this->assertIsString($accessToken);
        $user->setAccessToken($accessToken);

        $refreshToken = $mergedData['refreshToken'];
        $this->assertIsString($refreshToken);
        $user->setRefreshToken($refreshToken);

        $expiresIn = $mergedData['expiresIn'];
        $this->assertIsInt($expiresIn);
        $user->setExpiresIn($expiresIn);
    }

    /** @param array<string, mixed> $mergedData */
    private function setOptionalFields(WechatOAuth2User $user, array $mergedData): void
    {
        $this->setOptionalUnionId($user, $mergedData);
        $this->setOptionalNickname($user, $mergedData);
        $this->setOptionalSex($user, $mergedData);
        $this->setOptionalProvince($user, $mergedData);
        $this->setOptionalCity($user, $mergedData);
        $this->setOptionalCountry($user, $mergedData);
        $this->setOptionalHeadimgurl($user, $mergedData);
        $this->setOptionalPrivilege($user, $mergedData);
        $this->setOptionalScope($user, $mergedData);
        $this->setOptionalRawData($user, $mergedData);
    }

    /** @param array<string, mixed> $mergedData */
    private function setOptionalUnionId(WechatOAuth2User $user, array $mergedData): void
    {
        if (!array_key_exists('unionid', $mergedData)) {
            return;
        }

        $value = $mergedData['unionid'];
        if (is_string($value) || null === $value) {
            $user->setUnionId($value);
        }
    }

    /** @param array<string, mixed> $mergedData */
    private function setOptionalNickname(WechatOAuth2User $user, array $mergedData): void
    {
        if (!array_key_exists('nickname', $mergedData)) {
            return;
        }

        $value = $mergedData['nickname'];
        if (is_string($value) || null === $value) {
            $user->setNickname($value);
        }
    }

    /** @param array<string, mixed> $mergedData */
    private function setOptionalSex(WechatOAuth2User $user, array $mergedData): void
    {
        if (!array_key_exists('sex', $mergedData)) {
            return;
        }

        $value = $mergedData['sex'];
        if (is_int($value) || null === $value) {
            $user->setSex($value);
        }
    }

    /** @param array<string, mixed> $mergedData */
    private function setOptionalProvince(WechatOAuth2User $user, array $mergedData): void
    {
        if (!array_key_exists('province', $mergedData)) {
            return;
        }

        $value = $mergedData['province'];
        if (is_string($value) || null === $value) {
            $user->setProvince($value);
        }
    }

    /** @param array<string, mixed> $mergedData */
    private function setOptionalCity(WechatOAuth2User $user, array $mergedData): void
    {
        if (!array_key_exists('city', $mergedData)) {
            return;
        }

        $value = $mergedData['city'];
        if (is_string($value) || null === $value) {
            $user->setCity($value);
        }
    }

    /** @param array<string, mixed> $mergedData */
    private function setOptionalCountry(WechatOAuth2User $user, array $mergedData): void
    {
        if (!array_key_exists('country', $mergedData)) {
            return;
        }

        $value = $mergedData['country'];
        if (is_string($value) || null === $value) {
            $user->setCountry($value);
        }
    }

    /** @param array<string, mixed> $mergedData */
    private function setOptionalHeadimgurl(WechatOAuth2User $user, array $mergedData): void
    {
        if (!array_key_exists('headimgurl', $mergedData)) {
            return;
        }

        $value = $mergedData['headimgurl'];
        if (is_string($value) || null === $value) {
            $user->setHeadimgurl($value);
        }
    }

    /** @param array<string, mixed> $mergedData */
    private function setOptionalPrivilege(WechatOAuth2User $user, array $mergedData): void
    {
        if (!array_key_exists('privilege', $mergedData)) {
            return;
        }

        $value = $mergedData['privilege'];
        if (null === $value) {
            $user->setPrivilege(null);

            return;
        }

        if (is_array($value)) {
            /** @var array<string> $value */
            $user->setPrivilege($value);
        }
    }

    /** @param array<string, mixed> $mergedData */
    private function setOptionalScope(WechatOAuth2User $user, array $mergedData): void
    {
        if (!array_key_exists('scope', $mergedData)) {
            return;
        }

        $value = $mergedData['scope'];
        if (is_string($value) || null === $value) {
            $user->setScope($value);
        }
    }

    /** @param array<string, mixed> $mergedData */
    private function setOptionalRawData(WechatOAuth2User $user, array $mergedData): void
    {
        if (!array_key_exists('rawData', $mergedData)) {
            return;
        }

        $value = $mergedData['rawData'];
        if (null === $value) {
            $user->setRawData(null);

            return;
        }

        if (is_array($value)) {
            /** @var array<string, mixed> $value */
            $user->setRawData($value);
        }
    }

    protected function createNewEntity(): object
    {
        return $this->createWechatUser(['openid' => 'new_entity_openid_' . uniqid()]);
    }

    /** @return ServiceEntityRepository<WechatOAuth2User> */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
