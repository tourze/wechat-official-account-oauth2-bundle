<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2State;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2User;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\WechatOAuth2ApiException;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\WechatOAuth2ConfigurationException;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\WechatOAuth2Exception;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2ConfigRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2StateRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2UserRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\GetAccessTokenRequest;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\GetOAuth2UserInfoRequest;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\RefreshAccessTokenRequest;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\ValidateAccessTokenRequest;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;

/**
 * 微信OAuth2服务 - 处理与微信OAuth2相关的操作
 */
#[Autoconfigure(lazy: true)]
class WechatOAuth2Service
{
    private const AUTHORIZE_URL = 'https://open.weixin.qq.com/connect/oauth2/authorize';


    public function __construct(
        private readonly OfficialAccountClient $wechatClient,
        private readonly WechatOAuth2ConfigRepository $configRepository,
        private readonly WechatOAuth2StateRepository $stateRepository,
        private readonly WechatOAuth2UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    /**
     * 生成授权URL
     */
    public function generateAuthorizationUrl(?string $sessionId = null, ?string $scope = null): string
    {
        $config = $this->configRepository->findValidConfig();
        if ($config === null) {
            throw new WechatOAuth2ConfigurationException('No valid Wechat OAuth2 configuration found');
        }

        $state = bin2hex(random_bytes(16));
        $stateEntity = new WechatOAuth2State($state, $config);
        
        if ($sessionId !== null) {
            $stateEntity->setSessionId($sessionId);
        }
        
        $this->entityManager->persist($stateEntity);
        $this->entityManager->flush();

        $redirectUri = $this->urlGenerator->generate('wechat_oauth2_callback', [], UrlGeneratorInterface::ABSOLUTE_URL);
        
        $params = [
            'appid' => $config->getAppId(),
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => $scope ?: $config->getScope() ?: 'snsapi_base',
            'state' => $state,
        ];

        $queryString = http_build_query($params);
        
        return self::AUTHORIZE_URL . '?' . $queryString . '#wechat_redirect';
    }

    /**
     * 处理回调
     */
    public function handleCallback(string $code, string $state): WechatOAuth2User
    {
        $stateEntity = $this->stateRepository->findValidState($state);
        if ($stateEntity === null || !$stateEntity->isValidState()) {
            throw new WechatOAuth2Exception('Invalid or expired state', 0, null, ['state' => $state]);
        }

        $stateEntity->markAsUsed();
        $this->entityManager->persist($stateEntity);
        $this->entityManager->flush();

        // Get config from state
        $config = $stateEntity->getConfig();
        $account = $config->getAccount();
        
        // Exchange code for access token
        $tokenData = $this->exchangeCodeForToken($code, $account);
        
        // Get user info based on scope
        $userInfo = [];
        if (isset($tokenData['scope']) && str_contains($tokenData['scope'], 'snsapi_userinfo')) {
            $userInfo = $this->fetchUserInfo($tokenData['access_token'], $tokenData['openid']);
        }
        
        // Merge all data
        $userData = array_merge($tokenData, $userInfo);
        
        return $this->userRepository->updateOrCreate($userData, $config);
    }

    /**
     * 交换授权码获取访问令牌
     */
    private function exchangeCodeForToken(string $code, $account): array
    {
        try {
            $request = new GetAccessTokenRequest();
            $request->setAccount($account);
            $request->setCode($code);

            $response = $this->wechatClient->request($request);
            
            if (isset($response['errcode']) && $response['errcode'] != 0) {
                $this->logger?->warning('Wechat OAuth2 token exchange API error', [
                    'errcode' => $response['errcode'],
                    'errmsg' => $response['errmsg'] ?? '',
                ]);
                throw new WechatOAuth2ApiException(
                    sprintf('Failed to exchange code for token: %s - %s', $response['errcode'], $response['errmsg'] ?? ''),
                    0,
                    null,
                    'sns/oauth2/access_token',
                    $response
                );
            }
            
            if (!isset($response['access_token']) || empty($response['access_token'])) {
                throw new WechatOAuth2ApiException(
                    'No access token received from Wechat API',
                    0,
                    null,
                    'sns/oauth2/access_token',
                    $response
                );
            }
            
            return $response;
        } catch (\Exception $e) {
            if ($e instanceof WechatOAuth2ApiException) {
                throw $e;
            }
            
            $this->logger?->error('Wechat OAuth2 token exchange error', ['error' => $e->getMessage()]);
            throw new WechatOAuth2ApiException(
                'Network error during token exchange',
                0,
                $e,
                'sns/oauth2/access_token',
                null
            );
        }
    }

    /**
     * 获取用户详细信息
     */
    private function fetchUserInfo(string $accessToken, string $openid): array
    {
        try {
            $request = new GetOAuth2UserInfoRequest();
            $request->setOauthAccessToken($accessToken);
            $request->setOpenid($openid);
            $request->setLang('zh_CN');

            $response = $this->wechatClient->request($request);
            
            if (isset($response['errcode']) && $response['errcode'] != 0) {
                throw new WechatOAuth2ApiException(
                    sprintf('Failed to get user info: %s - %s', $response['errcode'], $response['errmsg'] ?? ''),
                    0,
                    null,
                    'sns/userinfo',
                    $response
                );
            }
            
            return $response;
        } catch (\Exception $e) {
            if ($e instanceof WechatOAuth2ApiException) {
                throw $e;
            }
            
            throw new WechatOAuth2ApiException(
                'Failed to fetch user info',
                0,
                $e,
                'sns/userinfo',
                null
            );
        }
    }

    /**
     * 获取用户信息
     */
    public function getUserInfo(string $openid, bool $forceRefresh = false): array
    {
        $user = $this->userRepository->findByOpenid($openid);
        if ($user === null) {
            throw new WechatOAuth2Exception('User not found', 0, null, ['openid' => $openid]);
        }

        if (!$forceRefresh && !$user->isTokenExpired() && $user->getRawData() !== null) {
            return $user->getRawData();
        }

        if ($user->isTokenExpired()) {
            $this->refreshToken($openid);
            $user = $this->userRepository->findByOpenid($openid);
        }

        $userInfo = $this->fetchUserInfo($user->getAccessToken(), $openid);
        
        $user->setNickname($userInfo['nickname'] ?? null)
            ->setSex($userInfo['sex'] ?? null)
            ->setProvince($userInfo['province'] ?? null)
            ->setCity($userInfo['city'] ?? null)
            ->setCountry($userInfo['country'] ?? null)
            ->setHeadimgurl($userInfo['headimgurl'] ?? null)
            ->setPrivilege($userInfo['privilege'] ?? null)
            ->setUnionid($userInfo['unionid'] ?? null)
            ->setRawData($userInfo);
            
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        return $userInfo;
    }

    /**
     * 刷新令牌
     */
    public function refreshToken(string $openid): bool
    {
        $user = $this->userRepository->findByOpenid($openid);
        if ($user === null) {
            return false;
        }

        $config = $user->getConfig();
        $account = $config->getAccount();

        try {
            $request = new RefreshAccessTokenRequest();
            $request->setAccount($account);
            $request->setRefreshToken($user->getRefreshToken());

            $response = $this->wechatClient->request($request);

            if (isset($response['errcode']) && $response['errcode'] != 0) {
                return false;
            }

            $user->setAccessToken($response['access_token'])
                ->setRefreshToken($response['refresh_token'])
                ->setExpiresIn((int)$response['expires_in'])
                ->setScope($response['scope'] ?? null);
                
            if (isset($response['openid'])) {
                $user->setOpenid($response['openid']);
            }
            
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            
            return true;
        } catch (\Exception $e) {
            $this->logger?->error('Failed to refresh token', [
                'openid' => $openid,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * 刷新过期的令牌
     */
    public function refreshExpiredTokens(): int
    {
        $expiredUsers = $this->userRepository->findExpiredTokenUsers();
        $refreshed = 0;

        foreach ($expiredUsers as $user) {
            if ($this->refreshToken($user->getOpenid())) {
                $refreshed++;
            }
            
            // Add small delay to avoid rate limiting
            usleep(100000); // 0.1 seconds
        }

        return $refreshed;
    }

    /**
     * 清理过期的状态
     */
    public function cleanupExpiredStates(): int
    {
        return $this->stateRepository->cleanupExpiredStates();
    }

    /**
     * 验证访问令牌
     */
    public function validateAccessToken(string $accessToken, string $openid): bool
    {
        try {
            $request = new ValidateAccessTokenRequest();
            $request->setAccessToken($accessToken);
            $request->setOpenid($openid);

            $response = $this->wechatClient->request($request);
            
            return isset($response['errcode']) && $response['errcode'] === 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}