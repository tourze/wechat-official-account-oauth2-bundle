<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2;

use WechatOfficialAccountBundle\Request\WithAccountRequest;

/**
 * 获取OAuth2用户信息请求
 * 
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#3
 */
class GetUserInfoRequest extends WithAccountRequest
{
    private string $openid;
    private string $accessToken;
    private string $lang = 'zh_CN';

    public function getRequestPath(): string
    {
        return 'https://api.weixin.qq.com/sns/userinfo';
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRequestOptions(): ?array
    {
        return [
            'query' => [
                'access_token' => $this->getAccessToken(),
                'openid' => $this->getOpenid(),
                'lang' => $this->getLang(),
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

    public function getOpenid(): string
    {
        return $this->openid;
    }

    public function setOpenid(string $openid): void
    {
        $this->openid = $openid;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setLang(string $lang): void
    {
        $this->lang = $lang;
    }

    public function getRequestMethod(): ?string
    {
        return 'GET';
    }
}