<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2ConfigRepository;
use WechatOfficialAccountBundle\Repository\AccountRepository;

/**
 * 配置微信OAuth2
 */
#[AsCommand(
    name: 'wechat:oauth2:configure',
    description: 'Configure Wechat OAuth2 settings'
)]
class OAuth2ConfigureCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WechatOAuth2ConfigRepository $configRepository,
        private readonly AccountRepository $accountRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('account-id', InputArgument::REQUIRED, 'The Wechat account ID')
            ->addOption('scope', null, InputOption::VALUE_REQUIRED, 'OAuth2 scope', 'snsapi_base')
            ->addOption('default', null, InputOption::VALUE_NONE, 'Set as default configuration')
            ->addOption('disable', null, InputOption::VALUE_NONE, 'Disable the configuration')
            ->addOption('remark', null, InputOption::VALUE_REQUIRED, 'Configuration remark')
            ->setHelp('This command creates or updates Wechat OAuth2 configuration for a specific account.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $accountId = $input->getArgument('account-id');

        $account = $this->accountRepository->find($accountId);
        if (!$account) {
            $io->error(sprintf('Account with ID "%s" not found.', $accountId));
            return Command::FAILURE;
        }

        $config = $this->configRepository->findOneBy(['account' => $account]);
        
        if (!$config) {
            $config = new WechatOAuth2Config();
            $config->setAccount($account);
            $io->note('Creating new configuration...');
        } else {
            $io->note('Updating existing configuration...');
        }

        // Update configuration
        $config->setScope($input->getOption('scope'));
        
        if ($input->getOption('disable')) {
            $config->setIsEnabled(false);
        } else {
            $config->setIsEnabled(true);
        }
        
        if ($input->getOption('remark')) {
            $config->setRemark($input->getOption('remark'));
        }

        $this->entityManager->persist($config);
        $this->entityManager->flush();

        // Set as default if requested
        if ($input->getOption('default')) {
            $this->configRepository->setDefault($config);
            $io->success('Configuration set as default.');
        }

        $io->success(sprintf(
            'OAuth2 configuration for account "%s" has been %s.',
            $account->getName() ?: $account->getAppId(),
            $config->getId() ? 'updated' : 'created'
        ));

        // Display configuration details
        $io->table(
            ['Property', 'Value'],
            [
                ['Account', $account->getName() ?: $account->getAppId()],
                ['App ID', $account->getAppId()],
                ['Scope', $config->getScope()],
                ['Enabled', $config->isEnabled() ? 'Yes' : 'No'],
                ['Default', $config->isDefault() ? 'Yes' : 'No'],
                ['Remark', $config->getRemark() ?: '-'],
            ]
        );

        return Command::SUCCESS;
    }
}