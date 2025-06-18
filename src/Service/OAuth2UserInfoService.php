<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Service;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AccessToken;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\GetUserInfoRequest;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;

/**
 * OAuth2用户信息服务
 */
#[Autoconfigure(lazy: true)]
class OAuth2UserInfoService
{
    public function __construct(
        private readonly OfficialAccountClient $wechatClient,
    ) {
    }

    /**
     * 获取用户信息
     */
    public function getUserInfo(OAuth2AccessToken $accessToken): array
    {
        $userInfo = [
            'openid' => $accessToken->getOpenid(),
            'scope' => $accessToken->getScopes(),
        ];

        if ($accessToken->getUnionid() !== null) {
            $userInfo['unionid'] = $accessToken->getUnionid();
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
     */
    private function getDetailedUserInfo(OAuth2AccessToken $accessToken): array
    {
        $account = $accessToken->getWechatAccount();
        
        // 创建获取用户信息的请求
        $request = new GetUserInfoRequest();
        $request->setAccount($account);
        $request->setOpenid($accessToken->getOpenid());
        $request->setAccessToken($accessToken->getAccessToken());
        $request->setLang('zh_CN');

        // 使用 OfficialAccountClient 发送请求
        $response = $this->wechatClient->request($request);

        // 过滤并返回用户信息
        return [
            'nickname' => $response['nickname'] ?? '',
            'sex' => $response['sex'] ?? 0,
            'language' => $response['language'] ?? '',
            'city' => $response['city'] ?? '',
            'province' => $response['province'] ?? '',
            'country' => $response['country'] ?? '',
            'headimgurl' => $response['headimgurl'] ?? '',
            'subscribe' => $response['subscribe'] ?? 0,
            'subscribe_time' => $response['subscribe_time'] ?? 0,
            'subscribe_scene' => $response['subscribe_scene'] ?? '',
            'qr_scene' => $response['qr_scene'] ?? '',
            'qr_scene_str' => $response['qr_scene_str'] ?? '',
        ];
    }
}
