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
use WechatOfficialAccountBundle\Entity\Account;

/**
 * OAuth2应用创建命令
 */
#[AsCommand(
    name: 'oauth2:create-application',
    description: '创建OAuth2应用',
)]
class OAuth2CreateApplicationCommand extends Command
{
    public const NAME = 'oauth2:create-application';
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('wechat-account-id', InputArgument::REQUIRED, '微信公众号账号ID')
            ->addOption('redirect-uri', 'r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, '回调URI（可多个）')
            ->addOption('scope', 's', InputOption::VALUE_REQUIRED, '默认权限范围', 'snsapi_base');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $wechatAccountIdArg = $input->getArgument('wechat-account-id');
        $wechatAccountId = is_scalar($wechatAccountIdArg) ? (string) $wechatAccountIdArg : '';
        $redirectUris = $input->getOption('redirect-uri');
        $scope = $input->getOption('scope');

        // 查找微信账号
        $wechatAccount = $this->entityManager->getRepository(Account::class)->find($wechatAccountId);
        if ($wechatAccount === null) {
            $io->error(sprintf('微信公众号账号 ID "%s" 不存在', $wechatAccountId));
            return Command::FAILURE;
        }

        // 生成客户端凭据
        $clientId = $this->generateClientId();
        $clientSecret = $this->generateClientSecret();

        try {
            // 直接更新Account表的OAuth2字段
            $sql = '
                UPDATE wechat_official_account_account 
                SET oauth2_client_id = :clientId, 
                    oauth2_client_secret = :clientSecret,
                    oauth2_redirect_uris = :redirectUris,
                    oauth2_scopes = :scopes
                WHERE id = :id
            ';
            
            $this->entityManager->getConnection()->executeStatement($sql, [
                'id' => $wechatAccount->getId(),
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
                'redirectUris' => is_array($redirectUris) ? implode("\n", $redirectUris) : null,
                'scopes' => $scope
            ]);

            $io->success('OAuth2配置创建成功！');
            
            $io->table(
                ['属性', '值'],
                [
                    ['微信账号', $wechatAccount->getName() ?? $wechatAccount->getAppId()],
                    ['Client ID', $clientId],
                    ['Client Secret', $clientSecret],
                    ['权限范围', $scope],
                    ['回调URI', is_array($redirectUris) ? implode(', ', $redirectUris) : '无'],
                ]
            );

            $io->warning([
                '请妥善保管 Client Secret，它不会再次显示。',
                '如果丢失，您需要重新生成新的凭据。'
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('创建OAuth2配置失败: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function generateClientId(): string
    {
        return 'oauth2_' . bin2hex(random_bytes(16));
    }

    private function generateClientSecret(): string
    {
        return bin2hex(random_bytes(32));
    }
}