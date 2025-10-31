<?php

declare(strict_types=1);

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
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Repository\AccountRepository;

/**
 * 配置微信OAuth2
 */
#[AsCommand(name: self::NAME, description: 'Configure Wechat OAuth2 settings', help: <<<'TXT'
    This command creates or updates Wechat OAuth2 configuration for a specific account.
    TXT)]
class OAuth2ConfigureCommand extends Command
{
    public const NAME = 'wechat:oauth2:configure';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WechatOAuth2ConfigRepository $configRepository,
        private readonly AccountRepository $accountRepository,
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $account = $this->getAccount($input, $io);
        if (null === $account) {
            return Command::FAILURE;
        }

        $config = $this->getOrCreateConfig($account, $io);
        $this->updateConfigFromInput($config, $input);

        $this->entityManager->persist($config);
        $this->entityManager->flush();

        $this->handleDefaultOption($config, $input, $io);
        $this->displayResults($config, $account, $io);

        return Command::SUCCESS;
    }

    private function getAccount(InputInterface $input, SymfonyStyle $io): ?Account
    {
        $accountIdArg = $input->getArgument('account-id');
        $accountId = is_scalar($accountIdArg) ? (string) $accountIdArg : '';

        $account = $this->accountRepository->find($accountId);
        if (null === $account) {
            $io->error(sprintf('Account with ID "%s" not found.', $accountId));
        }

        return $account;
    }

    private function getOrCreateConfig(Account $account, SymfonyStyle $io): WechatOAuth2Config
    {
        $config = $this->configRepository->findOneBy(['account' => $account]);

        if (null === $config) {
            $config = new WechatOAuth2Config();
            $config->setAccount($account);
            $io->note('Creating new configuration...');
        } else {
            $io->note('Updating existing configuration...');
        }

        return $config;
    }

    private function updateConfigFromInput(WechatOAuth2Config $config, InputInterface $input): void
    {
        $scope = $input->getOption('scope');
        if (is_string($scope) || null === $scope) {
            $config->setScope($scope);
        }

        $config->setValid(!(bool) $input->getOption('disable'));

        $remark = $input->getOption('remark');
        if (is_string($remark) || null === $remark) {
            $config->setRemark($remark);
        }
    }

    private function handleDefaultOption(WechatOAuth2Config $config, InputInterface $input, SymfonyStyle $io): void
    {
        if ((bool) $input->getOption('default')) {
            $this->configRepository->setDefault($config);
            $io->success('Configuration set as default.');
        }
    }

    private function displayResults(WechatOAuth2Config $config, Account $account, SymfonyStyle $io): void
    {
        $io->success(sprintf(
            'OAuth2 configuration for account "%s" has been %s.',
            $account->getName() ?? $account->getAppId(),
            null !== $config->getId() ? 'updated' : 'created'
        ));

        $io->table(
            ['Property', 'Value'],
            [
                ['Account', $account->getName() ?? $account->getAppId()],
                ['App ID', $account->getAppId()],
                ['Scope', $config->getScope()],
                ['Enabled', $config->isValid() ? 'Yes' : 'No'],
                ['Default', $config->isDefault() ? 'Yes' : 'No'],
                ['Remark', $config->getRemark() ?? '-'],
            ]
        );
    }
}
