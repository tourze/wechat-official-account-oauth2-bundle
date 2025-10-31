<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Command\OAuth2CreateApplicationCommand;

/**
 * @internal
 */
#[CoversClass(OAuth2CreateApplicationCommand::class)]
#[RunTestsInSeparateProcesses]
final class OAuth2CreateApplicationCommandTest extends AbstractCommandTestCase
{
    private OAuth2CreateApplicationCommand $command;

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
            'wechat-account-id' => '99999',
            '--redirect-uri' => ['https://example.com/callback'],
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('微信公众号账号 ID "99999" 不存在', $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    public function testArgumentWechatAccountId(): void
    {
        $this->commandTester->execute([
            'wechat-account-id' => '99999',
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('微信公众号账号 ID "99999" 不存在', $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    public function testOptionRedirectUri(): void
    {
        $this->commandTester->execute([
            'wechat-account-id' => '99999',
            '--redirect-uri' => ['https://example.com/callback'],
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('微信公众号账号 ID "99999" 不存在', $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    public function testOptionScope(): void
    {
        $this->commandTester->execute([
            'wechat-account-id' => '99999',
            '--scope' => 'snsapi_userinfo',
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('微信公众号账号 ID "99999" 不存在', $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    protected function onSetUp(): void
    {
        $command = self::getContainer()->get(OAuth2CreateApplicationCommand::class);
        $this->assertInstanceOf(OAuth2CreateApplicationCommand::class, $command);
        $this->command = $command;

        $application = new Application();
        $application->add($this->command);

        $command = $application->find('oauth2:create-application');
        $this->commandTester = new CommandTester($command);
    }
}
