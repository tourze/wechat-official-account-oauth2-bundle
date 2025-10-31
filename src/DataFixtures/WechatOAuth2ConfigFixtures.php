<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;
use WechatOfficialAccountBundle\DataFixtures\AccountFixtures;
use WechatOfficialAccountBundle\Entity\Account;

#[When(env: 'test')]
#[When(env: 'dev')]
class WechatOAuth2ConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const CONFIG_DEFAULT_REFERENCE = 'config-default';
    public const CONFIG_SECONDARY_REFERENCE = 'config-secondary';

    public function load(ObjectManager $manager): void
    {
        $account = $this->getReference(AccountFixtures::ACCOUNT_REFERENCE, Account::class);

        // 由于WechatOAuth2Config有account_id的唯一约束，每个账户只能有一个OAuth2配置
        // 因此我们只创建一个默认配置
        $defaultConfig = new WechatOAuth2Config();
        $defaultConfig->setAccount($account);
        $defaultConfig->setScope('snsapi_userinfo');
        $defaultConfig->setIsDefault(true);
        $defaultConfig->setValid(true);
        $manager->persist($defaultConfig);
        $this->addReference(self::CONFIG_DEFAULT_REFERENCE, $defaultConfig);

        // 为了保持向后兼容性，让第二个引用也指向同一个配置
        $this->addReference(self::CONFIG_SECONDARY_REFERENCE, $defaultConfig);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AccountFixtures::class,
        ];
    }
}
