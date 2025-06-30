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
    name: self::NAME,
    description: 'Configure Wechat OAuth2 settings'
)]
class OAuth2ConfigureCommand extends Command
{
    public const NAME = 'wechat:oauth2:configure';
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
        $accountIdArg = $input->getArgument('account-id');
        $accountId = is_scalar($accountIdArg) ? (string) $accountIdArg : '';

        $account = $this->accountRepository->find($accountId);
        if ($account === null) {
            $io->error(sprintf('Account with ID "%s" not found.', $accountId));
            return Command::FAILURE;
        }

        $config = $this->configRepository->findOneBy(['account' => $account]);
        
        if ($config === null) {
            $config = new WechatOAuth2Config();
            $config->setAccount($account);
            $io->note('Creating new configuration...');
        } else {
            $io->note('Updating existing configuration...');
        }

        // Update configuration
        $scope = $input->getOption('scope');
        if (is_string($scope) || $scope === null) {
            $config->setScope($scope);
        }
        
        if ((bool) $input->getOption('disable')) {
            $config->setValid(false);
        } else {
            $config->setValid(true);
        }
        
        $remark = $input->getOption('remark');
        if (is_string($remark) || $remark === null) {
            $config->setRemark($remark);
        }

        $this->entityManager->persist($config);
        $this->entityManager->flush();

        // Set as default if requested
        if ((bool) $input->getOption('default')) {
            $this->configRepository->setDefault($config);
            $io->success('Configuration set as default.');
        }

        $io->success(sprintf(
            'OAuth2 configuration for account "%s" has been %s.',
            $account->getName() !== null ? $account->getName() : $account->getAppId(),
            $config->getId() !== null ? 'updated' : 'created'
        ));

        // Display configuration details
        $io->table(
            ['Property', 'Value'],
            [
                ['Account', $account->getName() !== null ? $account->getName() : $account->getAppId()],
                ['App ID', $account->getAppId()],
                ['Scope', $config->getScope()],
                ['Enabled', $config->isValid() ? 'Yes' : 'No'],
                ['Default', $config->isDefault() ? 'Yes' : 'No'],
                ['Remark', $config->getRemark() !== null ? $config->getRemark() : '-'],
            ]
        );

        return Command::SUCCESS;
    }
}