<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2UserRepository;

class WechatOAuth2UserRepositoryTest extends TestCase
{
    private WechatOAuth2UserRepository $repository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $classMetadata = $this->createMock(ClassMetadata::class);
        
        $this->entityManager->expects($this->any())
            ->method('getClassMetadata')
            ->willReturn($classMetadata);
            
        $this->repository = new WechatOAuth2UserRepository($this->entityManager);
    }

    public function testFindByOpenid(): void
    {
        // Test finding user by openid
        $this->assertNull($this->repository->findByOpenid('non_existent_openid'));
    }

    public function testFindByUnionid(): void
    {
        // Test finding user by unionid
        $this->assertNull($this->repository->findByUnionid('non_existent_unionid'));
    }
}