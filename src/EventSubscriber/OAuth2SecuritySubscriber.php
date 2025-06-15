<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\WechatOAuth2Service;

/**
 * OAuth2安全事件订阅器
 * 负责处理OAuth2相关的安全检查
 */
class OAuth2SecuritySubscriber implements EventSubscriberInterface
{
    private const PROTECTED_PATHS = [
        '/oauth2/userinfo',
        '/oauth2/introspect'
    ];

    public function __construct(
        private WechatOAuth2Service $oauth2Service
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // 检查是否需要OAuth2保护
        if (!$this->isProtectedPath($path)) {
            return;
        }

        // 提取访问令牌
        $accessToken = $this->extractAccessToken($request);
        if (!$accessToken) {
            $event->setResponse(new JsonResponse([
                'error' => 'invalid_token',
                'error_description' => 'Access token is required'
            ], 401));
            return;
        }

        // 验证访问令牌
        $tokenEntity = $this->oauth2Service->validateAccessToken($accessToken);
        if (!$tokenEntity) {
            $event->setResponse(new JsonResponse([
                'error' => 'invalid_token',
                'error_description' => 'Invalid or expired access token'
            ], 401));
            return;
        }

        // 将令牌信息存储到请求属性中
        $request->attributes->set('oauth2_token', $tokenEntity);
        $request->attributes->set('oauth2_user_openid', $tokenEntity->getOpenid());
        $request->attributes->set('oauth2_client_id', $tokenEntity->getApplication()->getClientId());
    }

    private function isProtectedPath(string $path): bool
    {
        foreach (self::PROTECTED_PATHS as $protectedPath) {
            if (str_starts_with($path, $protectedPath)) {
                return true;
            }
        }

        return false;
    }

    private function extractAccessToken($request): ?string
    {
        $authHeader = $request->headers->get('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        return $request->query->get('access_token');
    }
}