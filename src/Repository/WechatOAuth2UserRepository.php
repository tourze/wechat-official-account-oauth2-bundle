<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2User;

/**
 * @extends ServiceEntityRepository<WechatOAuth2User>
 *
 * @method WechatOAuth2User|null find($id, $lockMode = null, $lockVersion = null)
 * @method WechatOAuth2User|null findOneBy(array $criteria, array $orderBy = null)
 * @method WechatOAuth2User[] findAll()
 * @method WechatOAuth2User[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WechatOAuth2UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WechatOAuth2User::class);
    }

    public function findByOpenid(string $openid): ?WechatOAuth2User
    {
        return $this->findOneBy(['openid' => $openid]);
    }

    public function findByUnionid(string $unionid): array
    {
        return $this->findBy(['unionid' => $unionid]);
    }

    public function updateOrCreate(array $userData, WechatOAuth2Config $config): WechatOAuth2User
    {
        $user = $this->findOneBy([
            'openid' => $userData['openid'],
            'config' => $config,
        ]);

        if (!$user) {
            $user = new WechatOAuth2User();
            $user->setConfig($config);
            $user->setOpenid($userData['openid']);
        }

        if (isset($userData['access_token'])) {
            $user->setAccessToken($userData['access_token']);
        }
        
        if (isset($userData['refresh_token'])) {
            $user->setRefreshToken($userData['refresh_token']);
        }
        
        if (isset($userData['expires_in'])) {
            $user->setExpiresIn((int)$userData['expires_in']);
        }
        
        if (isset($userData['scope'])) {
            $user->setScope($userData['scope']);
        }
        
        if (isset($userData['unionid'])) {
            $user->setUnionid($userData['unionid']);
        }

        if (isset($userData['nickname'])) {
            $user->setNickname($userData['nickname']);
        }
        
        if (isset($userData['sex'])) {
            $user->setSex((int)$userData['sex']);
        }
        
        if (isset($userData['province'])) {
            $user->setProvince($userData['province']);
        }
        
        if (isset($userData['city'])) {
            $user->setCity($userData['city']);
        }
        
        if (isset($userData['country'])) {
            $user->setCountry($userData['country']);
        }
        
        if (isset($userData['headimgurl'])) {
            $user->setHeadimgurl($userData['headimgurl']);
        }
        
        if (isset($userData['privilege'])) {
            $user->setPrivilege($userData['privilege']);
        }

        $user->setRawData($userData);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }

    public function findExpiredTokenUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.accessTokenExpiresTime < :now')
            ->andWhere('u.refreshToken IS NOT NULL')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    public function cleanupExpiredData(int $days = 30): int
    {
        $qb = $this->createQueryBuilder('u');
        
        return $qb->delete()
            ->where('u.accessTokenExpiresTime < :expiredDate')
            ->andWhere('u.updateTime < :oldDate')
            ->setParameter('expiredDate', new \DateTime('-' . $days . ' days'))
            ->setParameter('oldDate', new \DateTime('-' . $days . ' days'))
            ->getQuery()
            ->execute();
    }
}