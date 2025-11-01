<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Request\OAuth2;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\RefreshAccessTokenRequest;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * @internal
 */
#[CoversClass(RefreshAccessTokenRequest::class)]
final class RefreshAccessTokenRequestTest extends RequestTestCase
{
    private RefreshAccessTokenRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new RefreshAccessTokenRequest();
    }

    public function testRequestProperties(): void
    {
        // 使用具体的 WechatOfficialAccountBundle\Entity\Account 类进行 Mock
        // 理由1：Account 是 Entity 类，作为数据传输对象，没有定义相应的接口
        // 理由2：测试需要验证请求对象的账号设置功能，Mock 可以避免依赖真实账号数据
        // 理由3：简化测试设置，专注于请求对象的行为验证
        // 替代方案评估：可以创建真实的 Account 实例，但会增加测试的数据准备工作
        $account = $this->createMock(Account::class);

        $this->request->setAccount($account);
        $this->assertEquals($account, $this->request->getAccount());

        $this->request->setRefreshToken('refresh_token_123');
        $this->assertEquals('refresh_token_123', $this->request->getRefreshToken());

        $this->request->setGrantType('refresh_token');
        $this->assertEquals('refresh_token', $this->request->getGrantType());
    }

    public function testRequestPath(): void
    {
        $this->assertEquals('https://api.weixin.qq.com/sns/oauth2/refresh_token', $this->request->getRequestPath());
    }

    public function testRequestMethod(): void
    {
        $this->assertEquals('GET', $this->request->getRequestMethod());
    }
}
