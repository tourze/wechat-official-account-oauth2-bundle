<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\OAuth2AuthorizationCodeRepository;

class OAuth2AuthorizationCodeRepositoryTest extends TestCase
{
    private OAuth2AuthorizationCodeRepository $repository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $classMetadata = $this->createMock(ClassMetadata::class);
        
        $this->entityManager->expects($this->any())
            ->method('getClassMetadata')
            ->willReturn($classMetadata);
            
        $this->repository = new OAuth2AuthorizationCodeRepository($this->entityManager);
    }

    public function testFindByCode(): void
    {
        // Test finding authorization code
        $this->assertNull($this->repository->findByCode('non_existent_code'));
    }

    public function testCleanupExpiredCodes(): void
    {
        // Test cleanup of expired codes
        $this->assertEquals(0, $this->repository->cleanupExpiredCodes());
    }
}