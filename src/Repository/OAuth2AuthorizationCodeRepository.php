<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AuthorizationCode;

/**
 * @extends ServiceEntityRepository<OAuth2AuthorizationCode>
 */
#[AsRepository(entityClass: OAuth2AuthorizationCode::class)]
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
        $result = $this->createQueryBuilder('ac')
            ->where('ac.code = :code')
            ->andWhere('ac.used = :used')
            ->andWhere('ac.expiresAt > :now')
            ->setParameter('code', $code)
            ->setParameter('used', false)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult()
        ;
        assert($result instanceof OAuth2AuthorizationCode || null === $result);

        return $result;
    }

    /**
     * @return array<OAuth2AuthorizationCode>
     */
    public function findExpiredCodes(?\DateTime $beforeDate = null): array
    {
        $beforeDate ??= new \DateTime();

        $result = $this->createQueryBuilder('ac')
            ->where('ac.expiresAt < :beforeDate')
            ->setParameter('beforeDate', $beforeDate)
            ->getQuery()
            ->getResult()
        ;
        assert(is_array($result));
        /** @var array<OAuth2AuthorizationCode> $result */

        return $result;
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
        $beforeDate ??= new \DateTime();

        $result = $this->createQueryBuilder('ac')
            ->delete()
            ->where('ac.expiresAt < :beforeDate')
            ->setParameter('beforeDate', $beforeDate)
            ->getQuery()
            ->execute()
        ;
        assert(is_int($result));

        return $result;
    }

    public function deleteUsedCodes(): int
    {
        $result = $this->createQueryBuilder('ac')
            ->delete()
            ->where('ac.used = :used')
            ->setParameter('used', true)
            ->getQuery()
            ->execute()
        ;
        assert(is_int($result));

        return $result;
    }

    public function save(OAuth2AuthorizationCode $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OAuth2AuthorizationCode $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
