# WeChat Official Account OAuth2 Bundle

为 Symfony 应用提供微信公众号 OAuth2 授权功能的完整解决方案。

## 功能特性

- 🔐 标准 OAuth2 授权码流程
- 🎯 微信公众号用户授权
- 👤 获取用户基本信息和详细信息
- 🔄 访问令牌自动刷新
- 🗃️ 令牌管理和清理
- 🛡️ 安全的客户端验证
- 📊 EasyAdmin 后台管理集成
- 🧪 完整的单元测试和集成测试

## 安装配置

### 1. 添加 Bundle 到 Kernel

```php
// config/bundles.php
return [
    // ...
    Tourze\WechatOfficialAccountOAuth2Bundle\WechatOfficialAccountOAuth2Bundle::class => ['all' => true],
];
```

### 2. 数据库迁移

```bash
# 生成迁移文件
php bin/console doctrine:migrations:diff

# 执行迁移
php bin/console doctrine:migrations:migrate
```

### 3. 配置微信公众号

确保您已经配置了 `wechat-official-account-bundle` 中的微信公众号信息。

## 使用方法

### 1. 创建 OAuth2 应用

```bash
php bin/console oauth2:create-application 1 \
    --redirect-uri="https://example.com/callback" \
    --redirect-uri="https://example.com/auth/callback" \
    --scope="snsapi_userinfo"
```

**参数说明：**
- `1`: 微信公众号账号ID
- `--redirect-uri`: 授权回调地址（可指定多个）
- `--scope`: 默认授权范围

### 2. OAuth2 授权流程

#### 第一步：用户授权

引导用户访问授权页面：
```
GET /oauth2/authorize?client_id=CLIENT_ID&redirect_uri=REDIRECT_URI&scope=snsapi_base&state=STATE&response_type=code
```

**参数说明：**
- `client_id`: OAuth2 客户端 ID
- `redirect_uri`: 授权回调地址
- `scope`: 授权范围（`snsapi_base` 或 `snsapi_userinfo`）
- `state`: 防CSRF攻击的随机字符串
- `response_type`: 固定值 `code`

#### 第二步：获取访问令牌

使用授权码换取访问令牌：
```bash
POST /oauth2/token
Content-Type: application/x-www-form-urlencoded

grant_type=authorization_code&code=AUTHORIZATION_CODE&redirect_uri=REDIRECT_URI&client_id=CLIENT_ID&client_secret=CLIENT_SECRET
```

**响应示例：**
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

#### 第三步：获取用户信息

使用访问令牌获取用户信息：
```bash
# 查询参数方式
GET /oauth2/userinfo?access_token=ACCESS_TOKEN

# 请求头方式
GET /oauth2/userinfo
Authorization: Bearer ACCESS_TOKEN
```

#### 第四步：刷新访问令牌

```bash
POST /oauth2/refresh
Content-Type: application/x-www-form-urlencoded

grant_type=refresh_token&refresh_token=REFRESH_TOKEN&client_id=CLIENT_ID&client_secret=CLIENT_SECRET
```

### 3. 令牌管理

#### 撤销令牌

```bash
POST /oauth2/revoke
Content-Type: application/x-www-form-urlencoded

token=ACCESS_TOKEN&client_id=CLIENT_ID&client_secret=CLIENT_SECRET
```

#### 令牌验证

```bash
POST /oauth2/introspect
Content-Type: application/x-www-form-urlencoded

token=ACCESS_TOKEN
```

### 4. 定时清理

建议设置定时任务清理过期的令牌：
```bash
# 每小时清理过期令牌
0 * * * * php /path/to/your/app/bin/console oauth2:cleanup

# 预览清理（不实际删除）
php bin/console oauth2:cleanup --dry-run

# 清理指定时间前的令牌
php bin/console oauth2:cleanup --before="-1 week"
```

## API 端点

