<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Command\OAuth2ConfigureCommand;

/**
 * @internal
 */
#[CoversClass(OAuth2ConfigureCommand::class)]
#[RunTestsInSeparateProcesses]
final class OAuth2ConfigureCommandTest extends AbstractCommandTestCase
{
    private OAuth2ConfigureCommand $command;

    private CommandTester $commandTester;

    protected function getCommandTester(): CommandTester
    {
        if (!isset($this->commandTester)) {
            $this->onSetUp();
        }

        return $this->commandTester;
    }

    public function testExecuteWithInvalidAccount(): void
    {
        $this->commandTester->execute([
            'account-id' => '99999',
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Account with ID "99999" not found', $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    public function testArgumentAccountId(): void
    {
        $this->commandTester->execute([
            'account-id' => '99999',
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Account with ID "99999" not found', $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    public function testOptionScope(): void
    {
        $this->commandTester->execute([
            'account-id' => '99999',
            '--scope' => 'snsapi_userinfo',
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Account with ID "99999" not found', $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    public function testOptionDefault(): void
    {
        $this->commandTester->execute([
            'account-id' => '99999',
            '--default' => true,
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Account with ID "99999" not found', $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    public function testOptionDisable(): void
    {
        $this->commandTester->execute([
            'account-id' => '99999',
            '--disable' => true,
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Account with ID "99999" not found', $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    public function testOptionRemark(): void
    {
        $this->commandTester->execute([
            'account-id' => '99999',
            '--remark' => 'Test remark',
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Account with ID "99999" not found', $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    protected function onSetUp(): void
    {
        $command = self::getContainer()->get(OAuth2ConfigureCommand::class);
        $this->assertInstanceOf(OAuth2ConfigureCommand::class, $command);
        $this->command = $command;

        $application = new Application();
        $application->addCommand($this->command);

        $command = $application->find('wechat:oauth2:configure');
        $this->commandTester = new CommandTester($command);
    }
}
