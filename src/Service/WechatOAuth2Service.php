<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2State;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2User;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\OAuth2Exception;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\WechatOAuth2ApiException;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\WechatOAuth2ConfigurationException;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2ConfigRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2StateRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2UserRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\GetAccessTokenRequest;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\GetOAuth2UserInfoRequest;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\RefreshAccessTokenRequest;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\ValidateAccessTokenRequest;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;

/**
 * 微信OAuth2服务 - 处理与微信OAuth2相关的操作
 */
#[Autoconfigure(lazy: true, public: true)]
#[WithMonologChannel(channel: 'wechat_official_account_o_auth2')]
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
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * 生成授权URL
     */
    public function generateAuthorizationUrl(?string $sessionId = null, ?string $scope = null): string
    {
        $config = $this->configRepository->findValidConfig();
        if (null === $config) {
            throw new WechatOAuth2ConfigurationException('No valid Wechat OAuth2 configuration found');
        }

        $state = bin2hex(random_bytes(16));
        $stateEntity = new WechatOAuth2State();
        $stateEntity->setState($state);
        $stateEntity->setConfig($config);

        if (null !== $sessionId) {
            $stateEntity->setSessionId($sessionId);
        }

        $this->entityManager->persist($stateEntity);
        $this->entityManager->flush();

        $redirectUri = $this->urlGenerator->generate('wechat_oauth2_callback', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $params = [
            'appid' => $config->getAppId(),
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => null !== $scope ? $scope : (null !== $config->getScope() ? $config->getScope() : 'snsapi_base'),
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
        if (null === $stateEntity || !$stateEntity->isValidState()) {
            throw new OAuth2Exception('Invalid or expired state', 0, null, ['state' => $state]);
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
        if (isset($tokenData['scope'])) {
            $scope = $tokenData['scope'];
            assert(is_string($scope));
            if (str_contains($scope, 'snsapi_userinfo')) {
                $accessToken = $tokenData['access_token'];
                $openid = $tokenData['openid'];
                assert(is_string($accessToken));
                assert(is_string($openid));
                $userInfo = $this->fetchUserInfo($accessToken, $openid);
            }
        }

        // Merge all data
        $userData = array_merge($tokenData, $userInfo);

        return $this->userRepository->updateOrCreate($userData, $config);
    }

    /**
     * 交换授权码获取访问令牌
     * @return array<string, mixed>
     */
    private function exchangeCodeForToken(string $code, mixed $account): array
    {
        try {
            assert($account instanceof Account);
            $request = new GetAccessTokenRequest();
            $request->setAccount($account);
            $request->setCode($code);

            $response = $this->wechatClient->request($request);
            assert(is_array($response));

            if (isset($response['errcode'])) {
                $errcode = $response['errcode'];
                assert(is_int($errcode) || is_string($errcode));
                if (0 !== $errcode) {
                    $errmsg = $response['errmsg'] ?? '';
                    assert(is_string($errmsg));
                    $this->logger?->warning('Wechat OAuth2 token exchange API error', [
                        'errcode' => $errcode,
                        'errmsg' => $errmsg,
                    ]);
                    /** @var array<string, mixed> $response */
                    throw new WechatOAuth2ApiException(sprintf('Failed to exchange code for token: %s - %s', $errcode, $errmsg), 0, null, 'sns/oauth2/access_token', $response);
                }
            }

            if (!isset($response['access_token']) || '' === $response['access_token']) {
                /** @var array<string, mixed> $response */
                throw new WechatOAuth2ApiException('No access token received from Wechat API', 0, null, 'sns/oauth2/access_token', $response);
            }

            /** @var array<string, mixed> $response */
            return $response;
        } catch (\Exception $e) {
            if ($e instanceof WechatOAuth2ApiException) {
                throw $e;
            }

            $this->logger?->error('Wechat OAuth2 token exchange error', ['error' => $e->getMessage()]);
            throw new WechatOAuth2ApiException('Network error during token exchange', 0, $e, 'sns/oauth2/access_token', null);
        }
    }

    /**
     * 获取用户详细信息
     * @return array<string, mixed>
     */
    private function fetchUserInfo(string $accessToken, string $openid): array
    {
        try {
            $request = new GetOAuth2UserInfoRequest();
            $request->setOauthAccessToken($accessToken);
            $request->setOpenId($openid);
            $request->setLang('zh_CN');

            $response = $this->wechatClient->request($request);
            assert(is_array($response));

            if (isset($response['errcode'])) {
                $errcode = $response['errcode'];
                assert(is_int($errcode) || is_string($errcode));
                if (0 !== $errcode) {
                    $errmsg = $response['errmsg'] ?? '';
                    assert(is_string($errmsg));
                    /** @var array<string, mixed> $response */
                    throw new WechatOAuth2ApiException(sprintf('Failed to get user info: %s - %s', $errcode, $errmsg), 0, null, 'sns/userinfo', $response);
                }
            }

            /** @var array<string, mixed> $response */
            return $response;
        } catch (\Exception $e) {
            if ($e instanceof WechatOAuth2ApiException) {
                throw $e;
            }

            throw new WechatOAuth2ApiException('Failed to fetch user info', 0, $e, 'sns/userinfo', null);
        }
    }

    /**
     * 获取用户信息
     * @return array<string, mixed>
     */
    public function getUserInfo(string $openid, bool $forceRefresh = false): array
    {
        $user = $this->userRepository->findByOpenid($openid);
        if (null === $user) {
            throw new OAuth2Exception('User not found', 0, null, ['openid' => $openid]);
        }

        if (!$forceRefresh && !$user->isTokenExpired() && null !== $user->getRawData()) {
            return $user->getRawData();
        }

        if ($user->isTokenExpired()) {
            $this->refreshToken($openid);
            $user = $this->userRepository->findByOpenid($openid);
            if (null === $user) {
                throw new OAuth2Exception('User not found after token refresh', 0, null, ['openid' => $openid]);
            }
        }

        $userInfo = $this->fetchUserInfo($user->getAccessToken(), $openid);

        $nickname = $userInfo['nickname'] ?? null;
        assert(is_string($nickname) || null === $nickname);
        $user->setNickname($nickname);

        $sex = $userInfo['sex'] ?? null;
        assert(is_int($sex) || null === $sex);
        $user->setSex($sex);

        $province = $userInfo['province'] ?? null;
        assert(is_string($province) || null === $province);
        $user->setProvince($province);

        $city = $userInfo['city'] ?? null;
        assert(is_string($city) || null === $city);
        $user->setCity($city);

        $country = $userInfo['country'] ?? null;
        assert(is_string($country) || null === $country);
        $user->setCountry($country);

        $headimgurl = $userInfo['headimgurl'] ?? null;
        assert(is_string($headimgurl) || null === $headimgurl);
        $user->setHeadimgurl($headimgurl);

        $privilege = $userInfo['privilege'] ?? null;
        if (is_array($privilege) || null === $privilege) {
            /** @var array<string>|null $privilege */
            $user->setPrivilege($privilege);
        }

        $unionid = $userInfo['unionid'] ?? null;
        assert(is_string($unionid) || null === $unionid);
        $user->setUnionId($unionid);

        $user->setRawData($userInfo);

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
        if (null === $user) {
            return false;
        }

        $config = $user->getConfig();
        $account = $config->getAccount();

        try {
            $request = new RefreshAccessTokenRequest();
            $request->setAccount($account);
            $request->setRefreshToken($user->getRefreshToken());

            $response = $this->wechatClient->request($request);
            assert(is_array($response));

            if (isset($response['errcode'])) {
                $errcode = $response['errcode'];
                assert(is_int($errcode) || is_string($errcode));
                if (0 !== $errcode) {
                    return false;
                }
            }

            $accessToken = $response['access_token'];
            assert(is_string($accessToken));
            $user->setAccessToken($accessToken);

            $refreshToken = $response['refresh_token'];
            assert(is_string($refreshToken));
            $user->setRefreshToken($refreshToken);

            $expiresIn = $response['expires_in'];
            assert(is_int($expiresIn) || is_string($expiresIn));
            $user->setExpiresIn((int) $expiresIn);

            $scope = $response['scope'] ?? null;
            assert(is_string($scope) || null === $scope);
            $user->setScope($scope);

            if (isset($response['openid'])) {
                $openidValue = $response['openid'];
                assert(is_string($openidValue));
                $user->setOpenId($openidValue);
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
            if ($this->refreshToken($user->getOpenId())) {
                ++$refreshed;
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
            $request->setOpenId($openid);

            $response = $this->wechatClient->request($request);
            assert(is_array($response));

            if (isset($response['errcode'])) {
                $errcode = $response['errcode'];
                assert(is_int($errcode) || is_string($errcode));

                return 0 === $errcode;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
