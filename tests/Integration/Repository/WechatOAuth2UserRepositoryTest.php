<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Integration\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2User;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2UserRepository;

class WechatOAuth2UserRepositoryTest extends TestCase
{
    private WechatOAuth2UserRepository $repository;
    private ManagerRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->repository = new WechatOAuth2UserRepository($this->registry);
    }

    public function testRepositoryIntegration(): void
    {
        // Test repository integration
        $this->assertInstanceOf(WechatOAuth2UserRepository::class, $this->repository);
    }
}