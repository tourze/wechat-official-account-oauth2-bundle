<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Integration\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\WechatOfficialAccountOAuth2Bundle\Command\OAuth2RefreshTokenCommand;
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\WechatOAuth2Service;

class OAuth2RefreshTokenCommandTest extends KernelTestCase
{
    private OAuth2RefreshTokenCommand $command;
    private WechatOAuth2Service $oauth2Service;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        
        $this->oauth2Service = $this->createMock(WechatOAuth2Service::class);
        
        $this->command = new OAuth2RefreshTokenCommand(
            $this->oauth2Service
        );
    }

    public function testExecute(): void
    {
        $this->oauth2Service->expects($this->once())
            ->method('refreshExpiredTokens')
            ->willReturn(0);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}