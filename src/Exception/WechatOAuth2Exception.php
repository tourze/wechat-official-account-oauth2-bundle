<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Exception;

/**
 * 微信OAuth2基础异常类
 */
class WechatOAuth2Exception extends \RuntimeException
{
    /** @var array<string, mixed>|null */
    private ?array $context = null;

    /**
     * @param array<string, mixed>|null $context
     */
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null, ?array $context = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getContext(): ?array
    {
        return $this->context;
    }
}