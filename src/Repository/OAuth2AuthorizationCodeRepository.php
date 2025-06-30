<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AuthorizationCode;

/**
 * @extends ServiceEntityRepository<OAuth2AuthorizationCode>
 */
class OAuth2AuthorizationCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OAuth2AuthorizationCode::class);
    }

    public function findByCode(string $code): ?OAuth2AuthorizationCode
    {
        return $this->findOneBy(['code' => $code]);
    }

    public function findValidByCode(string $code): ?OAuth2AuthorizationCode
    {
        return $this->createQueryBuilder('ac')
            ->where('ac.code = :code')
            ->andWhere('ac.used = :used')
            ->andWhere('ac.expiresAt > :now')
            ->setParameter('code', $code)
            ->setParameter('used', false)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<OAuth2AuthorizationCode>
     */
    public function findExpiredCodes(?\DateTime $beforeDate = null): array
    {
        $beforeDate = $beforeDate ?? new \DateTime();
        
        return $this->createQueryBuilder('ac')
            ->where('ac.expiresAt < :beforeDate')
            ->setParameter('beforeDate', $beforeDate)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<OAuth2AuthorizationCode>
     */
    public function findUsedCodes(): array
    {
        return $this->findBy(['used' => true]);
    }

    public function deleteExpiredCodes(?\DateTime $beforeDate = null): int
    {
        $beforeDate = $beforeDate ?? new \DateTime();
        
        return $this->createQueryBuilder('ac')
            ->delete()
            ->where('ac.expiresAt < :beforeDate')
            ->setParameter('beforeDate', $beforeDate)
            ->getQuery()
            ->execute();
    }

    public function deleteUsedCodes(): int
    {
        return $this->createQueryBuilder('ac')
            ->delete()
            ->where('ac.used = :used')
            ->setParameter('used', true)
            ->getQuery()
            ->execute();
    }

    public function save(OAuth2AuthorizationCode $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OAuth2AuthorizationCode $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}