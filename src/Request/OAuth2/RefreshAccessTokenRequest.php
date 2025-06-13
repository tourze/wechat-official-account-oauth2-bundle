<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2;

use HttpClientBundle\Request\ApiRequest;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * 刷新OAuth2访问令牌请求
 * 
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#2
 */
class RefreshAccessTokenRequest extends ApiRequest
{
    private Account $account;
    private string $refreshToken;
    private string $grantType = 'refresh_token';

    public function getRequestPath(): string
    {
        return 'https://api.weixin.qq.com/sns/oauth2/refresh_token';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'query' => [
                'appid' => $this->getAccount()->getAppId(),
                'grant_type' => $this->getGrantType(),
                'refresh_token' => $this->getRefreshToken(),
            ],
        ];
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    public function getGrantType(): string
    {
        return $this->grantType;
    }

    public function setGrantType(string $grantType): void
    {
        $this->grantType = $grantType;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    public function getRequestMethod(): ?string
    {
        return 'GET';
    }
}