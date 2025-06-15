<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\WechatOfficialAccountOAuth2Bundle\Command\OAuth2RefreshTokenCommand;
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\WechatOAuth2Service;

class OAuth2RefreshTokenCommandTest extends TestCase
{
    private MockObject|WechatOAuth2Service $oauth2Service;
    private OAuth2RefreshTokenCommand $command;
    private CommandTester $commandTester;

    public function testExecuteSuccess(): void
    {
        $this->oauth2Service->expects($this->once())
            ->method('refreshExpiredTokens')
            ->willReturn(5);

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Refreshing Expired Wechat OAuth2 Tokens', $output);
        $this->assertStringContainsString('Successfully refreshed 5 tokens.', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithDryRun(): void
    {
        $this->oauth2Service->expects($this->never())
            ->method('refreshExpiredTokens');

        $this->commandTester->execute(['--dry-run' => true]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Running in dry-run mode', $output);
        $this->assertStringContainsString('Dry-run mode is not fully implemented', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithError(): void
    {
        $this->oauth2Service->expects($this->once())
            ->method('refreshExpiredTokens')
            ->willThrowException(new \Exception('API connection failed'));

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Error refreshing tokens: API connection failed', $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    protected function setUp(): void
    {
        $this->oauth2Service = $this->createMock(WechatOAuth2Service::class);
        $this->command = new OAuth2RefreshTokenCommand($this->oauth2Service);

        $application = new Application();
        $application->add($this->command);

        $command = $application->find('wechat:oauth2:refresh-tokens');
        $this->commandTester = new CommandTester($command);
    }
}