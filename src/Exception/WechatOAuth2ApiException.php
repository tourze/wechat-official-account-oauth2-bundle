<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Exception;

/**
 * 微信OAuth2 API调用异常
 */
class WechatOAuth2ApiException extends WechatOAuth2Exception
{
    private ?string $apiUrl = null;
    private ?array $apiResponse = null;

    public function __construct(
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null,
        ?string $apiUrl = null,
        ?array $apiResponse = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->apiUrl = $apiUrl;
        $this->apiResponse = $apiResponse;
    }

    public function getApiUrl(): ?string
    {
        return $this->apiUrl;
    }

    public function getApiResponse(): ?array
    {
        return $this->apiResponse;
    }
}