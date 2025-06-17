<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2State;

/**
 * @extends ServiceEntityRepository<WechatOAuth2State>
 *
 * @method WechatOAuth2State|null find($id, $lockMode = null, $lockVersion = null)
 * @method WechatOAuth2State|null findOneBy(array $criteria, array $orderBy = null)
 * @method WechatOAuth2State[] findAll()
 * @method WechatOAuth2State[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WechatOAuth2StateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WechatOAuth2State::class);
    }

    public function findValidState(string $state): ?WechatOAuth2State
    {
        return $this->createQueryBuilder('s')
            ->where('s.state = :state')
            ->andWhere('s.valid = :valid')
            ->andWhere('s.expiresTime > :now')
            ->setParameter('state', $state)
            ->setParameter('valid', true)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function cleanupExpiredStates(): int
    {
        $qb = $this->createQueryBuilder('s');
        
        return $qb->delete()
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
            ->execute();
    }

    /**
     * @return array<WechatOAuth2State>
     */
    public function findUnusedBySessionId(string $sessionId): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.sessionId = :sessionId')
            ->andWhere('s.valid = :valid')
            ->andWhere('s.expiresTime > :now')
            ->setParameter('sessionId', $sessionId)
            ->setParameter('valid', true)
            ->setParameter('now', new \DateTime())
            ->orderBy('s.createTime', 'DESC')
            ->getQuery()
            ->getResult();
    }
}