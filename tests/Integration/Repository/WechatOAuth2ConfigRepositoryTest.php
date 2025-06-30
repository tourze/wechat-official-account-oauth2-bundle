<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Integration\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2ConfigRepository;

class WechatOAuth2ConfigRepositoryTest extends TestCase
{
    private WechatOAuth2ConfigRepository $repository;
    private ManagerRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->repository = new WechatOAuth2ConfigRepository($this->registry);
    }

    public function testRepositoryIntegration(): void
    {
        // Test repository integration
        $this->assertInstanceOf(WechatOAuth2ConfigRepository::class, $this->repository);
    }
}