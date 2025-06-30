<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Integration\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\WechatOfficialAccountOAuth2Bundle\Command\OAuth2CreateApplicationCommand;
use WechatOfficialAccountBundle\Repository\AccountRepository;

class OAuth2CreateApplicationCommandTest extends KernelTestCase
{
    private OAuth2CreateApplicationCommand $command;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $accountRepository = $this->createMock(AccountRepository::class);
        
        $this->command = new OAuth2CreateApplicationCommand($entityManager, $accountRepository);
    }

    public function testExecute(): void
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        $this->assertEquals(0, $commandTester->getStatusCode());
        $this->assertStringContainsString('OAuth2 application creation', $commandTester->getDisplay());
    }
}