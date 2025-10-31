<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2State;

#[When(env: 'test')]
#[When(env: 'dev')]
class WechatOAuth2StateFixtures extends Fixture implements DependentFixtureInterface
{
    public const STATE_VALID_REFERENCE = 'state-valid';
    public const STATE_EXPIRED_REFERENCE = 'state-expired';
    public const STATE_USED_REFERENCE = 'state-used';

    public function getDependencies(): array
    {
        return [
            WechatOAuth2ConfigFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $config = $this->getReference(WechatOAuth2ConfigFixtures::CONFIG_DEFAULT_REFERENCE, WechatOAuth2Config::class);

        $validState = new WechatOAuth2State();
        $validState->setState('valid_state_12345');
        $validState->setConfig($config);
        $validState->setSessionId('test_session_id_12345');
        $manager->persist($validState);
        $this->addReference(self::STATE_VALID_REFERENCE, $validState);

        $expiredState = new WechatOAuth2State();
        $expiredState->setState('expired_state_12345');
        $expiredState->setConfig($config);
        $expiredState->setSessionId('test_session_id_expired');
        $manager->persist($expiredState);
        $this->addReference(self::STATE_EXPIRED_REFERENCE, $expiredState);

        $usedState = new WechatOAuth2State();
        $usedState->setState('used_state_12345');
        $usedState->setConfig($config);
        $usedState->setSessionId('test_session_id_used');
        $manager->persist($usedState);
        $this->addReference(self::STATE_USED_REFERENCE, $usedState);

        $manager->flush();
    }
}
