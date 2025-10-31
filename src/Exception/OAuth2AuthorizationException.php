<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Exception;

/**
 * OAuth2授权异常
 */
class OAuth2AuthorizationException extends WechatOAuth2Exception
{
    public const INVALID_AUTHORIZATION_CODE = 'invalid_authorization_code';
    public const EXPIRED_AUTHORIZATION_CODE = 'expired_authorization_code';
    public const REDIRECT_URI_MISMATCH = 'redirect_uri_mismatch';
    public const INVALID_REFRESH_TOKEN = 'invalid_refresh_token';
    public const EXPIRED_REFRESH_TOKEN = 'expired_refresh_token';
    public const INVALID_TOKEN = 'invalid_token';
    public const INVALID_CODE = 'invalid_code';
}
