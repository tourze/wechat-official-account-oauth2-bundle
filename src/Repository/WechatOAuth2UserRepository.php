<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\WechatOfficialAccountContracts\OfficialAccountInterface;
use Tourze\WechatOfficialAccountContracts\UserInterface;
use Tourze\WechatOfficialAccountContracts\UserLoaderInterface;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2User;

/**
 * @extends ServiceEntityRepository<WechatOAuth2User>
 */
#[AsRepository(entityClass: WechatOAuth2User::class)]
class WechatOAuth2UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WechatOAuth2User::class);
    }

    public function loadUserByOpenId(string $openId): ?WechatOAuth2User
    {
        return $this->findOneBy(['openid' => $openId]);
    }

    public function loadUserByUnionId(string $unionId): ?WechatOAuth2User
    {
        return $this->findOneBy(['unionid' => $unionId]);
    }

    public function syncUserByOpenId(OfficialAccountInterface $officialAccount, string $openId): ?UserInterface
    {
        // TODO: Implement syncUserByOpenId() method.
        throw new \RuntimeException('暂时没实现');
    }

    /**
     * @param array<string, mixed> $userData
     */
    public function updateOrCreate(array $userData, WechatOAuth2Config $config): WechatOAuth2User
    {
        $user = $this->findOneBy([
            'openid' => $userData['openid'],
            'config' => $config,
        ]);

        if (null === $user) {
            $user = $this->createNewUser($userData, $config);
        }

        $this->updateUserTokenData($user, $userData);
        $this->updateUserProfileData($user, $userData);
        $user->setRawData($userData);

        return $user;
    }

    /**
     * @param array<string, mixed> $userData
     */
    private function createNewUser(array $userData, WechatOAuth2Config $config): WechatOAuth2User
    {
        $user = new WechatOAuth2User();
        $user->setConfig($config);
        $openid = $userData['openid'];
        assert(is_string($openid));
        $user->setOpenId($openid);

        return $user;
    }

    /**
     * @param array<string, mixed> $userData
     */
    private function updateUserTokenData(WechatOAuth2User $user, array $userData): void
    {
        if (isset($userData['access_token'])) {
            $accessToken = $userData['access_token'];
            assert(is_string($accessToken));
            $user->setAccessToken($accessToken);
        }

        if (isset($userData['refresh_token'])) {
            $refreshToken = $userData['refresh_token'];
            assert(is_string($refreshToken));
            $user->setRefreshToken($refreshToken);
        }

        if (isset($userData['expires_in'])) {
            $expiresIn = $userData['expires_in'];
            assert(is_int($expiresIn) || is_string($expiresIn));
            $user->setExpiresIn((int) $expiresIn);
        }

        if (array_key_exists('scope', $userData)) {
            $scope = $userData['scope'];
            if (is_string($scope) || null === $scope) {
                $user->setScope($scope);
            }
        }

        if (array_key_exists('unionid', $userData)) {
            $unionid = $userData['unionid'];
            if (is_string($unionid) || null === $unionid) {
                $user->setUnionId($unionid);
            }
        }
    }

    /**
     * @param array<string, mixed> $userData
     */
    private function updateUserProfileData(WechatOAuth2User $user, array $userData): void
    {
        $this->updateNickname($user, $userData);
        $this->updateSex($user, $userData);
        $this->updateProvince($user, $userData);
        $this->updateCity($user, $userData);
        $this->updateCountry($user, $userData);
        $this->updateHeadimgurl($user, $userData);
        $this->updatePrivilege($user, $userData);
    }

    /**
     * @param array<string, mixed> $userData
     */
    private function updateNickname(WechatOAuth2User $user, array $userData): void
    {
        if (!array_key_exists('nickname', $userData)) {
            return;
        }

        $value = $userData['nickname'];
        if (is_string($value) || null === $value) {
            $user->setNickname($value);
        }
    }

    /**
     * @param array<string, mixed> $userData
     */
    private function updateSex(WechatOAuth2User $user, array $userData): void
    {
        if (!array_key_exists('sex', $userData)) {
            return;
        }

        $value = $userData['sex'];
        assert(is_int($value) || is_string($value));
        $user->setSex((int) $value);
    }

    /**
     * @param array<string, mixed> $userData
     */
    private function updateProvince(WechatOAuth2User $user, array $userData): void
    {
        if (!array_key_exists('province', $userData)) {
            return;
        }

        $value = $userData['province'];
        if (is_string($value) || null === $value) {
            $user->setProvince($value);
        }
    }

    /**
     * @param array<string, mixed> $userData
     */
    private function updateCity(WechatOAuth2User $user, array $userData): void
    {
        if (!array_key_exists('city', $userData)) {
            return;
        }

        $value = $userData['city'];
        if (is_string($value) || null === $value) {
            $user->setCity($value);
        }
    }

    /**
     * @param array<string, mixed> $userData
     */
    private function updateCountry(WechatOAuth2User $user, array $userData): void
    {
        if (!array_key_exists('country', $userData)) {
            return;
        }

        $value = $userData['country'];
        if (is_string($value) || null === $value) {
            $user->setCountry($value);
        }
    }

    /**
     * @param array<string, mixed> $userData
     */
    private function updateHeadimgurl(WechatOAuth2User $user, array $userData): void
    {
        if (!array_key_exists('headimgurl', $userData)) {
            return;
        }

        $value = $userData['headimgurl'];
        if (is_string($value) || null === $value) {
            $user->setHeadimgurl($value);
        }
    }

    /**
     * @param array<string, mixed> $userData
     */
    private function updatePrivilege(WechatOAuth2User $user, array $userData): void
    {
        if (!array_key_exists('privilege', $userData)) {
            return;
        }

        $value = $userData['privilege'];
        if (is_array($value)) {
            /** @var array<string> $privilege */
            $privilege = $value;
            $user->setPrivilege($privilege);
        } elseif (null === $value) {
            $user->setPrivilege(null);
        }
    }

    /**
     * @return array<WechatOAuth2User>
     */
    public function findExpiredTokenUsers(): array
    {
        $result = $this->createQueryBuilder('u')
            ->where('u.accessTokenExpiresTime < :now')
            ->andWhere('u.refreshToken IS NOT NULL')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult()
        ;
        assert(is_array($result));
        /** @var array<WechatOAuth2User> $result */

        return $result;
    }

    public function cleanupExpiredData(int $days = 30): int
    {
        $qb = $this->createQueryBuilder('u');

        $result = $qb->delete()
            ->where('u.accessTokenExpiresTime < :expiredDate')
            ->andWhere('u.updateTime < :oldDate')
            ->setParameter('expiredDate', new \DateTime('-' . $days . ' days'))
            ->setParameter('oldDate', new \DateTime('-' . $days . ' days'))
            ->getQuery()
            ->execute()
        ;
        assert(is_int($result));

        return $result;
    }

    public function save(WechatOAuth2User $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WechatOAuth2User $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
