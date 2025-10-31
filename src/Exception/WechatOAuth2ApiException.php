<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Exception;

/**
 * 微信OAuth2 API调用异常
 */
class WechatOAuth2ApiException extends WechatOAuth2Exception
{
    /**
     * @param array<string, mixed>|null $apiResponse
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        private readonly ?string $apiUrl = null,
        private readonly ?array $apiResponse = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getApiUrl(): ?string
    {
        return $this->apiUrl;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getApiResponse(): ?array
    {
        return $this->apiResponse;
    }
}
