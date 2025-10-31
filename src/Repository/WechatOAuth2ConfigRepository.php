<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;

/**
 * @extends ServiceEntityRepository<WechatOAuth2Config>
 */
#[AsRepository(entityClass: WechatOAuth2Config::class)]
class WechatOAuth2ConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WechatOAuth2Config::class);
    }

    public function findValidConfig(): ?WechatOAuth2Config
    {
        $config = $this->findOneBy(['valid' => true, 'isDefault' => true]);

        if (null === $config) {
            $config = $this->findOneBy(['valid' => true]);
        }

        return $config;
    }

    public function setDefault(WechatOAuth2Config $config): void
    {
        $qb = $this->createQueryBuilder('c');
        $qb->update()
            ->set('c.isDefault', ':false')
            ->where('c.id != :id')
            ->setParameter('false', false)
            ->setParameter('id', $config->getId())
            ->getQuery()
            ->execute()
        ;

        $config->setIsDefault(true);
    }

    public function clearCache(): void
    {
        $this->getEntityManager()->clear();
    }

    public function save(WechatOAuth2Config $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WechatOAuth2Config $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
