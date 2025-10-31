<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Service;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AccessToken;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\OAuth2InvalidArgumentException;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\GetUserInfoRequest;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;

/**
 * OAuth2用户信息服务
 */
#[Autoconfigure(public: true)]
readonly class OAuth2UserInfoService
{
    public function __construct(
        private OfficialAccountClient $wechatClient,
    ) {
    }

    /**
     * 获取用户信息
     * @return array<string, mixed>
     */
    public function getUserInfo(OAuth2AccessToken $accessToken): array
    {
        $userInfo = [
            'openid' => $accessToken->getOpenId(),
            'scope' => $accessToken->getScopes(),
        ];

        if (null !== $accessToken->getUnionId()) {
            $userInfo['unionid'] = $accessToken->getUnionId();
        }

        // 如果scope包含snsapi_userinfo，尝试获取详细用户信息
        $scopes = $accessToken->getScopesArray();
        if (in_array('snsapi_userinfo', $scopes, true)) {
            try {
                $detailedInfo = $this->getDetailedUserInfo($accessToken);
                $userInfo = array_merge($userInfo, $detailedInfo);
            } catch (\Exception $e) {
                // 如果获取详细信息失败，返回基本信息
                $userInfo['error'] = '无法获取详细用户信息: ' . $e->getMessage();
            }
        }

        return $userInfo;
    }

    /**
     * 获取详细用户信息
     * @return array<string, mixed>
     */
    private function getDetailedUserInfo(OAuth2AccessToken $accessToken): array
    {
        $this->validateAccessToken($accessToken);

        $request = $this->createUserInfoRequest($accessToken);
        $response = $this->wechatClient->request($request);
        assert(is_array($response));

        // 确保响应数组键是字符串
        /** @var array<string, mixed> $response */
        return $this->extractUserInfoFromResponse($response);
    }

    /**
     * 验证AccessToken必需字段
     */
    private function validateAccessToken(OAuth2AccessToken $accessToken): void
    {
        if (null === $accessToken->getWechatAccount()) {
            throw new OAuth2InvalidArgumentException('Access token must have an associated account');
        }

        if (null === $accessToken->getOpenId()) {
            throw new OAuth2InvalidArgumentException('Access token must have an openid');
        }

        if (null === $accessToken->getAccessToken()) {
            throw new OAuth2InvalidArgumentException('Access token must have a token value');
        }
    }

    /**
     * 创建用户信息请求
     */
    private function createUserInfoRequest(OAuth2AccessToken $accessToken): GetUserInfoRequest
    {
        $account = $accessToken->getWechatAccount();
        $openId = $accessToken->getOpenId();
        $accessTokenValue = $accessToken->getAccessToken();

        assert(null !== $account, 'Account must not be null after validation');
        assert(null !== $openId, 'OpenId must not be null after validation');
        assert(null !== $accessTokenValue, 'Access token value must not be null after validation');

        $request = new GetUserInfoRequest();
        $request->setAccount($account);
        $request->setOpenId($openId);
        $request->setAccessToken($accessTokenValue);
        $request->setLang('zh_CN');

        return $request;
    }

    /**
     * 从响应中提取用户信息
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     */
    private function extractUserInfoFromResponse(array $response): array
    {
        return [
            'nickname' => $this->getStringValue($response, 'nickname'),
            'sex' => $this->getIntValue($response, 'sex'),
            'language' => $this->getStringValue($response, 'language'),
            'city' => $this->getStringValue($response, 'city'),
            'province' => $this->getStringValue($response, 'province'),
            'country' => $this->getStringValue($response, 'country'),
            'headimgurl' => $this->getStringValue($response, 'headimgurl'),
            'subscribe' => $this->getIntValue($response, 'subscribe'),
            'subscribe_time' => $this->getIntValue($response, 'subscribe_time'),
            'subscribe_scene' => $this->getStringValue($response, 'subscribe_scene'),
            'qr_scene' => $this->getQrSceneValue($response),
            'qr_scene_str' => $this->getStringValue($response, 'qr_scene_str'),
        ];
    }

    /**
     * 获取字符串类型的值
     * @param array<string, mixed> $data
     */
    private function getStringValue(array $data, string $key): string
    {
        return isset($data[$key]) && is_string($data[$key]) ? $data[$key] : '';
    }

    /**
     * 获取整数类型的值
     * @param array<string, mixed> $data
     */
    private function getIntValue(array $data, string $key): int
    {
        return isset($data[$key]) && is_int($data[$key]) ? $data[$key] : 0;
    }

    /**
     * 获取qr_scene值（支持int或string）
     * @param array<string, mixed> $data
     */
    private function getQrSceneValue(array $data): string
    {
        if (!isset($data['qr_scene'])) {
            return '';
        }

        $value = $data['qr_scene'];

        return is_int($value) || is_string($value) ? (string) $value : '';
    }
}
