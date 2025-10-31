<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\WechatOAuth2Service;

/**
 * 微信OAuth2授权控制器
 */
final class WechatOAuth2AuthorizeController extends AbstractController
{
    public function __construct(
        private readonly WechatOAuth2Service $oauth2Service,
    ) {
    }

    /**
     * 授权入口
     */
    #[Route(path: '/authorize', name: 'wechat_oauth2_authorize', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $sessionId = $request->getSession()->getId();
        $scope = $request->query->get('scope');

        // Ensure $scope is string|null as expected by the method
        if (null !== $scope && !is_string($scope)) {
            $scope = (string) $scope;
        }

        $authorizationUrl = $this->oauth2Service->generateAuthorizationUrl($sessionId, $scope);

        return $this->redirect($authorizationUrl);
    }
}
