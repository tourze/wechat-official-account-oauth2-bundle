<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\WechatOAuth2Service;

/**
 * 刷新过期的OAuth2令牌
 */
#[AsCommand(name: self::NAME, description: 'Refresh expired Wechat OAuth2 tokens', help: <<<'TXT'
    This command refreshes expired Wechat OAuth2 access tokens using their refresh tokens.
    TXT)]
class OAuth2RefreshTokenCommand extends Command
{
    public const NAME = 'wechat:oauth2:refresh-tokens';

    public function __construct(
        private readonly WechatOAuth2Service $oauth2Service,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only show what would be refreshed')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $isDryRun = $input->getOption('dry-run');

        $io->title('Refreshing Expired Wechat OAuth2 Tokens');

        if ((bool) $isDryRun) {
            $io->note('Running in dry-run mode. No tokens will be refreshed.');
        }

        try {
            if ((bool) $isDryRun) {
                $io->warning('Dry-run mode is not fully implemented. Skipping actual refresh.');
                $refreshed = 0;
            } else {
                $refreshed = $this->oauth2Service->refreshExpiredTokens();
            }

            $io->success(sprintf('Successfully refreshed %d tokens.', $refreshed));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Error refreshing tokens: %s', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}
