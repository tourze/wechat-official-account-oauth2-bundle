<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Service;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\GetAccessTokenRequest;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\GetOAuth2UserInfoRequest;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\RefreshAccessTokenRequest;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\ValidateAccessTokenRequest;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;

/**
 * 微信OAuth2服务 - 处理与微信OAuth2相关的操作
 */
#[Autoconfigure(lazy: true)]
class WechatOAuth2Service
{
    public function __construct(
        private readonly OfficialAccountClient $wechatClient,
    ) {
    }

    /**
     * 构建微信OAuth2授权URL
     */
    public function buildAuthorizationUrl(
        Account $account,
        string $redirectUri,
        string $scope = 'snsapi_base',
        ?string $state = null
    ): string {
        $params = [
            'appid' => $account->getAppId(),
            'redirect_uri' => urlencode($redirectUri),
            'response_type' => 'code',
            'scope' => $scope,
        ];

        if ($state !== null) {
            $params['state'] = $state;
        }

        $queryString = http_build_query($params);
        
        return "https://open.weixin.qq.com/connect/oauth2/authorize?{$queryString}#wechat_redirect";
    }

    /**
     * 通过授权码获取微信访问令牌
     */
    public function getAccessTokenByCode(Account $account, string $code): array
    {
        $request = new GetAccessTokenRequest();
        $request->setAccount($account);
        $request->setCode($code);

        return $this->wechatClient->request($request);
    }

    /**
     * 刷新微信访问令牌
     */
    public function refreshAccessToken(Account $account, string $refreshToken): array
    {
        $request = new RefreshAccessTokenRequest();
        $request->setAccount($account);
        $request->setRefreshToken($refreshToken);

        return $this->wechatClient->request($request);
    }

    /**
     * 获取用户信息（使用微信OAuth2访问令牌）
     */
    public function getUserInfo(string $accessToken, string $openid, string $lang = 'zh_CN'): array
    {
        $request = new GetOAuth2UserInfoRequest();
        $request->setOauthAccessToken($accessToken);
        $request->setOpenid($openid);
        $request->setLang($lang);

        return $this->wechatClient->request($request);
    }

    /**
     * 验证微信访问令牌
     */
    public function validateAccessToken(Account $account, string $accessToken, string $openid): bool
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