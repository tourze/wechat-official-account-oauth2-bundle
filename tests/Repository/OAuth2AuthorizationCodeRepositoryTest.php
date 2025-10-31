<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AuthorizationCode;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\OAuth2AuthorizationCodeRepository;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * @internal
 */
#[CoversClass(OAuth2AuthorizationCodeRepository::class)]
#[RunTestsInSeparateProcesses]
final class OAuth2AuthorizationCodeRepositoryTest extends AbstractRepositoryTestCase
{
    private OAuth2AuthorizationCodeRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(OAuth2AuthorizationCodeRepository::class);
    }

    public function testRepositoryInstance(): void
    {
        $this->assertInstanceOf(OAuth2AuthorizationCodeRepository::class, $this->repository);
    }

    public function testFindByCode(): void
    {
        $result = $this->repository->findByCode('nonexistent_code');
        $this->assertNull($result);

        $code = $this->createTestAuthorizationCode();
        $uniqueCode = 'valid_auth_code_' . uniqid();
        $code->setCode($uniqueCode);
        $this->repository->save($code);

        $result = $this->repository->findByCode($uniqueCode);
        $this->assertInstanceOf(OAuth2AuthorizationCode::class, $result);
        $this->assertSame($uniqueCode, $result->getCode());
    }

    public function testDeleteExpiredCodes(): void
    {
        $result = $this->repository->deleteExpiredCodes();
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testDeleteExpiredCodesWithSpecificDate(): void
    {
        $beforeDate = new \DateTime('2023-01-01');
        $result = $this->repository->deleteExpiredCodes($beforeDate);
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testDeleteUsedCodes(): void
    {
        $result = $this->repository->deleteUsedCodes();
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testFindExpiredCodes(): void
    {
        $result = $this->repository->findExpiredCodes();
        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(OAuth2AuthorizationCode::class, $result);
    }

    public function testFindUsedCodes(): void
    {
        $result = $this->repository->findUsedCodes();
        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(OAuth2AuthorizationCode::class, $result);
    }

    public function testFindValidByCode(): void
    {
        $result = $this->repository->findValidByCode('nonexistent_code');
        $this->assertNull($result);

        $code = $this->createTestAuthorizationCode();
        $uniqueCode = 'valid_auth_code_' . uniqid();
        $code->setCode($uniqueCode);
        $code->setUsed(false);
        $code->setExpiresAt(new \DateTimeImmutable('+1 hour'));
        $this->repository->save($code);

        $result = $this->repository->findValidByCode($uniqueCode);
        $this->assertInstanceOf(OAuth2AuthorizationCode::class, $result);
        $this->assertSame($uniqueCode, $result->getCode());
        $this->assertFalse($result->isUsed());
        $this->assertFalse($result->isExpired());
    }

    // Save and Remove tests
    public function testSave(): void
    {
        $code = $this->createTestAuthorizationCode();
        $code->setCode('test_save_code');

        $this->repository->save($code);

        $this->assertNotNull($code->getId());

        $foundCode = $this->repository->find($code->getId());
        $this->assertInstanceOf(OAuth2AuthorizationCode::class, $foundCode);
        $this->assertSame('test_save_code', $foundCode->getCode());
    }

    public function testSaveWithoutFlush(): void
    {
        $code = $this->createTestAuthorizationCode();
        $code->setCode('test_save_no_flush_code');

        $this->repository->save($code, false);
        self::getEntityManager()->flush();

        $this->assertNotNull($code->getId());
    }

    public function testRemove(): void
    {
        $code = $this->createTestAuthorizationCode();
        $this->repository->save($code);
        $codeId = $code->getId();

        $this->repository->remove($code);

        $foundCode = $this->repository->find($codeId);
        $this->assertNull($foundCode);
    }

    // FindBy tests

    // FindOneBy tests

    public function testFindOneByWithOrderBy(): void
    {
        // Clear existing data to ensure clean test
        self::getEntityManager()->createQuery('DELETE FROM ' . OAuth2AuthorizationCode::class)->execute();

        $code1 = $this->createTestAuthorizationCode();
        $code1->setCode('aaa_code_order_test');
        $code2 = $this->createTestAuthorizationCode();
        $code2->setCode('zzz_code_order_test');
        $this->repository->save($code1);
        $this->repository->save($code2);

        $result = $this->repository->findOneBy([], ['code' => 'DESC']);
        $this->assertInstanceOf(OAuth2AuthorizationCode::class, $result);
        $this->assertSame('zzz_code_order_test', $result->getCode());
    }

    public function testFindOneByWithOrderByShouldRespectSortingLogic(): void
    {
        // Clear existing data to ensure clean test
        self::getEntityManager()->createQuery('DELETE FROM ' . OAuth2AuthorizationCode::class)->execute();

        $code1 = $this->createTestAuthorizationCode();
        $code1->setCode('aaa_sorting_test');
        $code2 = $this->createTestAuthorizationCode();
        $code2->setCode('zzz_sorting_test');
        $this->repository->save($code1);
        $this->repository->save($code2);

        $resultAsc = $this->repository->findOneBy([], ['code' => 'ASC']);
        $resultDesc = $this->repository->findOneBy([], ['code' => 'DESC']);

        $this->assertInstanceOf(OAuth2AuthorizationCode::class, $resultAsc);
        $this->assertInstanceOf(OAuth2AuthorizationCode::class, $resultDesc);

        // Verify ASC returns first alphabetically
        $this->assertSame('aaa_sorting_test', $resultAsc->getCode());

        // Verify DESC returns last alphabetically
        $this->assertSame('zzz_sorting_test', $resultDesc->getCode());

        // Verify different results for different sorting
        $this->assertNotEquals($resultAsc->getId(), $resultDesc->getId());
    }

    // FindAll tests

    // Find (by ID) tests

    // Association query tests
    public function testFindByWechatAccount(): void
    {
        $account = $this->createTestAccount();
        self::getEntityManager()->persist($account);

        $code = $this->createTestAuthorizationCode();
        $code->setWechatAccount($account);
        $this->repository->save($code);

        $results = $this->repository->findBy(['wechatAccount' => $account]);
        $this->assertIsArray($results);
        $this->assertContainsOnlyInstancesOf(OAuth2AuthorizationCode::class, $results);

        foreach ($results as $result) {
            $this->assertNotNull($result->getWechatAccount());
            $this->assertSame($account->getId(), $result->getWechatAccount()->getId());
        }
    }

    public function testCountByWechatAccount(): void
    {
        $account = $this->createTestAccount();
        self::getEntityManager()->persist($account);

        $code = $this->createTestAuthorizationCode();
        $code->setWechatAccount($account);
        $this->repository->save($code);

        $count = $this->repository->count(['wechatAccount' => $account]);
        $this->assertIsInt($count);
        $this->assertSame(1, $count);
    }

    // Nullable field tests
    public function testFindByNullUnionid(): void
    {
        $code = $this->createTestAuthorizationCode();
        $code->setUnionId(null);
        $this->repository->save($code);

        $results = $this->repository->findBy(['unionid' => null]);
        $this->assertIsArray($results);
        $this->assertContainsOnlyInstancesOf(OAuth2AuthorizationCode::class, $results);
    }

    public function testFindByNullScopes(): void
    {
        $code = $this->createTestAuthorizationCode();
        $code->setScopes(null);
        $this->repository->save($code);

        $results = $this->repository->findBy(['scopes' => null]);
        $this->assertIsArray($results);
        $this->assertContainsOnlyInstancesOf(OAuth2AuthorizationCode::class, $results);
    }

    public function testFindByNullState(): void
    {
        $code = $this->createTestAuthorizationCode();
        $code->setState(null);
        $this->repository->save($code);

        $results = $this->repository->findBy(['state' => null]);
        $this->assertIsArray($results);
        $this->assertContainsOnlyInstancesOf(OAuth2AuthorizationCode::class, $results);
    }

    public function testCountWithNullUnionid(): void
    {
        $code = $this->createTestAuthorizationCode();
        $code->setUnionId(null);
        $this->repository->save($code);

        $count = $this->repository->count(['unionid' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountWithNullScopes(): void
    {
        $code = $this->createTestAuthorizationCode();
        $code->setScopes(null);
        $this->repository->save($code);

        $count = $this->repository->count(['scopes' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountWithNullState(): void
    {
        $code = $this->createTestAuthorizationCode();
        $code->setState(null);
        $this->repository->save($code);

        $count = $this->repository->count(['state' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    // Additional association query tests required by PHPStan
    public function testFindOneByWithWechatAccount(): void
    {
        $account = $this->createTestAccount();
        self::getEntityManager()->persist($account);

        $code = $this->createTestAuthorizationCode();
        $code->setWechatAccount($account);
        $this->repository->save($code);

        $result = $this->repository->findOneBy(['wechatAccount' => $account]);
        $this->assertInstanceOf(OAuth2AuthorizationCode::class, $result);
        $this->assertNotNull($result->getWechatAccount());
        $this->assertSame($account->getId(), $result->getWechatAccount()->getId());
    }

    public function testFindOneByWithWechatAccountOrderBy(): void
    {
        // Clear existing data to ensure clean test
        self::getEntityManager()->createQuery('DELETE FROM ' . OAuth2AuthorizationCode::class)->execute();

        $account = $this->createTestAccount();
        self::getEntityManager()->persist($account);

        $code1 = $this->createTestAuthorizationCode();
        $code1->setCode('aaa_code_with_account_test');
        $code1->setWechatAccount($account);
        $code2 = $this->createTestAuthorizationCode();
        $code2->setCode('zzz_code_with_account_test');
        $code2->setWechatAccount($account);
        $this->repository->save($code1);
        $this->repository->save($code2);

        $result = $this->repository->findOneBy(['wechatAccount' => $account], ['code' => 'DESC']);
        $this->assertInstanceOf(OAuth2AuthorizationCode::class, $result);
        $this->assertSame('zzz_code_with_account_test', $result->getCode());
    }

    // PHPStan required test for findOneBy sorting logic

    // PHPStan required test for association query
    public function testFindOneByAssociationWechatAccountShouldReturnMatchingEntity(): void
    {
        $account = $this->createTestAccount();
        self::getEntityManager()->persist($account);

        $code = $this->createTestAuthorizationCode();
        $code->setWechatAccount($account);
        $this->repository->save($code);

        $result = $this->repository->findOneBy(['wechatAccount' => $account]);

        $this->assertInstanceOf(OAuth2AuthorizationCode::class, $result);
        $this->assertNotNull($result->getWechatAccount());
        $this->assertEquals($account->getId(), $result->getWechatAccount()->getId());
    }

    // PHPStan required test for IS NULL query

    // PHPStan required test for findBy association query

    // PHPStan required test for count association query
    public function testCountByAssociationWechatAccountShouldReturnCorrectNumber(): void
    {
        $account = $this->createTestAccount();
        self::getEntityManager()->persist($account);

        $code1 = $this->createTestAuthorizationCode();
        $code1->setWechatAccount($account);
        $code2 = $this->createTestAuthorizationCode();
        $code2->setWechatAccount($account);
        $this->repository->save($code1);
        $this->repository->save($code2);

        $count = $this->repository->count(['wechatAccount' => $account]);

        $this->assertGreaterThanOrEqual(2, $count);
    }

    // PHPStan required test for findBy IS NULL query

    // PHPStan required test for count IS NULL query

    private function createTestAuthorizationCode(): OAuth2AuthorizationCode
    {
        $code = new OAuth2AuthorizationCode();
        $code->setCode('test_code_' . uniqid());
        $code->setOpenId('test_openid_' . uniqid());
        $code->setRedirectUri('https://example.com/callback');
        $code->setExpiresAt(new \DateTimeImmutable('+1 hour'));
        $code->setUsed(false);
        $account = $this->createTestAccount();
        $code->setWechatAccount($account);

        self::getEntityManager()->persist($account);

        return $code;
    }

    private function createTestAccount(): Account
    {
        $account = new Account();
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret');
        $account->setName('Test Account');

        return $account;
    }

    protected function createNewEntity(): object
    {
        return $this->createTestAuthorizationCode();
    }

    /** @return ServiceEntityRepository<OAuth2AuthorizationCode> */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
