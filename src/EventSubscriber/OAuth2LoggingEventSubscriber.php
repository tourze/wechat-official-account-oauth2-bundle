<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\EventSubscriber;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * OAuth2日志事件订阅器
 * 负责记录OAuth2相关的操作日志
 */
#[WithMonologChannel(channel: 'wechat_official_account_o_auth2')]
class OAuth2LoggingEventSubscriber implements EventSubscriberInterface
{
    private const OAUTH2_PATHS = [
        '/oauth2/authorize',
        '/oauth2/token',
        '/oauth2/userinfo',
        '/oauth2/revoke',
        '/oauth2/introspect',
        '/oauth2/wechat/callback',
    ];

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 5],
            KernelEvents::RESPONSE => ['onKernelResponse', 5],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        if (!$this->isOAuth2Path($path)) {
            return;
        }

        $clientId = $this->extractClientId($request);
        $grantType = $request->request->get('grant_type') ?? $request->query->get('grant_type');
        $responseType = $request->query->get('response_type');

        $this->logger->info('OAuth2 request received', [
            'path' => $path,
            'method' => $request->getMethod(),
            'client_id' => $clientId,
            'grant_type' => $grantType,
            'response_type' => $responseType,
            'ip_address' => $request->getClientIp(),
            'user_agent' => $request->headers->get('User-Agent'),
        ]);
    }

    private function isOAuth2Path(string $path): bool
    {
        foreach (self::OAUTH2_PATHS as $oauth2Path) {
            if (str_starts_with($path, $oauth2Path)) {
                return true;
            }
        }

        return false;
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $path = $request->getPathInfo();

        if (!$this->isOAuth2Path($path)) {
            return;
        }

        $statusCode = $response->getStatusCode();
        $clientId = $this->extractClientId($request);

        $this->logResponse($request, $response, $statusCode, $clientId);

        if ($statusCode >= 400) {
            $this->logErrorResponse($request, $response, $statusCode, $clientId);
        }
    }

    private function logResponse(Request $request, Response $response, int $statusCode, ?string $clientId): void
    {
        $logLevel = $statusCode >= 400 ? 'warning' : 'info';
        $this->logger->log($logLevel, 'OAuth2 response sent', [
            'path' => $request->getPathInfo(),
            'method' => $request->getMethod(),
            'status_code' => $statusCode,
            'client_id' => $clientId,
            'ip_address' => $request->getClientIp(),
        ]);
    }

    private function logErrorResponse(Request $request, Response $response, int $statusCode, ?string $clientId): void
    {
        $content = $response->getContent();
        if (!is_string($content)) {
            return;
        }

        $errorData = json_decode($content, true);
        $this->logger->error('OAuth2 error response', [
            'path' => $request->getPathInfo(),
            'status_code' => $statusCode,
            'client_id' => $clientId,
            'error' => is_array($errorData) && isset($errorData['error']) ? $errorData['error'] : null,
            'error_description' => is_array($errorData) && isset($errorData['error_description']) ? $errorData['error_description'] : null,
            'ip_address' => $request->getClientIp(),
        ]);
    }

    private function extractClientId(Request $request): ?string
    {
        $clientId = $request->request->get('client_id') ?? $request->query->get('client_id');

        return is_string($clientId) ? $clientId : null;
    }
}
