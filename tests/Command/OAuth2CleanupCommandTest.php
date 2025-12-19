<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Command\OAuth2CleanupCommand;

/**
 * @internal
 */
#[CoversClass(OAuth2CleanupCommand::class)]
#[RunTestsInSeparateProcesses]
final class OAuth2CleanupCommandTest extends AbstractCommandTestCase
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

    public function testExecuteWithDryRun(): void
    {
        $this->setUpCommand();

        $this->commandTester->execute(['--dry-run' => true]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('OAuth2 清理工具', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithoutDryRun(): void
    {
        $this->setUpCommand();

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('OAuth2 清理工具', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testOptionDryRun(): void
    {
        $this->setUpCommand();

        $this->commandTester->execute(['--dry-run' => true]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('这是预览模式', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testOptionBefore(): void
    {
        $this->setUpCommand();

        $this->commandTester->execute(['--before' => '-2 hours']);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('OAuth2 清理工具', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    private function setUpCommand(): void
    {
        $command = self::getContainer()->get(OAuth2CleanupCommand::class);
        $this->assertInstanceOf(OAuth2CleanupCommand::class, $command);

        $application = new Application();
        $application->addCommand($command);

        $command = $application->find('oauth2:cleanup');
        $this->commandTester = new CommandTester($command);
    }
}
