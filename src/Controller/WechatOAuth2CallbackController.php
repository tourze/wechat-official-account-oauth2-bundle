<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\WechatOfficialAccountOAuth2Bundle\Exception\WechatOAuth2Exception;
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\WechatOAuth2Service;

/**
 * 微信OAuth2回调控制器
 */
class WechatOAuth2CallbackController extends AbstractController
{
    public function __construct(
        private readonly WechatOAuth2Service $oauth2Service,
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    /**
     * 处理回调
     */
    #[Route(path: '/wechat/oauth2/callback', name: 'wechat_oauth2_callback', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $code = $request->query->get('code');
        $state = $request->query->get('state');
        $error = $request->query->get('error');
        
        if ($error) {
            if ($this->logger !== null) {
                $this->logger->warning('OAuth2 authorization denied', [
                    'error' => $error,
                    'error_description' => $request->query->get('error_description'),
                ]);
            }
            
            return $this->render('@WechatOfficialAccountOAuth2/error.html.twig', [
                'error' => $error,
                'error_description' => $request->query->get('error_description'),
            ]);
        }
        
        if (!is_string($code) || !is_string($state)) {
            throw $this->createNotFoundException('Invalid callback parameters');
        }
        
        try {
            $user = $this->oauth2Service->handleCallback($code, $state);
            
            // 这里可以触发登录事件或执行其他业务逻辑
            if ($this->logger !== null) {
                $this->logger->info('Wechat OAuth2 login successful', [
                    'openid' => $user->getOpenid(),
                    'unionid' => $user->getUnionid(),
                ]);
            }
            
            // 默认重定向到首页，实际项目中可能需要根据业务逻辑重定向
            return $this->redirectToRoute('app_home', [
                'openid' => $user->getOpenid(),
            ]);
        } catch (WechatOAuth2Exception $e) {
            if ($this->logger !== null) {
                $this->logger->error('OAuth2 callback failed', [
                    'error' => $e->getMessage(),
                    'context' => $e->getContext(),
                ]);
            }
            
            return $this->render('@WechatOfficialAccountOAuth2/error.html.twig', [
                'error' => 'callback_failed',
                'error_description' => $e->getMessage(),
            ]);
        }
    }
}