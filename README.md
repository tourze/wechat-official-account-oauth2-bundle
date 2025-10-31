# WeChat Official Account OAuth2 Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![PHP Version](https://img.shields.io/packagist/php-v/tourze/wechat-official-account-oauth2-bundle.svg?style=flat-square)](
https://packagist.org/packages/tourze/wechat-official-account-oauth2-bundle)
[![Latest Version](https://img.shields.io/packagist/v/tourze/wechat-official-account-oauth2-bundle.svg?style=flat-square)](
https://packagist.org/packages/tourze/wechat-official-account-oauth2-bundle)
[![License](https://img.shields.io/packagist/l/tourze/wechat-official-account-oauth2-bundle.svg?style=flat-square)](
https://packagist.org/packages/tourze/wechat-official-account-oauth2-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/wechat-official-account-oauth2-bundle.svg?style=flat-square)](
https://packagist.org/packages/tourze/wechat-official-account-oauth2-bundle)
[![Coverage](https://img.shields.io/badge/coverage-90%25-green.svg?style=flat-square)](#)

A complete OAuth2 authorization solution for WeChat Official Accounts in Symfony applications.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
- [API Endpoints](#api-endpoints)
- [Authorization Scopes](#authorization-scopes)
- [Error Handling](#error-handling)
- [Security Recommendations](#security-recommendations)
- [Development and Testing](#development-and-testing)
- [Service Classes](#service-classes)
- [Dependencies](#dependencies)
- [License](#license)
- [Contributing](#contributing)

## Features

- 🔐 Standard OAuth2 authorization code flow
- 🎯 WeChat Official Account user authorization
- 👤 Get user basic and detailed information
- 🔄 Automatic access token refresh
- 🗃️ Token management and cleanup
- 🛡️ Secure client authentication
- 📊 EasyAdmin backend integration
- 🧪 Complete unit and integration tests

## Installation

### 1. Install via Composer

```bash
composer require tourze/wechat-official-account-oauth2-bundle
```

### 2. Add Bundle to Kernel

```php
// config/bundles.php
return [
    // ...
    Tourze\WechatOfficialAccountOAuth2Bundle\WechatOfficialAccountOAuth2Bundle::class => ['all' => true],
];
```

### 3. Database Migration

```bash
# Generate migration files
php bin/console doctrine:migrations:diff

# Execute migrations
php bin/console doctrine:migrations:migrate
```

### 4. Configure WeChat Official Account

Ensure you have configured your WeChat Official Account information in `wechat-official-account-bundle`.

## Usage

### 1. Create OAuth2 Application

```bash
php bin/console oauth2:create-application 1 \
    --redirect-uri="https://example.com/callback" \
    --redirect-uri="https://example.com/auth/callback" \
    --scope="snsapi_userinfo"
```

**Parameters:**
- `1`: WeChat Official Account ID
- `--redirect-uri`: Authorization callback URL (multiple allowed)
- `--scope`: Default authorization scope

### 2. OAuth2 Authorization Flow

#### Step 1: User Authorization

Redirect users to the authorization page:
```text
GET /oauth2/authorize?client_id=CLIENT_ID&redirect_uri=REDIRECT_URI&scope=snsapi_base&state=STATE&response_type=code
```

**Parameters:**
- `client_id`: OAuth2 client ID
- `redirect_uri`: Authorization callback URL
- `scope`: Authorization scope (`snsapi_base` or `snsapi_userinfo`)
- `state`: Random string for CSRF protection
- `response_type`: Fixed value `code`

#### Step 2: Get Access Token

Exchange authorization code for access token:
```bash
POST /oauth2/token
Content-Type: application/x-www-form-urlencoded

grant_type=authorization_code&code=AUTHORIZATION_CODE&redirect_uri=REDIRECT_URI&client_id=CLIENT_ID&client_secret=CLIENT_SECRET
```

**Response Example:**
```json
{
  "access_token": "AT_...",
  "token_type": "Bearer",
  "expires_in": 7200,
  "refresh_token": "RT_...",
  "scope": "snsapi_base",
  "openid": "o6_bmjrPTlm6_2sgVt7hMZOPfL2M",
  "unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
}
```

#### Step 3: Get User Information

Get user information using access token:
```bash
# Query parameter method
GET /oauth2/userinfo?access_token=ACCESS_TOKEN

# Header method
GET /oauth2/userinfo
Authorization: Bearer ACCESS_TOKEN
```

#### Step 4: Refresh Access Token

```bash
POST /oauth2/refresh
Content-Type: application/x-www-form-urlencoded

grant_type=refresh_token&refresh_token=REFRESH_TOKEN&client_id=CLIENT_ID&client_secret=CLIENT_SECRET
```

### 3. Token Management

#### Revoke Token

```bash
POST /oauth2/revoke
Content-Type: application/x-www-form-urlencoded

token=ACCESS_TOKEN&client_id=CLIENT_ID&client_secret=CLIENT_SECRET
```

#### Token Introspection

```bash
POST /oauth2/introspect
Content-Type: application/x-www-form-urlencoded

token=ACCESS_TOKEN
```

### 4. Console Commands

#### OAuth2 Configuration Command

Configure OAuth2 settings for WeChat Official Account:

```bash
# Configure OAuth2 settings
php bin/console wechat:oauth2:configure <account-id> [--scope=SCOPE] [--remark=REMARK]
```

**Parameters:**
- `account-id`: WeChat Official Account ID
- `--scope`: Authorization scope (optional, default: snsapi_base)
- `--remark`: Configuration remark (optional)

**Examples:**
```bash
# Basic configuration
php bin/console wechat:oauth2:configure 1

# Configure user info authorization
php bin/console wechat:oauth2:configure 1 --scope=snsapi_userinfo --remark="User info authorization config"
```

#### Refresh Token Command

Automatically refresh expiring access tokens:

```bash
# Refresh all expiring tokens (default: within 2 hours)
php bin/console wechat:oauth2:refresh-tokens

# Refresh tokens expiring within 1 hour
php bin/console wechat:oauth2:refresh-tokens --expires-within="1 hour"

# Force refresh all tokens
php bin/console wechat:oauth2:refresh-tokens --force
```

**Parameters:**
- `--expires-within`: Tokens expiring within specified time range (optional, default: 2 hours)
- `--force`: Force refresh all tokens regardless of expiration (optional)

### 5. Scheduled Cleanup

It's recommended to set up a cron job to clean up expired tokens:
```bash
# Clean up expired tokens every hour
0 * * * * php /path/to/your/app/bin/console oauth2:cleanup

# Preview cleanup (without deletion)
php bin/console oauth2:cleanup --dry-run

# Clean up tokens before specified time
php bin/console oauth2:cleanup --before="-1 week"
```

## API Endpoints

| Endpoint | Method | Description |
| `/oauth2/authorize` | GET | User authorization page |
| `/oauth2/callback` | GET | WeChat authorization callback |
| `/oauth2/token` | POST | Get access token |
| `/oauth2/refresh` | POST | Refresh access token |
| `/oauth2/revoke` | POST | Revoke token |
| `/oauth2/userinfo` | GET/POST | Get user information |
| `/oauth2/introspect` | POST | Token introspection |

## Authorization Scopes

- `snsapi_base`: Silent authorization, only get user's openid
- `snsapi_userinfo`: Requires user's manual consent, can get user's basic information

## Error Handling

All error responses follow the OAuth2 standard format:

```json
{
  "error": "invalid_request",
  "error_description": "Invalid request parameters"
}
```

**Common Error Codes:**
- `invalid_request`: Invalid request parameters
- `invalid_client`: Client authentication failed
- `invalid_grant`: Invalid or expired authorization code
- `invalid_token`: Invalid access token
- `unsupported_grant_type`: Unsupported grant type

## Security Recommendations

1. 🔒 Keep Client Secret secure and avoid leakage
2. 🌐 Only use OAuth2 features in HTTPS environment
3. ✅ Strictly validate redirect_uri parameter
4. ⏰ Set reasonable token expiration times
5. 🧹 Regularly clean up expired authorization codes and tokens

## Development and Testing

### Running Tests

```bash
# Run all tests
vendor/bin/phpunit packages/wechat-official-account-oauth2-bundle/tests/

# Run unit tests (Entity and basic functionality tests)
vendor/bin/phpunit packages/wechat-official-account-oauth2-bundle/tests/Entity/
vendor/bin/phpunit packages/wechat-official-account-oauth2-bundle/tests/Exception/

# Run specific test file
vendor/bin/phpunit packages/wechat-official-account-oauth2-bundle/tests/Entity/OAuth2AccessTokenTest.php
```

> **Note**: Some integration tests may experience conflicts due to Symfony configuration class loading in the same PHP process.
> This is a known issue tracked in [Issue #774](https://github.com/tourze/php-monorepo/issues/774).
> Unit tests (Entity, Exception tests) run without issues.

### Code Quality Checks

```bash
# PHPStan static analysis
vendor/bin/phpstan analyse packages/wechat-official-account-oauth2-bundle/src/

# PHP CS Fixer code formatting
vendor/bin/php-cs-fixer fix packages/wechat-official-account-oauth2-bundle/src/
```

## Service Classes

### OAuth2AuthorizationService

```php
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\OAuth2AuthorizationService;

// Build WeChat authorization URL
$authUrl = $authorizationService->buildWechatAuthUrl($account, 'snsapi_userinfo', $redirectUri);

// Get user info by authorization code
$userInfo = $authorizationService->getUserInfoByCode($account, $code);

// Create internal access token
$accessToken = $authorizationService->exchangeCodeForToken($code, $redirectUri, $account);
```

### OAuth2UserInfoService

```php
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\OAuth2UserInfoService;

// Get user info (via internal access token)
$userInfo = $userInfoService->getUserInfo($oAuth2AccessToken);
```

### WechatOAuth2Service

```php
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\WechatOAuth2Service;

// Build WeChat authorization URL
$authUrl = $wechatOAuth2Service->buildAuthorizationUrl($account, $redirectUri, 'snsapi_base');

// Get WeChat access token
$tokenData = $wechatOAuth2Service->getAccessTokenByCode($account, $code);

// Validate WeChat access token
$isValid = $wechatOAuth2Service->validateAccessToken($account, $accessToken, $openid);
```

## Dependencies

This bundle depends on the following components:

- `wechat-official-account-bundle`: WeChat Official Account basic functionality
- `http-client-bundle`: HTTP client wrapper
- `symfony-routing-auto-loader-bundle`: Automatic route loading
- `doctrine-snowflake-bundle`: Snowflake ID generation
- `doctrine-indexed-bundle`: Database indexing support
- `doctrine-timestamp-bundle`: Automatic timestamp handling
- `doctrine-track-bundle`: Entity tracking features
- `doctrine-user-bundle`: User management features

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit issues and pull requests.

---

📝 **Note**: Before using in production, ensure all features are thoroughly tested and configured according to 
WeChat's official documentation.