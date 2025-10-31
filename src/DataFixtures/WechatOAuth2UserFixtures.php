<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2User;

#[When(env: 'test')]
#[When(env: 'dev')]
class WechatOAuth2UserFixtures extends Fixture implements DependentFixtureInterface
{
    public const USER_VALID_REFERENCE = 'user-valid';
    public const USER_EXPIRED_TOKEN_REFERENCE = 'user-expired-token';

    public function load(ObjectManager $manager): void
    {
        $config = $this->getReference(WechatOAuth2ConfigFixtures::CONFIG_DEFAULT_REFERENCE, WechatOAuth2Config::class);

        $validUser = new WechatOAuth2User();
        $validUser->setConfig($config);
        $validUser->setOpenId('test_openid_12345');
        $validUser->setUnionId('test_unionid_12345');
        $validUser->setNickname('测试用户');
        $validUser->setSex(1);
        $validUser->setProvince('北京');
        $validUser->setCity('北京市');
        $validUser->setCountry('中国');
        $validUser->setHeadimgurl('https://test.example.local/avatar.jpg');
        $validUser->setPrivilege(['test_privilege']);
        $validUser->setAccessToken('valid_access_token_12345');
        $validUser->setRefreshToken('valid_refresh_token_12345');
        $validUser->setExpiresIn(7200);
        $validUser->setScope('snsapi_userinfo');
        $manager->persist($validUser);
        $this->addReference(self::USER_VALID_REFERENCE, $validUser);

        $expiredTokenUser = new WechatOAuth2User();
        $expiredTokenUser->setConfig($config);
        $expiredTokenUser->setOpenId('test_openid_expired');
        $expiredTokenUser->setNickname('过期Token用户');
        $expiredTokenUser->setSex(2);
        $expiredTokenUser->setAccessToken('expired_access_token_12345');
        $expiredTokenUser->setRefreshToken('expired_refresh_token_12345');
        $expiredTokenUser->setExpiresIn(7200);
        $expiredTokenUser->setScope('snsapi_base');
        $manager->persist($expiredTokenUser);
        $this->addReference(self::USER_EXPIRED_TOKEN_REFERENCE, $expiredTokenUser);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            WechatOAuth2ConfigFixtures::class,
        ];
    }
}
