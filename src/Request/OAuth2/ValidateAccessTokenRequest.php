<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2;

use HttpClientBundle\Request\ApiRequest;

/**
 * 验证OAuth2访问令牌请求
 *
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#4
 */
class ValidateAccessTokenRequest extends ApiRequest
{
    private string $accessToken;

    private string $openid;

    public function getRequestPath(): string
    {
        return 'https://api.weixin.qq.com/sns/auth';
    }

    /**
     * @return ?array<string, mixed>
     */
    public function getRequestOptions(): ?array
    {
        return [
            'query' => [
                'access_token' => $this->getAccessToken(),
                'openid' => $this->getOpenId(),
            ],
        ];
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getOpenId(): string
    {
        return $this->openid;
    }

    public function setOpenId(string $openid): void
    {
        $this->openid = $openid;
    }

    public function getRequestMethod(): ?string
    {
        return 'GET';
    }
}
