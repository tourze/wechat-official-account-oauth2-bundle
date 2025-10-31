<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AccessToken;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\OAuth2AccessTokenRepository;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * @internal
 */
#[CoversClass(OAuth2AccessTokenRepository::class)]
#[RunTestsInSeparateProcesses]
final class OAuth2AccessTokenRepositoryTest extends AbstractRepositoryTestCase
{
    private OAuth2AccessTokenRepository $repository;

    private Account $testAccount;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(OAuth2AccessTokenRepository::class);
        $this->testAccount = $this->createTestAccount();
    }

    public function testRepositoryInstance(): void
    {
        $repository = self::getService(OAuth2AccessTokenRepository::class);
        $this->assertInstanceOf(OAuth2AccessTokenRepository::class, $repository);
    }

    // Basic find method tests

    // IS NULL query tests for nullable fields
    public function testCountWithNullRefreshToken(): void
    {
        $token = $this->createTestToken('test_token', 'test_openid');
        $token->setRefreshToken(null);
        $this->repository->save($token);

        $count = $this->repository->count(['refreshToken' => null]);

        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountWithNullUnionid(): void
    {
        $token = $this->createTestToken('test_token', 'test_openid');
        $token->setUnionId(null);
        $this->repository->save($token);

        $count = $this->repository->count(['unionid' => null]);

        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountWithNullRefreshTokenExpiresAt(): void
    {
        $token = $this->createTestToken('test_token', 'test_openid');
        $token->setRefreshTokenExpiresAt(null);
        $this->repository->save($token);

        $count = $this->repository->count(['refreshTokenExpiresAt' => null]);

        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByWithNullUnionid(): void
    {
        $token = $this->createTestToken('test_token', 'test_openid');
        $token->setUnionId(null);
        $this->repository->save($token);

        $result = $this->repository->findBy(['unionid' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    public function testFindByWithNullRefreshToken(): void
    {
        $token = $this->createTestToken('test_token', 'test_openid');
        $token->setRefreshToken(null);
        $this->repository->save($token);

        $result = $this->repository->findBy(['refreshToken' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    public function testFindByWithNullRefreshTokenExpiresAt(): void
    {
        $token = $this->createTestToken('test_token', 'test_openid');
        $token->setRefreshTokenExpiresAt(null);
        $this->repository->save($token);

        $result = $this->repository->findBy(['refreshTokenExpiresAt' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    public function testFindByWithNullScopes(): void
    {
        $token = $this->createTestToken('test_token', 'test_openid');
        $token->setScopes(null);
        $this->repository->save($token);

        $result = $this->repository->findBy(['scopes' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    public function testCountWithNullScopes(): void
    {
        $token = $this->createTestToken('test_token', 'test_openid');
        $token->setScopes(null);
        $this->repository->save($token);

        $count = $this->repository->count(['scopes' => null]);

        $this->assertGreaterThanOrEqual(1, $count);
    }

    // Association query tests
    public function testCountWithAssociation(): void
    {
        $token = $this->createTestToken('test_token', 'test_openid');
        $token->setWechatAccount($this->testAccount);
        $this->repository->save($token);

        $count = $this->repository->count(['wechatAccount' => $this->testAccount]);

        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByWithAssociation(): void
    {
        $token = $this->createTestToken('test_token', 'test_openid');
        $token->setWechatAccount($this->testAccount);
        $this->repository->save($token);

        $result = $this->repository->findBy(['wechatAccount' => $this->testAccount]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    public function testFindOneByWithAssociation(): void
    {
        $token = $this->createTestToken('test_token', 'test_openid');
        $token->setWechatAccount($this->testAccount);
        $this->repository->save($token);

        $result = $this->repository->findOneBy(['wechatAccount' => $this->testAccount]);

        $this->assertInstanceOf(OAuth2AccessToken::class, $result);
    }

    public function testCountWithWechatAccountAssociation(): void
    {
        $token = $this->createTestToken('test_token', 'test_openid');
        $token->setWechatAccount($this->testAccount);
        $this->repository->save($token);

        $count = $this->repository->count(['wechatAccount' => $this->testAccount]);

        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByWithWechatAccountAssociation(): void
    {
        $token = $this->createTestToken('test_token', 'test_openid');
        $token->setWechatAccount($this->testAccount);
        $this->repository->save($token);

        $result = $this->repository->findBy(['wechatAccount' => $this->testAccount]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    public function testFindOneByWithWechatAccountAssociation(): void
    {
        $token = $this->createTestToken('test_token', 'test_openid');
        $token->setWechatAccount($this->testAccount);
        $this->repository->save($token);

        $result = $this->repository->findOneBy(['wechatAccount' => $this->testAccount]);

        $this->assertInstanceOf(OAuth2AccessToken::class, $result);
    }

    // FindBy method tests

    // FindOneBy method tests

    public function testFindOneByWithOrderByClause(): void
    {
        $token1 = $this->createTestToken('token1', 'aaa_openid');
        $token2 = $this->createTestToken('token2', 'zzz_openid');
        $this->repository->save($token1);
        $this->repository->save($token2);

        $resultAsc = $this->repository->findOneBy([], ['openid' => 'ASC']);
        $resultDesc = $this->repository->findOneBy([], ['openid' => 'DESC']);

        $this->assertInstanceOf(OAuth2AccessToken::class, $resultAsc);
        $this->assertInstanceOf(OAuth2AccessToken::class, $resultDesc);
    }

    public function testFindOneByWithOrderByShouldRespectSortingLogic(): void
    {
        // Clear existing data for clean test
        self::getEntityManager()->createQuery('DELETE FROM ' . OAuth2AccessToken::class)->execute();

        $token1 = $this->createTestToken('token1', 'aaa_openid_test');
        $token2 = $this->createTestToken('token2', 'zzz_openid_test');
        $this->repository->save($token1);
        $this->repository->save($token2);

        $resultAsc = $this->repository->findOneBy([], ['openid' => 'ASC']);
        $resultDesc = $this->repository->findOneBy([], ['openid' => 'DESC']);

        $this->assertInstanceOf(OAuth2AccessToken::class, $resultAsc);
        $this->assertInstanceOf(OAuth2AccessToken::class, $resultDesc);

        // Verify that ASC ordering returns the first alphabetically
        $this->assertNotNull($resultAsc->getOpenId());
        $this->assertStringContainsString('aaa_openid_test', $resultAsc->getOpenId());

        // Verify that DESC ordering returns the last alphabetically
        $this->assertNotNull($resultDesc->getOpenId());
        $this->assertStringContainsString('zzz_openid_test', $resultDesc->getOpenId());

        // Test that ordering is actually working
        $this->assertNotEquals($resultAsc->getId(), $resultDesc->getId());
    }

    // PHPStan required test for findOneBy sorting logic

    // PHPStan required test for association query
    public function testFindOneByAssociationWechatAccountShouldReturnMatchingEntity(): void
    {
        $token = $this->createTestToken('test_token', 'test_openid');
        $token->setWechatAccount($this->testAccount);
        $this->repository->save($token);

        $result = $this->repository->findOneBy(['wechatAccount' => $this->testAccount]);

        $this->assertInstanceOf(OAuth2AccessToken::class, $result);
        $this->assertNotNull($result->getWechatAccount());
        $this->assertEquals($this->testAccount->getId(), $result->getWechatAccount()->getId());
    }

    // PHPStan required test for IS NULL query

    // PHPStan required test for findBy association query

    // PHPStan required test for count association query
    public function testCountByAssociationWechatAccountShouldReturnCorrectNumber(): void
    {
        $token1 = $this->createTestToken('token1', 'openid1');
        $token1->setWechatAccount($this->testAccount);
        $token2 = $this->createTestToken('token2', 'openid2');
        $token2->setWechatAccount($this->testAccount);
        $this->repository->save($token1);
        $this->repository->save($token2);

        $count = $this->repository->count(['wechatAccount' => $this->testAccount]);

        $this->assertGreaterThanOrEqual(2, $count);
    }

    // PHPStan required test for findBy IS NULL query

    // PHPStan required test for count IS NULL query

    // FindAll method tests

    // Save and Remove method tests
    public function testSave(): void
    {
        $token = $this->createTestToken('save_test_token', 'save_test_openid');

        $this->repository->save($token);

        $this->assertNotNull($token->getId());
        $saved = $this->repository->find($token->getId());
        $this->assertInstanceOf(OAuth2AccessToken::class, $saved);
    }

    public function testSaveWithoutFlush(): void
    {
        $token = $this->createTestToken('save_no_flush_token', 'save_no_flush_openid');

        $this->repository->save($token, false);

        // After persisting without flush, the entity might not have an ID yet
        // But we can verify the save method was called without errors
        $this->assertInstanceOf(OAuth2AccessToken::class, $token);
    }

    public function testRemove(): void
    {
        $token = $this->createTestToken('remove_test_token', 'remove_test_openid');
        $this->repository->save($token);
        $id = $token->getId();

        $this->repository->remove($token);

        $result = $this->repository->find($id);
        $this->assertNull($result);
    }

    // Custom repository method tests
    public function testFindExpiredTokensWithDefaultDate(): void
    {
        $result = $this->repository->findExpiredTokens();

        $this->assertIsArray($result);
    }

    public function testFindExpiredTokensWithSpecificDate(): void
    {
        $beforeDate = new \DateTime('2023-01-01');

        $result = $this->repository->findExpiredTokens($beforeDate);

        $this->assertIsArray($result);
    }

    public function testDeleteExpiredTokensWithDefaultDate(): void
    {
        $result = $this->repository->deleteExpiredTokens();

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testDeleteExpiredTokensWithSpecificDate(): void
    {
        $beforeDate = new \DateTime('2023-01-01');

        $result = $this->repository->deleteExpiredTokens($beforeDate);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testDeleteRevokedTokens(): void
    {
        $result = $this->repository->deleteRevokedTokens();

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testFindByAccessToken(): void
    {
        $result = $this->repository->findByAccessToken('nonexistent_access_token');

        $this->assertNull($result);
    }

    public function testFindByRefreshToken(): void
    {
        $result = $this->repository->findByRefreshToken('nonexistent_refresh_token');

        $this->assertNull($result);
    }

    public function testFindValidByAccessToken(): void
    {
        $result = $this->repository->findValidByAccessToken('nonexistent_access_token');

        $this->assertNull($result);
    }

    public function testFindValidByRefreshToken(): void
    {
        $result = $this->repository->findValidByRefreshToken('nonexistent_refresh_token');

        $this->assertNull($result);
    }

    public function testFindRevokedTokens(): void
    {
        $result = $this->repository->findRevokedTokens();

        $this->assertIsArray($result);
    }

    public function testRevokeTokensByOpenid(): void
    {
        $result = $this->repository->revokeTokensByOpenid('nonexistent_openid');

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testFindByOpenidAndAccount(): void
    {
        $result = $this->repository->findByOpenidAndAccount('nonexistent_openid', $this->testAccount);

        $this->assertNull($result);
    }

    public function testRevokeTokensByAccount(): void
    {
        $result = $this->repository->revokeTokensByAccount($this->testAccount);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    private function createTestAccount(): Account
    {
        $account = new Account();
        $account->setAppId('test_app_id_' . uniqid());
        $account->setAppSecret('test_app_secret');
        $account->setName('Test Account');

        // Persist the account first
        $em = self::getEntityManager();
        $em->persist($account);
        $em->flush();

        return $account;
    }

    private function createTestToken(string $accessToken = 'test_access_token', string $openid = 'test_openid'): OAuth2AccessToken
    {
        $token = new OAuth2AccessToken();
        $token->setAccessToken($accessToken . '_' . uniqid()); // Make it unique
        $token->setOpenId($openid . '_' . uniqid()); // Make it unique
        $token->setAccessTokenExpiresAt(new \DateTimeImmutable('+1 hour'));
        $token->setWechatAccount($this->testAccount);
        $token->setRevoked(false);

        return $token;
    }

    protected function createNewEntity(): object
    {
        return $this->createTestToken('new_entity_token', 'new_entity_openid');
    }

    /** @return ServiceEntityRepository<OAuth2AccessToken> */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
