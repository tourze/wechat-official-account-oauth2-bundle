<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;

/**
 * @extends ServiceEntityRepository<WechatOAuth2Config>
 *
 * @method WechatOAuth2Config|null find($id, $lockMode = null, $lockVersion = null)
 * @method WechatOAuth2Config|null findOneBy(array $criteria, array $orderBy = null)
 * @method WechatOAuth2Config[] findAll()
 * @method WechatOAuth2Config[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WechatOAuth2ConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WechatOAuth2Config::class);
    }

    public function findValidConfig(): ?WechatOAuth2Config
    {
        $config = $this->findOneBy(['valid' => true, 'isDefault' => true]);
        
        if ($config === null) {
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
           ->execute();
        
        $config->setIsDefault(true);
        $this->getEntityManager()->persist($config);
        $this->getEntityManager()->flush();
    }

    public function clearCache(): void
    {
        $this->getEntityManager()->clear();
    }
}