<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Service;

use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AccessToken;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AuthorizationCode;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2State;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2User;

/**
 * 微信OAuth2菜单服务
 */
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('微信OAuth2')) {
            $item->addChild('微信OAuth2');
        }

        $oauthMenu = $item->getChild('微信OAuth2');
        if (null === $oauthMenu) {
            return;
        }

        // OAuth2配置管理菜单
        $oauthMenu->addChild('OAuth2配置')
            ->setUri($this->linkGenerator->getCurdListPage(WechatOAuth2Config::class))
            ->setAttribute('icon', 'fas fa-cog')
        ;

        // OAuth2用户管理菜单
        $oauthMenu->addChild('OAuth2用户')
            ->setUri($this->linkGenerator->getCurdListPage(WechatOAuth2User::class))
            ->setAttribute('icon', 'fas fa-users')
        ;

        // OAuth2访问令牌菜单
        $oauthMenu->addChild('OAuth2令牌')
            ->setUri($this->linkGenerator->getCurdListPage(OAuth2AccessToken::class))
            ->setAttribute('icon', 'fas fa-key')
        ;

        // OAuth2授权码菜单
        $oauthMenu->addChild('OAuth2授权码')
            ->setUri($this->linkGenerator->getCurdListPage(OAuth2AuthorizationCode::class))
            ->setAttribute('icon', 'fas fa-qrcode')
        ;

        // OAuth2状态管理菜单
        $oauthMenu->addChild('OAuth2状态')
            ->setUri($this->linkGenerator->getCurdListPage(WechatOAuth2State::class))
            ->setAttribute('icon', 'fas fa-chart-line')
        ;
    }
}
