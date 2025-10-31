<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AccessToken;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * @extends ServiceEntityRepository<OAuth2AccessToken>
 */
#[AsRepository(entityClass: OAuth2AccessToken::class)]
class OAuth2AccessTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OAuth2AccessToken::class);
    }

    public function findByAccessToken(string $accessToken): ?OAuth2AccessToken
    {
        return $this->findOneBy(['accessToken' => $accessToken]);
    }

    public function findValidByAccessToken(string $accessToken): ?OAuth2AccessToken
    {
        $result = $this->createQueryBuilder('at')
            ->where('at.accessToken = :accessToken')
            ->andWhere('at.revoked = :revoked')
            ->andWhere('at.accessTokenExpiresAt > :now')
            ->setParameter('accessToken', $accessToken)
            ->setParameter('revoked', false)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult()
        ;
        assert($result instanceof OAuth2AccessToken || null === $result);

        return $result;
    }

    public function findByRefreshToken(string $refreshToken): ?OAuth2AccessToken
    {
        return $this->findOneBy(['refreshToken' => $refreshToken]);
    }

    public function findValidByRefreshToken(string $refreshToken): ?OAuth2AccessToken
    {
        $result = $this->createQueryBuilder('at')
            ->where('at.refreshToken = :refreshToken')
            ->andWhere('at.revoked = :revoked')
            ->andWhere('(at.refreshTokenExpiresAt IS NULL OR at.refreshTokenExpiresAt > :now)')
            ->setParameter('refreshToken', $refreshToken)
            ->setParameter('revoked', false)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult()
        ;
        assert($result instanceof OAuth2AccessToken || null === $result);

        return $result;
    }

    public function findByOpenidAndAccount(string $openid, Account $account): ?OAuth2AccessToken
    {
        $result = $this->createQueryBuilder('at')
            ->where('at.openid = :openid')
            ->andWhere('at.wechatAccount = :account')
            ->andWhere('at.revoked = :revoked')
            ->andWhere('at.accessTokenExpiresAt > :now')
            ->setParameter('openid', $openid)
            ->setParameter('account', $account)
            ->setParameter('revoked', false)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('at.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        assert($result instanceof OAuth2AccessToken || null === $result);

        return $result;
    }

    /**
     * @return array<OAuth2AccessToken>
     */
    public function findExpiredTokens(?\DateTime $beforeDate = null): array
    {
        $beforeDate ??= new \DateTimeImmutable();

        $result = $this->createQueryBuilder('at')
            ->where('at.accessTokenExpiresAt < :beforeDate')
            ->setParameter('beforeDate', $beforeDate)
            ->getQuery()
            ->getResult()
        ;
        assert(is_array($result));
        /** @var array<OAuth2AccessToken> $result */

        return $result;
    }

    /**
     * @return array<OAuth2AccessToken>
     */
    public function findRevokedTokens(): array
    {
        return $this->findBy(['revoked' => true]);
    }

    public function revokeTokensByOpenid(string $openid): int
    {
        $result = $this->createQueryBuilder('at')
            ->update()
            ->set('at.revoked', ':revoked')
            ->where('at.openid = :openid')
            ->setParameter('revoked', true)
            ->setParameter('openid', $openid)
            ->getQuery()
            ->execute()
        ;
        assert(is_int($result));

        return $result;
    }

    public function revokeTokensByAccount(Account $account): int
    {
        $result = $this->createQueryBuilder('at')
            ->update()
            ->set('at.revoked', ':revoked')
            ->where('at.wechatAccount = :account')
            ->setParameter('revoked', true)
            ->setParameter('account', $account)
            ->getQuery()
            ->execute()
        ;
        assert(is_int($result));

        return $result;
    }

    public function deleteExpiredTokens(?\DateTime $beforeDate = null): int
    {
        $beforeDate ??= new \DateTimeImmutable();

        $result = $this->createQueryBuilder('at')
            ->delete()
            ->where('at.accessTokenExpiresAt < :beforeDate')
            ->setParameter('beforeDate', $beforeDate)
            ->getQuery()
            ->execute()
        ;
        assert(is_int($result));

        return $result;
    }

    public function deleteRevokedTokens(): int
    {
        $result = $this->createQueryBuilder('at')
            ->delete()
            ->where('at.revoked = :revoked')
            ->setParameter('revoked', true)
            ->getQuery()
            ->execute()
        ;
        assert(is_int($result));

        return $result;
    }

    public function save(OAuth2AccessToken $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OAuth2AccessToken $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
