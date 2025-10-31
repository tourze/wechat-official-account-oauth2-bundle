<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2State;

/**
 * @extends ServiceEntityRepository<WechatOAuth2State>
 */
#[AsRepository(entityClass: WechatOAuth2State::class)]
class WechatOAuth2StateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WechatOAuth2State::class);
    }

    public function findValidState(string $state): ?WechatOAuth2State
    {
        $result = $this->createQueryBuilder('s')
            ->where('s.state = :state')
            ->andWhere('s.valid = :valid')
            ->andWhere('s.expiresTime > :now')
            ->setParameter('state', $state)
            ->setParameter('valid', true)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult()
        ;
        assert($result instanceof WechatOAuth2State || null === $result);

        return $result;
    }

    public function cleanupExpiredStates(): int
    {
        $qb = $this->createQueryBuilder('s');

        $result = $qb->delete()
            ->where($qb->expr()->orX(
                $qb->expr()->lt('s.expiresTime', ':now'),
                $qb->expr()->andX(
                    $qb->expr()->eq('s.valid', ':invalid'),
                    $qb->expr()->lt('s.usedTime', ':oldDate')
                )
            ))
            ->setParameter('now', new \DateTime())
            ->setParameter('invalid', false)
            ->setParameter('oldDate', new \DateTime('-1 day'))
            ->getQuery()
            ->execute()
        ;
        assert(is_int($result));

        return $result;
    }

    /**
     * @return array<WechatOAuth2State>
     */
    public function findUnusedBySessionId(string $sessionId): array
    {
        $result = $this->createQueryBuilder('s')
            ->where('s.sessionId = :sessionId')
            ->andWhere('s.valid = :valid')
            ->andWhere('s.expiresTime > :now')
            ->setParameter('sessionId', $sessionId)
            ->setParameter('valid', true)
            ->setParameter('now', new \DateTime())
            ->orderBy('s.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
        assert(is_array($result));
        /** @var array<WechatOAuth2State> $result */

        return $result;
    }

    public function save(WechatOAuth2State $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WechatOAuth2State $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
