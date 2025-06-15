<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Exception;

/**
 * 微信OAuth2基础异常类
 */
class WechatOAuth2Exception extends \RuntimeException
{
    private ?array $context = null;

    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null, ?array $context = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }
}