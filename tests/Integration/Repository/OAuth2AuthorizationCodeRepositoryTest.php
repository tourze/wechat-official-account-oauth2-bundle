<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Integration\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AuthorizationCode;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\OAuth2AuthorizationCodeRepository;

class OAuth2AuthorizationCodeRepositoryTest extends TestCase
{
    private OAuth2AuthorizationCodeRepository $repository;
    private ManagerRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->repository = new OAuth2AuthorizationCodeRepository($this->registry);
    }

    public function testRepositoryIntegration(): void
    {
        // Test repository integration
        $this->assertInstanceOf(OAuth2AuthorizationCodeRepository::class, $this->repository);
    }
}