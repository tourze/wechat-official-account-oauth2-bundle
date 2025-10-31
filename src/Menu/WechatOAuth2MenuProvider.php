<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Menu;

use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AccessToken;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AuthorizationCode;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2State;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2User;

/**
 * 微信OAuth2菜单提供者
 * 为EasyAdmin后台提供微信OAuth2相关的菜单项
 */
readonly class WechatOAuth2MenuProvider implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        // 创建或获取微信OAuth2主菜单
        if (null === $item->getChild('微信OAuth2')) {
            $item->addChild('微信OAuth2')
                ->setAttribute('icon', 'fab fa-weixin')
            ;
        }

        $oauth2Menu = $item->getChild('微信OAuth2');
        if (null === $oauth2Menu) {
            return;
        }

        // 配置管理
        $oauth2Menu->addChild('OAuth2配置')
            ->setUri($this->linkGenerator->getCurdListPage(WechatOAuth2Config::class))
            ->setAttribute('icon', 'fas fa-cog')
        ;

        // 用户管理
        $oauth2Menu->addChild('OAuth2用户')
            ->setUri($this->linkGenerator->getCurdListPage(WechatOAuth2User::class))
            ->setAttribute('icon', 'fas fa-users')
        ;

        // 令牌管理
        $oauth2Menu->addChild('访问令牌')
            ->setUri($this->linkGenerator->getCurdListPage(OAuth2AccessToken::class))
            ->setAttribute('icon', 'fas fa-key')
        ;

        // 授权码管理
        $oauth2Menu->addChild('授权码')
            ->setUri($this->linkGenerator->getCurdListPage(OAuth2AuthorizationCode::class))
            ->setAttribute('icon', 'fas fa-ticket-alt')
        ;

        // 状态参数管理
        $oauth2Menu->addChild('状态参数')
            ->setUri($this->linkGenerator->getCurdListPage(WechatOAuth2State::class))
            ->setAttribute('icon', 'fas fa-random')
        ;
    }
}
