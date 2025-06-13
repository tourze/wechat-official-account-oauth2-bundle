<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AccessToken;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * @extends ServiceEntityRepository<OAuth2AccessToken>
 */
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
        return $this->createQueryBuilder('at')
            ->where('at.accessToken = :accessToken')
            ->andWhere('at.revoked = :revoked')
            ->andWhere('at.accessTokenExpiresAt > :now')
            ->setParameter('accessToken', $accessToken)
            ->setParameter('revoked', false)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByRefreshToken(string $refreshToken): ?OAuth2AccessToken
    {
        return $this->findOneBy(['refreshToken' => $refreshToken]);
    }

    public function findValidByRefreshToken(string $refreshToken): ?OAuth2AccessToken
    {
        return $this->createQueryBuilder('at')
            ->where('at.refreshToken = :refreshToken')
            ->andWhere('at.revoked = :revoked')
            ->andWhere('(at.refreshTokenExpiresAt IS NULL OR at.refreshTokenExpiresAt > :now)')
            ->setParameter('refreshToken', $refreshToken)
            ->setParameter('revoked', false)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByOpenidAndAccount(string $openid, Account $account): ?OAuth2AccessToken
    {
        return $this->createQueryBuilder('at')
            ->where('at.openid = :openid')
            ->andWhere('at.wechatAccount = :account')
            ->andWhere('at.revoked = :revoked')
            ->andWhere('at.accessTokenExpiresAt > :now')
            ->setParameter('openid', $openid)
            ->setParameter('account', $account)
            ->setParameter('revoked', false)
            ->setParameter('now', new \DateTime())
            ->orderBy('at.createTime', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findExpiredTokens(?\DateTime $beforeDate = null): array
    {
        $beforeDate = $beforeDate ?: new \DateTime();
        
        return $this->createQueryBuilder('at')
            ->where('at.accessTokenExpiresAt < :beforeDate')
            ->setParameter('beforeDate', $beforeDate)
            ->getQuery()
            ->getResult();
    }

    public function findRevokedTokens(): array
    {
        return $this->findBy(['revoked' => true]);
    }

    public function revokeTokensByOpenid(string $openid): int
    {
        return $this->createQueryBuilder('at')
            ->update()
            ->set('at.revoked', ':revoked')
            ->where('at.openid = :openid')
            ->setParameter('revoked', true)
            ->setParameter('openid', $openid)
            ->getQuery()
            ->execute();
    }

    public function revokeTokensByAccount(Account $account): int
    {
        return $this->createQueryBuilder('at')
            ->update()
            ->set('at.revoked', ':revoked')
            ->where('at.wechatAccount = :account')
            ->setParameter('revoked', true)
            ->setParameter('account', $account)
            ->getQuery()
            ->execute();
    }

    public function deleteExpiredTokens(?\DateTime $beforeDate = null): int
    {
        $beforeDate = $beforeDate ?: new \DateTime();
        
        return $this->createQueryBuilder('at')
            ->delete()
            ->where('at.accessTokenExpiresAt < :beforeDate')
            ->setParameter('beforeDate', $beforeDate)
            ->getQuery()
            ->execute();
    }

    public function deleteRevokedTokens(): int
    {
        return $this->createQueryBuilder('at')
            ->delete()
            ->where('at.revoked = :revoked')
            ->setParameter('revoked', true)
            ->getQuery()
            ->execute();
    }

    public function save(OAuth2AccessToken $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OAuth2AccessToken $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}