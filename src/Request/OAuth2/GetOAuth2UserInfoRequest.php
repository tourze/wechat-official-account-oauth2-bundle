<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2;

use HttpClientBundle\Request\ApiRequest;

/**
 * 使用OAuth2访问令牌获取用户信息请求
 * 
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#3
 */
class GetOAuth2UserInfoRequest extends ApiRequest
{
    private string $openid;
    private string $oauthAccessToken;
    private string $lang = 'zh_CN';

    public function getRequestPath(): string
    {
        return 'https://api.weixin.qq.com/sns/userinfo';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'query' => [
                'access_token' => $this->getOauthAccessToken(),
                'openid' => $this->getOpenid(),
                'lang' => $this->getLang(),
            ],
        ];
    }

    public function getOauthAccessToken(): string
    {
        return $this->oauthAccessToken;
    }

    public function setOauthAccessToken(string $oauthAccessToken): void
    {
        $this->oauthAccessToken = $oauthAccessToken;
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