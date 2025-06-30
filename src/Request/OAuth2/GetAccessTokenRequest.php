<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2;

use HttpClientBundle\Request\ApiRequest;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * 通过OAuth2授权码获取访问令牌请求
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#1
 */
class GetAccessTokenRequest extends ApiRequest
{
    private Account $account;
    private string $code;
    private string $grantType = 'authorization_code';

    public function getRequestPath(): string
    {
        return 'https://api.weixin.qq.com/sns/oauth2/access_token';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'query' => [
                'appid' => $this->getAccount()->getAppId(),
                'secret' => $this->getAccount()->getAppSecret(),
                'code' => $this->getCode(),
                'grant_type' => $this->getGrantType(),
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

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getGrantType(): string
    {
        return $this->grantType;
    }

    public function setGrantType(string $grantType): void
    {
        $this->grantType = $grantType;
    }

    public function getRequestMethod(): ?string
    {
        return 'GET';
    }
}