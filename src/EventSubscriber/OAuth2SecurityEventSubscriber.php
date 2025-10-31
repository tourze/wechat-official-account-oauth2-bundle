<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * OAuth2安全事件订阅器
 * 负责处理OAuth2相关的安全检查
 */
class OAuth2SecurityEventSubscriber implements EventSubscriberInterface
{
    private const PROTECTED_PATHS = [
        '/oauth2/userinfo',
        '/oauth2/introspect',
    ];

    public function __construct(
        // private readonly WechatOAuth2Service $oauth2Service
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
        if (null === $accessToken) {
            $event->setResponse(new JsonResponse([
                'error' => 'invalid_token',
                'error_description' => 'Access token is required',
            ], 401));

            return;
        }

        // 这里需要重新实现验证逻辑
        // WechatOAuth2Service 的 validateAccessToken 方法需要 openid 参数
        // 但在这个场景下我们还不知道 openid，需要先从 token 中获取
        // 这里暂时注释掉，需要重新设计验证流程

        // TODO: 实现新的 token 验证逻辑
        $event->setResponse(new JsonResponse([
            'error' => 'not_implemented',
            'error_description' => 'Token validation not implemented',
        ], 501));
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

    private function extractAccessToken(Request $request): ?string
    {
        $authHeader = $request->headers->get('Authorization');
        if (is_string($authHeader) && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        $accessToken = $request->query->get('access_token');

        return is_string($accessToken) ? $accessToken : null;
    }
}
