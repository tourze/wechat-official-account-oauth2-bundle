<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Integration\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\WechatOfficialAccountOAuth2Bundle\Command\OAuth2ConfigureCommand;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2ConfigRepository;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Repository\AccountRepository;

class OAuth2ConfigureCommandTest extends KernelTestCase
{
    private OAuth2ConfigureCommand $command;
    private EntityManagerInterface $entityManager;
    private WechatOAuth2ConfigRepository $configRepository;
    private AccountRepository $accountRepository;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->configRepository = $this->createMock(WechatOAuth2ConfigRepository::class);
        $this->accountRepository = $this->createMock(AccountRepository::class);
        
        $this->command = new OAuth2ConfigureCommand(
            $this->entityManager,
            $this->configRepository,
            $this->accountRepository
        );
    }

    public function testExecuteWithValidAccount(): void
    {
        $account = $this->createMock(Account::class);
        $account->method('getName')->willReturn('Test Account');
        $account->method('getAppId')->willReturn('test_app_id');
        
        $config = $this->createMock(WechatOAuth2Config::class);
        $config->method('getId')->willReturn(1);
        $config->method('getScope')->willReturn('snsapi_base');
        $config->method('isValid')->willReturn(true);
        $config->method('isDefault')->willReturn(false);
        $config->method('getRemark')->willReturn(null);
        
        $this->accountRepository->expects($this->once())
            ->method('find')
            ->willReturn($account);
            
        $this->configRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn($config);
            
        $this->entityManager->expects($this->once())
            ->method('persist');
            
        $this->entityManager->expects($this->once())
            ->method('flush');

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'account-id' => 'test_account_id',
            '--scope' => 'snsapi_userinfo'
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithInvalidAccount(): void
    {
        $this->accountRepository->expects($this->once())
            ->method('find')
            ->willReturn(null);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['account-id' => 'invalid_account_id']);

        $this->assertEquals(1, $commandTester->getStatusCode());
    }
}