<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Integration\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AccessToken;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\OAuth2AccessTokenRepository;

class OAuth2AccessTokenRepositoryTest extends TestCase
{
    private OAuth2AccessTokenRepository $repository;
    private ManagerRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->repository = new OAuth2AccessTokenRepository($this->registry);
    }

    public function testRepositoryIntegration(): void
    {
        // Test repository integration
        $this->assertInstanceOf(OAuth2AccessTokenRepository::class, $this->repository);
    }
}