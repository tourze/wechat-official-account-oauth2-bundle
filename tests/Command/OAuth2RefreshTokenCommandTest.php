<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Command\OAuth2RefreshTokenCommand;

/**
 * @internal
 */
#[CoversClass(OAuth2RefreshTokenCommand::class)]
#[RunTestsInSeparateProcesses]
final class OAuth2RefreshTokenCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    protected function onSetUp(): void        // 此测试不需要数据库操作或额外初始化
    {
    }

    protected function getCommandTester(): CommandTester
    {
        if (!isset($this->commandTester)) {
            $this->setUpCommand();
        }

        return $this->commandTester;
    }

    public function testExecuteSuccess(): void
    {
        $this->setUpCommand();

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Refreshing Expired Wechat OAuth2 Tokens', $output);
        $this->assertStringContainsString('Successfully refreshed', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithDryRun(): void
    {
        $this->setUpCommand();

        $this->commandTester->execute(['--dry-run' => true]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Running in dry-run mode', $output);
        $this->assertStringContainsString('Dry-run mode is not fully implemented', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testOptionDryRun(): void
    {
        $this->setUpCommand();

        $this->commandTester->execute(['--dry-run' => true]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Running in dry-run mode', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    private function setUpCommand(): void
    {
        $command = self::getContainer()->get(OAuth2RefreshTokenCommand::class);
        $this->assertInstanceOf(OAuth2RefreshTokenCommand::class, $command);

        $application = new Application();
        $application->addCommand($command);

        $command = $application->find('wechat:oauth2:refresh-tokens');
        $this->commandTester = new CommandTester($command);
    }
}
