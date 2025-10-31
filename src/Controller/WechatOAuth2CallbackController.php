<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\WechatOAuth2Service;

/**
 * 微信OAuth2回调控制器
 */
final class WechatOAuth2CallbackController extends AbstractController
{
    public function __construct(
        private readonly WechatOAuth2Service $oauth2Service,
    ) {
    }

    /**
     * 回调处理
     */
    #[Route(path: '/callback', name: 'wechat_oauth2_callback', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $code = $request->query->get('code');
        $state = $request->query->get('state');

        if (!is_string($code) || !is_string($state) || '' === $code || '' === $state) {
            throw $this->createNotFoundException('Missing required parameters');
        }

        try {
            $user = $this->oauth2Service->handleCallback($code, $state);

            // 这里可以根据业务需求处理用户登录或注册
            // 例如：将用户信息存储到session中，然后重定向到首页

            return $this->redirectToRoute('app_home');
        } catch (\Exception $e) {
            // 处理OAuth2错误
            return $this->render('@WechatOfficialAccountOAuth2/oauth2_error.html.twig', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
