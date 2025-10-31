<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AuthorizationCode;
use WechatOfficialAccountBundle\DataFixtures\AccountFixtures;
use WechatOfficialAccountBundle\Entity\Account;

#[When(env: 'test')]
#[When(env: 'dev')]
class OAuth2AuthorizationCodeFixtures extends Fixture implements DependentFixtureInterface
{
    public const CODE_VALID_REFERENCE = 'code-valid';
    public const CODE_EXPIRED_REFERENCE = 'code-expired';
    public const CODE_USED_REFERENCE = 'code-used';

    public function getDependencies(): array
    {
        return [
            AccountFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $account = $this->getReference(AccountFixtures::ACCOUNT_REFERENCE, Account::class);
        assert($account instanceof Account);

        $validCode = new OAuth2AuthorizationCode();
        $validCode->setCode('valid_auth_code_12345');
        $validCode->setOpenId('test_openid_12345');
        $validCode->setRedirectUri('https://test.example.local/oauth2/callback');
        $validCode->setState('valid_state_12345');
        $validCode->setScopes('snsapi_userinfo');
        $validCode->setExpiresAt(new \DateTimeImmutable('+10 minutes'));
        $validCode->setUsed(false);
        $validCode->setWechatAccount($account);
        $manager->persist($validCode);
        $this->addReference(self::CODE_VALID_REFERENCE, $validCode);

        $expiredCode = new OAuth2AuthorizationCode();
        $expiredCode->setCode('expired_auth_code_12345');
        $expiredCode->setOpenId('test_openid_expired');
        $expiredCode->setRedirectUri('https://test.example.local/oauth2/callback');
        $expiredCode->setState('expired_state_12345');
        $expiredCode->setScopes('snsapi_base');
        $expiredCode->setExpiresAt(new \DateTimeImmutable('-10 minutes'));
        $expiredCode->setUsed(false);
        $expiredCode->setWechatAccount($account);
        $manager->persist($expiredCode);
        $this->addReference(self::CODE_EXPIRED_REFERENCE, $expiredCode);

        $usedCode = new OAuth2AuthorizationCode();
        $usedCode->setCode('used_auth_code_12345');
        $usedCode->setOpenId('test_openid_used');
        $usedCode->setRedirectUri('https://test.example.local/oauth2/callback');
        $usedCode->setState('used_state_12345');
        $usedCode->setScopes('snsapi_userinfo');
        $usedCode->setExpiresAt(new \DateTimeImmutable('+10 minutes'));
        $usedCode->setUsed(true);
        $usedCode->setWechatAccount($account);
        $manager->persist($usedCode);
        $this->addReference(self::CODE_USED_REFERENCE, $usedCode);

        $manager->flush();
    }
}
