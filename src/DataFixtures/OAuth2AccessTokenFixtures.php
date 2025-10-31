<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AccessToken;
use WechatOfficialAccountBundle\DataFixtures\AccountFixtures;
use WechatOfficialAccountBundle\Entity\Account;

#[When(env: 'test')]
#[When(env: 'dev')]
class OAuth2AccessTokenFixtures extends Fixture implements DependentFixtureInterface
{
    public const TOKEN_VALID_REFERENCE = 'token-valid';
    public const TOKEN_EXPIRED_REFERENCE = 'token-expired';
    public const TOKEN_REVOKED_REFERENCE = 'token-revoked';

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

        $validToken = new OAuth2AccessToken();
        $validToken->setAccessToken('valid_access_token_12345');
        $validToken->setRefreshToken('valid_refresh_token_12345');
        $validToken->setOpenId('test_openid_12345');
        $validToken->setScopes('snsapi_userinfo');
        $validToken->setAccessTokenExpiresAt(new \DateTimeImmutable('+2 hours'));
        $validToken->setWechatAccount($account);
        $manager->persist($validToken);
        $this->addReference(self::TOKEN_VALID_REFERENCE, $validToken);

        $expiredToken = new OAuth2AccessToken();
        $expiredToken->setAccessToken('expired_access_token_12345');
        $expiredToken->setRefreshToken('expired_refresh_token_12345');
        $expiredToken->setOpenId('test_openid_expired');
        $expiredToken->setScopes('snsapi_base');
        $expiredToken->setAccessTokenExpiresAt(new \DateTimeImmutable('-1 day'));
        $expiredToken->setWechatAccount($account);
        $manager->persist($expiredToken);
        $this->addReference(self::TOKEN_EXPIRED_REFERENCE, $expiredToken);

        $revokedToken = new OAuth2AccessToken();
        $revokedToken->setAccessToken('revoked_access_token_12345');
        $revokedToken->setRefreshToken('revoked_refresh_token_12345');
        $revokedToken->setOpenId('test_openid_revoked');
        $revokedToken->setScopes('snsapi_userinfo');
        $revokedToken->setAccessTokenExpiresAt(new \DateTimeImmutable('+2 hours'));
        $revokedToken->setRevoked(true);
        $revokedToken->setWechatAccount($account);
        $manager->persist($revokedToken);
        $this->addReference(self::TOKEN_REVOKED_REFERENCE, $revokedToken);

        $manager->flush();
    }
}
