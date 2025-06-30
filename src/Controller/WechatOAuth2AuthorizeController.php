<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\WechatOAuth2Exception;
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\WechatOAuth2Service;

/**
 * 微信OAuth2授权控制器
 */
class WechatOAuth2AuthorizeController extends AbstractController
{
    public function __construct(
        private readonly WechatOAuth2Service $oauth2Service,
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    /**
     * 发起授权
     */
    #[Route(path: '/wechat/oauth2/authorize', name: 'wechat_oauth2_authorize', methods: ['GET'])]
    public function __invoke(Request $request): RedirectResponse
    {
        try {
            $sessionId = $request->getSession()->getId();
            $scope = $request->query->get('scope');
            
            $authUrl = $this->oauth2Service->generateAuthorizationUrl($sessionId, is_string($scope) ? $scope : null);
            
            return $this->redirect($authUrl);
        } catch (WechatOAuth2Exception $e) {
            if ($this->logger !== null) {
                $this->logger->error('Failed to generate authorization URL', [
                    'error' => $e->getMessage(),
                    'context' => $e->getContext(),
                ]);
            }
            
            throw $this->createNotFoundException('OAuth2 configuration not found');
        }
    }
}