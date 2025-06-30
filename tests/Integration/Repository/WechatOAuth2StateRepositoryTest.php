<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Integration\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2State;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2StateRepository;

class WechatOAuth2StateRepositoryTest extends TestCase
{
    private WechatOAuth2StateRepository $repository;
    private ManagerRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->repository = new WechatOAuth2StateRepository($this->registry);
    }

    public function testRepositoryIntegration(): void
    {
        // Test repository integration
        $this->assertInstanceOf(WechatOAuth2StateRepository::class, $this->repository);
    }
}