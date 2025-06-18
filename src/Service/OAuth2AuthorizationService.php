<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AccessToken;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AuthorizationCode;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * OAuth2授权服务
 */
#[Autoconfigure(lazy: true)]
class OAuth2AuthorizationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        // private readonly OfficialAccountClient $wechatClient,
        private readonly WechatOAuth2Service $wechatOAuth2Service,
    ) {
    }

    /**
     * 构建微信授权URL
     */
    public function buildWechatAuthUrl(Account $account, string $scope, string $redirectUri): string
    {
        $params = [
            'appid' => $account->getAppId(),
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => 'oauth2',
        ];

        $queryString = http_build_query($params);
        
        return "https://open.weixin.qq.com/connect/oauth2/authorize?{$queryString}#wechat_redirect";
    }

    /**
     * 使用微信code获取用户信息
     */
    public function getUserInfoByCode(Account $account, string $code): array
    {
        // 获取默认配置
        $config = $this->entityManager->getRepository(\Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config::class)
            ->findOneBy(['account' => $account, 'valid' => true]);
            
        if ($config === null) {
            throw new \RuntimeException('No valid OAuth2 config found for account');
        }
        
        // 使用 WechatOAuth2Service 的 exchangeCodeForToken 方法
        // 需要创建一个假的 state 来处理这个流程
        $state = bin2hex(random_bytes(16));
        $stateEntity = new \Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2State($state, $config);
        
        $this->entityManager->persist($stateEntity);
        $this->entityManager->flush();
        
        // 使用 handleCallback 来处理 code
        try {
            $user = $this->wechatOAuth2Service->handleCallback($code, $state);
            
            return [
                'openid' => $user->getOpenid(),
                'scope' => $user->getScope(),
                'unionid' => $user->getUnionid(),
                'nickname' => $user->getNickname(),
                'sex' => $user->getSex(),
                'province' => $user->getProvince(),
                'city' => $user->getCity(),
                'country' => $user->getCountry(),
                'headimgurl' => $user->getHeadimgurl(),
            ];
        } finally {
            // 清理临时 state
            $this->entityManager->remove($stateEntity);
            $this->entityManager->flush();
        }
    }


    /**
     * 创建授权码
     */
    public function createAuthorizationCode(
        Account $account,
        string $openid,
        ?string $unionid,
        string $redirectUri,
        string $scope,
        ?string $state
    ): OAuth2AuthorizationCode {
        $authorizationCode = new OAuth2AuthorizationCode();
        $authorizationCode->setCode($this->generateAuthorizationCode());
        $authorizationCode->setOpenid($openid);
        $authorizationCode->setUnionid($unionid);
        $authorizationCode->setRedirectUri($redirectUri);
        $authorizationCode->setScopes($scope);
        $authorizationCode->setState($state);
        $authorizationCode->setWechatAccount($account);
        $authorizationCode->setExpiresAt(new \DateTime('+10 minutes'));

        $this->entityManager->persist($authorizationCode);
        $this->entityManager->flush();

        return $authorizationCode;
    }

    private function generateAuthorizationCode(): string
    {
        return 'AC_' . bin2hex(random_bytes(16));
    }

    /**
     * 使用授权码换取访问令牌
     */
    public function exchangeCodeForToken(
        string $code,
        string $redirectUri,
        Account $account
    ): OAuth2AccessToken {
        // 查找授权码
        $authorizationCode = $this->entityManager->getRepository(OAuth2AuthorizationCode::class)
            ->findOneBy(['code' => $code, 'wechatAccount' => $account, 'used' => false]);

        if ($authorizationCode === null) {
            throw new \InvalidArgumentException('无效的授权码');
        }

        if ($authorizationCode->isExpired()) {
            throw new \InvalidArgumentException('授权码已过期');
        }

        if ($authorizationCode->getRedirectUri() !== $redirectUri) {
            throw new \InvalidArgumentException('重定向URI不匹配');
        }

        // 标记授权码为已使用
        $authorizationCode->setUsed(true);

        // 创建访问令牌
        $accessToken = new OAuth2AccessToken();
        $accessToken->setAccessToken($this->generateAccessToken());
        $accessToken->setRefreshToken($this->generateRefreshToken());
        $accessToken->setOpenid($authorizationCode->getOpenid());
        $accessToken->setUnionid($authorizationCode->getUnionid());
        $accessToken->setScopes($authorizationCode->getScopes());
        $accessToken->setWechatAccount($account);
        $accessToken->setAccessTokenExpiresAt(new \DateTime('+2 hours'));
        $accessToken->setRefreshTokenExpiresAt(new \DateTime('+30 days'));

        $this->entityManager->persist($authorizationCode);
        $this->entityManager->persist($accessToken);
        $this->entityManager->flush();

        return $accessToken;
    }

    private function generateAccessToken(): string
    {
        return 'AT_' . bin2hex(random_bytes(32));
    }

    private function generateRefreshToken(): string
    {
        return 'RT_' . bin2hex(random_bytes(32));
    }

    /**
     * 刷新访问令牌
     */
    public function refreshAccessToken(string $refreshToken, Account $account): OAuth2AccessToken
    {
        // 查找刷新令牌
        $oldToken = $this->entityManager->getRepository(OAuth2AccessToken::class)
            ->findOneBy(['refreshToken' => $refreshToken, 'wechatAccount' => $account, 'revoked' => false]);

        if ($oldToken === null) {
            throw new \InvalidArgumentException('无效的刷新令牌');
        }

        if ($oldToken->isRefreshTokenExpired()) {
            throw new \InvalidArgumentException('刷新令牌已过期');
        }

        // 撤销旧令牌
        $oldToken->setRevoked(true);

        // 创建新访问令牌
        $newToken = new OAuth2AccessToken();
        $newToken->setAccessToken($this->generateAccessToken());
        $newToken->setRefreshToken($this->generateRefreshToken());
        $newToken->setOpenid($oldToken->getOpenid());
        $newToken->setUnionid($oldToken->getUnionid());
        $newToken->setScopes($oldToken->getScopes());
        $newToken->setWechatAccount($account);
        $newToken->setAccessTokenExpiresAt(new \DateTime('+2 hours'));
        $newToken->setRefreshTokenExpiresAt(new \DateTime('+30 days'));

        $this->entityManager->persist($oldToken);
        $this->entityManager->persist($newToken);
        $this->entityManager->flush();

        return $newToken;
    }

    /**
     * 撤销令牌
     */
    public function revokeToken(string $token, Account $account, ?string $tokenTypeHint = null): void
    {
        if ($tokenTypeHint === 'refresh_token') {
            // 按刷新令牌查找
            $tokenEntity = $this->entityManager->getRepository(OAuth2AccessToken::class)
                ->findOneBy(['refreshToken' => $token, 'wechatAccount' => $account]);
        } else {
            // 按访问令牌查找
            $tokenEntity = $this->entityManager->getRepository(OAuth2AccessToken::class)
                ->findOneBy(['accessToken' => $token, 'wechatAccount' => $account]);

            // 如果没找到，尝试按刷新令牌查找
            if ($tokenEntity === null) {
                $tokenEntity = $this->entityManager->getRepository(OAuth2AccessToken::class)
                    ->findOneBy(['refreshToken' => $token, 'wechatAccount' => $account]);
            }
        }

        if ($tokenEntity !== null) {
            $tokenEntity->setRevoked(true);
            $this->entityManager->persist($tokenEntity);
            $this->entityManager->flush();
        }
    }
}