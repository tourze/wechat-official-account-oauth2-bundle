<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Integration\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Tourze\WechatOfficialAccountOAuth2Bundle\Command\OAuth2CleanupCommand;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\OAuth2AccessTokenRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\OAuth2AuthorizationCodeRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Integration\WechatOAuth2TestCase;

class OAuth2CleanupCommandTest extends WechatOAuth2TestCase
{
    private OAuth2CleanupCommand $command;
    private OAuth2AccessTokenRepository $accessTokenRepository;
    private OAuth2AuthorizationCodeRepository $authorizationCodeRepository;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        
        $this->accessTokenRepository = $this->createMock(OAuth2AccessTokenRepository::class);
        $this->authorizationCodeRepository = $this->createMock(OAuth2AuthorizationCodeRepository::class);
        
        $this->command = new OAuth2CleanupCommand(
            $this->accessTokenRepository,
            $this->authorizationCodeRepository
        );
    }

    public function testExecuteWithDryRun(): void
    {
        $this->authorizationCodeRepository->expects($this->once())
            ->method('findExpiredCodes')
            ->willReturn([]);
            
        $this->authorizationCodeRepository->expects($this->once())
            ->method('findUsedCodes')
            ->willReturn([]);
            
        $this->accessTokenRepository->expects($this->once())
            ->method('findExpiredTokens')
            ->willReturn([]);
            
        $this->accessTokenRepository->expects($this->once())
            ->method('findRevokedTokens')
            ->willReturn([]);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['--dry-run' => true]);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithoutDryRun(): void
    {
        $this->authorizationCodeRepository->expects($this->once())
            ->method('findExpiredCodes')
            ->willReturn([]);
            
        $this->authorizationCodeRepository->expects($this->once())
            ->method('findUsedCodes')
            ->willReturn([]);
            
        $this->authorizationCodeRepository->expects($this->once())
            ->method('deleteExpiredCodes')
            ->willReturn(0);
            
        $this->authorizationCodeRepository->expects($this->once())
            ->method('deleteUsedCodes')
            ->willReturn(0);
            
        $this->accessTokenRepository->expects($this->once())
            ->method('findExpiredTokens')
            ->willReturn([]);
            
        $this->accessTokenRepository->expects($this->once())
            ->method('findRevokedTokens')
            ->willReturn([]);
            
        $this->accessTokenRepository->expects($this->once())
            ->method('deleteExpiredTokens')
            ->willReturn(0);
            
        $this->accessTokenRepository->expects($this->once())
            ->method('deleteRevokedTokens')
            ->willReturn(0);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}