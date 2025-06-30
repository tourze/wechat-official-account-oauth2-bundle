<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\OAuth2AccessTokenRepository;

class OAuth2AccessTokenRepositoryTest extends TestCase
{
    private OAuth2AccessTokenRepository $repository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $classMetadata = $this->createMock(ClassMetadata::class);
        
        $this->entityManager->expects($this->any())
            ->method('getClassMetadata')
            ->willReturn($classMetadata);
            
        $this->repository = new OAuth2AccessTokenRepository($this->entityManager);
    }

    public function testFindByToken(): void
    {
        // Test finding access token by token string
        $this->assertNull($this->repository->findByToken('non_existent_token'));
    }

    public function testFindActiveTokens(): void
    {
        // Test finding active tokens
        $this->assertIsArray($this->repository->findActiveTokens());
    }
}