| 端点 | 方法 | 描述 |
|------|------|------|
| `/oauth2/authorize` | GET | 用户授权页面 |
| `/oauth2/callback` | GET | 微信授权回调 |
| `/oauth2/token` | POST | 获取访问令牌 |
| `/oauth2/refresh` | POST | 刷新访问令牌 |
| `/oauth2/revoke` | POST | 撤销令牌 |
| `/oauth2/userinfo` | GET/POST | 获取用户信息 |
| `/oauth2/introspect` | POST | 令牌验证 |

## 授权范围

- `snsapi_base`: 静默授权，只能获取用户openid
- `snsapi_userinfo`: 需要用户手动同意，可获取用户基本信息

## 错误处理

所有错误响应遵循 OAuth2 标准格式：

```json
{
  "error": "invalid_request",
  "error_description": "请求参数错误"
}
```

**常见错误码：**
- `invalid_request`: 请求参数错误
- `invalid_client`: 客户端认证失败
- `invalid_grant`: 授权码无效或过期
- `invalid_token`: 访问令牌无效
- `unsupported_grant_type`: 不支持的授权类型

## 安全建议

1. 🔒 妥善保管 Client Secret，避免泄露
2. 🌐 仅在 HTTPS 环境下使用 OAuth2 功能
3. ✅ 严格验证 redirect_uri 参数
4. ⏰ 合理设置令牌过期时间
5. 🧹 定期清理过期的授权码和令牌

## 开发和测试

### 运行测试

```bash
# 运行所有测试
vendor/bin/phpunit packages/wechat-official-account-oauth2-bundle/tests/

# 运行单元测试
vendor/bin/phpunit packages/wechat-official-account-oauth2-bundle/tests/Unit/

# 运行集成测试
vendor/bin/phpunit packages/wechat-official-account-oauth2-bundle/tests/Integration/
```

### 代码质量检查

```bash
# PHPStan 静态分析
vendor/bin/phpstan analyse packages/wechat-official-account-oauth2-bundle/src/

# PHP CS Fixer 代码格式化
vendor/bin/php-cs-fixer fix packages/wechat-official-account-oauth2-bundle/src/
```

## 服务类使用

### OAuth2AuthorizationService

```php
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\OAuth2AuthorizationService;

// 构建微信授权URL
$authUrl = $authorizationService->buildWechatAuthUrl($account, 'snsapi_userinfo', $redirectUri);

// 通过授权码获取用户信息
$userInfo = $authorizationService->getUserInfoByCode($account, $code);

// 创建内部访问令牌
$accessToken = $authorizationService->exchangeCodeForToken($code, $redirectUri, $account);
```

### OAuth2UserInfoService

```php
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\OAuth2UserInfoService;

// 获取用户信息（通过内部访问令牌）
$userInfo = $userInfoService->getUserInfo($oAuth2AccessToken);
```

### WechatOAuth2Service

```php
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\WechatOAuth2Service;

// 构建微信授权URL
$authUrl = $wechatOAuth2Service->buildAuthorizationUrl($account, $redirectUri, 'snsapi_base');

// 获取微信访问令牌
$tokenData = $wechatOAuth2Service->getAccessTokenByCode($account, $code);

// 验证微信访问令牌
$isValid = $wechatOAuth2Service->validateAccessToken($account, $accessToken, $openid);
```

## 依赖关系

本 Bundle 依赖以下组件：

- `wechat-official-account-bundle`: 微信公众号基础功能
- `http-client-bundle`: HTTP 客户端封装
- `symfony-routing-auto-loader-bundle`: 自动路由加载

## 许可证

本项目采用 MIT 许可证。详情请查看 [LICENSE](LICENSE) 文件。

## 贡献

欢迎提交 Issue 和 Pull Request 来改进这个项目。

---

📝 **注意**: 在生产环境中使用前，请确保已经充分测试所有功能，并按照微信官方文档配置相关参数。