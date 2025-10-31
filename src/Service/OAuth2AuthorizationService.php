<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AccessToken;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AuthorizationCode;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2State;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\OAuth2AuthorizationException;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\OAuth2AccessTokenRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\OAuth2AuthorizationCodeRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2ConfigRepository;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * OAuth2授权服务
 */
#[Autoconfigure(public: true)]
readonly class OAuth2AuthorizationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private WechatOAuth2Service $wechatOAuth2Service,
        private OAuth2AccessTokenRepository $accessTokenRepository,
        private OAuth2AuthorizationCodeRepository $authorizationCodeRepository,
        private WechatOAuth2ConfigRepository $oauth2ConfigRepository,
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
     * @return array<string, mixed>
     */
    public function getUserInfoByCode(Account $account, string $code): array
    {
        // 获取默认配置
        $config = $this->oauth2ConfigRepository->findOneBy(['account' => $account, 'valid' => true]);

        if (null === $config) {
            throw new OAuth2AuthorizationException('No valid OAuth2 config found for account', 0, null, ['error_code' => 'config_not_found']);
        }

        // 使用 WechatOAuth2Service 的 exchangeCodeForToken 方法
        // 需要创建一个假的 state 来处理这个流程
        $state = bin2hex(random_bytes(16));
        $stateEntity = new WechatOAuth2State();
        $stateEntity->setState($state);
        $stateEntity->setConfig($config);

        $this->entityManager->persist($stateEntity);
        $this->entityManager->flush();

        // 使用 handleCallback 来处理 code
        try {
            $user = $this->wechatOAuth2Service->handleCallback($code, $state);

            return [
                'openid' => $user->getOpenId(),
                'scope' => $user->getScope(),
                'unionid' => $user->getUnionId(),
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
        ?string $state,
    ): OAuth2AuthorizationCode {
        $authorizationCode = new OAuth2AuthorizationCode();
        $authorizationCode->setCode($this->generateAuthorizationCode());
        $authorizationCode->setOpenId($openid);
        $authorizationCode->setUnionId($unionid);
        $authorizationCode->setRedirectUri($redirectUri);
        $authorizationCode->setScopes($scope);
        $authorizationCode->setState($state);
        $authorizationCode->setWechatAccount($account);
        $authorizationCode->setExpiresAt(new \DateTimeImmutable('+10 minutes'));

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
        Account $account,
    ): OAuth2AccessToken {
        // 查找授权码
        $authorizationCode = $this->authorizationCodeRepository
            ->findOneBy(['code' => $code, 'wechatAccount' => $account, 'used' => false])
        ;

        if (null === $authorizationCode) {
            throw new OAuth2AuthorizationException('无效的授权码', 0, null, ['error_code' => OAuth2AuthorizationException::INVALID_AUTHORIZATION_CODE]);
        }

        if ($authorizationCode->isExpired()) {
            throw new OAuth2AuthorizationException('授权码已过期', 0, null, ['error_code' => OAuth2AuthorizationException::EXPIRED_AUTHORIZATION_CODE]);
        }

        if ($authorizationCode->getRedirectUri() !== $redirectUri) {
            throw new OAuth2AuthorizationException('重定向URI不匹配', 0, null, ['error_code' => OAuth2AuthorizationException::REDIRECT_URI_MISMATCH]);
        }

        // 标记授权码为已使用
        $authorizationCode->setUsed(true);

        // 创建访问令牌
        $accessToken = new OAuth2AccessToken();
        $accessToken->setAccessToken($this->generateAccessToken());
        $accessToken->setRefreshToken($this->generateRefreshToken());

        $openid = $authorizationCode->getOpenId();
        if (null === $openid) {
            throw new OAuth2AuthorizationException('Authorization code 缺少必需的 openid', 0, null, ['error_code' => OAuth2AuthorizationException::INVALID_CODE]);
        }
        $accessToken->setOpenId($openid);
        $accessToken->setUnionId($authorizationCode->getUnionId());
        $accessToken->setScopes($authorizationCode->getScopes());
        $accessToken->setWechatAccount($account);
        $accessToken->setAccessTokenExpiresAt(new \DateTimeImmutable('+2 hours'));
        $accessToken->setRefreshTokenExpiresAt(new \DateTimeImmutable('+30 days'));

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
        $oldToken = $this->accessTokenRepository
            ->findOneBy(['refreshToken' => $refreshToken, 'wechatAccount' => $account, 'revoked' => false])
        ;

        if (null === $oldToken) {
            throw new OAuth2AuthorizationException('无效的刷新令牌', 0, null, ['error_code' => OAuth2AuthorizationException::INVALID_REFRESH_TOKEN]);
        }

        if ($oldToken->isRefreshTokenExpired()) {
            throw new OAuth2AuthorizationException('刷新令牌已过期', 0, null, ['error_code' => OAuth2AuthorizationException::EXPIRED_REFRESH_TOKEN]);
        }

        // 撤销旧令牌
        $oldToken->setRevoked(true);

        // 创建新访问令牌
        $newToken = new OAuth2AccessToken();
        $newToken->setAccessToken($this->generateAccessToken());
        $newToken->setRefreshToken($this->generateRefreshToken());

        $openid = $oldToken->getOpenId();
        if (null === $openid) {
            throw new OAuth2AuthorizationException('Token 缺少必需的 openid', 0, null, ['error_code' => OAuth2AuthorizationException::INVALID_TOKEN]);
        }
        $newToken->setOpenId($openid);
        $newToken->setUnionId($oldToken->getUnionId());
        $newToken->setScopes($oldToken->getScopes());
        $newToken->setWechatAccount($account);
        $newToken->setAccessTokenExpiresAt(new \DateTimeImmutable('+2 hours'));
        $newToken->setRefreshTokenExpiresAt(new \DateTimeImmutable('+30 days'));

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
        if ('refresh_token' === $tokenTypeHint) {
            // 按刷新令牌查找
            $tokenEntity = $this->accessTokenRepository
                ->findOneBy(['refreshToken' => $token, 'wechatAccount' => $account])
            ;
        } else {
            // 按访问令牌查找
            $tokenEntity = $this->accessTokenRepository
                ->findOneBy(['accessToken' => $token, 'wechatAccount' => $account])
            ;

            // 如果没找到，尝试按刷新令牌查找
            if (null === $tokenEntity) {
                $tokenEntity = $this->accessTokenRepository
                    ->findOneBy(['refreshToken' => $token, 'wechatAccount' => $account])
                ;
            }
        }

        if (null !== $tokenEntity) {
            $tokenEntity->setRevoked(true);
            $this->entityManager->persist($tokenEntity);
            $this->entityManager->flush();
        }
    }
}
