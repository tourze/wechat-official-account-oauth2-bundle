<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\WechatOAuth2ConfigurationException;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\OAuth2AccessTokenRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\OAuth2AuthorizationCodeRepository;

/**
 * OAuth2清理命令 - 清理过期的令牌和授权码
 */
#[AsCommand(
    name: self::NAME,
    description: '清理过期的OAuth2令牌和授权码',
)]
class OAuth2CleanupCommand extends Command
{
    public const NAME = 'oauth2:cleanup';
    public function __construct(
        private readonly OAuth2AccessTokenRepository $accessTokenRepository,
        private readonly OAuth2AuthorizationCodeRepository $authorizationCodeRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                '仅显示将要删除的记录数量，不实际执行删除'
            )
            ->addOption(
                'before',
                null,
                InputOption::VALUE_REQUIRED,
                '清理指定时间之前的记录 (格式: Y-m-d H:i:s 或相对时间如 "-1 week")',
                '-1 hour'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');
        $beforeOption = $input->getOption('before');
        $beforeDate = $this->parseDate(is_string($beforeOption) ? $beforeOption : '-1 hour');

        $io->title('OAuth2 清理工具');
        $io->text([
            '清理时间: ' . $beforeDate->format('Y-m-d H:i:s'),
            '模式: ' . ((bool) $dryRun ? '仅预览' : '实际执行'),
        ]);

        // 清理过期的授权码
        $expiredCodes = $this->authorizationCodeRepository->findExpiredCodes($beforeDate);
        $usedCodes = $this->authorizationCodeRepository->findUsedCodes();

        $io->section('授权码清理');
        $io->text([
            sprintf('过期的授权码: %d 个', count($expiredCodes)),
            sprintf('已使用的授权码: %d 个', count($usedCodes)),
        ]);

        if (!(bool) $dryRun) {
            $deletedExpiredCodes = $this->authorizationCodeRepository->deleteExpiredCodes($beforeDate);
            $deletedUsedCodes = $this->authorizationCodeRepository->deleteUsedCodes();
            
            $io->success([
                sprintf('已删除 %d 个过期的授权码', $deletedExpiredCodes),
                sprintf('已删除 %d 个已使用的授权码', $deletedUsedCodes),
            ]);
        }

        // 清理过期的访问令牌
        $expiredTokens = $this->accessTokenRepository->findExpiredTokens($beforeDate);
        $revokedTokens = $this->accessTokenRepository->findRevokedTokens();

        $io->section('访问令牌清理');
        $io->text([
            sprintf('过期的访问令牌: %d 个', count($expiredTokens)),
            sprintf('已撤销的访问令牌: %d 个', count($revokedTokens)),
        ]);

        if (!(bool) $dryRun) {
            $deletedExpiredTokens = $this->accessTokenRepository->deleteExpiredTokens($beforeDate);
            $deletedRevokedTokens = $this->accessTokenRepository->deleteRevokedTokens();
            
            $io->success([
                sprintf('已删除 %d 个过期的访问令牌', $deletedExpiredTokens),
                sprintf('已删除 %d 个已撤销的访问令牌', $deletedRevokedTokens),
            ]);
        }

        if ((bool) $dryRun) {
            $io->note('这是预览模式，没有实际执行删除操作。使用 --no-dry-run 选项来实际执行清理。');
        } else {
            $io->success('OAuth2 清理完成！');
        }

        return Command::SUCCESS;
    }

    private function parseDate(string $dateString): \DateTime
    {
        try {
            // 尝试解析为绝对时间
            return new \DateTime($dateString);
        } catch (\Exception $e) {
            // 解析为相对时间
            try {
                return new \DateTime($dateString);
            } catch (\Exception $e) {
                throw new WechatOAuth2ConfigurationException(
                    sprintf('无效的时间格式: %s. 请使用 Y-m-d H:i:s 格式或相对时间（如 "-1 week"）', $dateString)
                );
            }
        }
    }
}