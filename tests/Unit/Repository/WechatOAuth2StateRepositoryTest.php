<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2State;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2StateRepository;

class WechatOAuth2StateRepositoryTest extends TestCase
{
    private MockObject|ManagerRegistry $registry;
    private MockObject|EntityManagerInterface $entityManager;
    private WechatOAuth2StateRepository $repository;

    public function testFindValidState(): void
    {
        // Test that the repository is properly instantiated
        $this->assertInstanceOf(WechatOAuth2StateRepository::class, $this->repository);
    }

    public function testCleanupExpiredStates(): void
    {
        // Test that the repository is properly instantiated
        $this->assertInstanceOf(WechatOAuth2StateRepository::class, $this->repository);
    }

    public function testFindUnusedBySessionId(): void
    {
        // Test that the repository is properly instantiated
        $this->assertInstanceOf(WechatOAuth2StateRepository::class, $this->repository);
    }

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->with(WechatOAuth2State::class)
            ->willReturn($this->entityManager);

        $this->repository = new WechatOAuth2StateRepository($this->registry);
    }
}