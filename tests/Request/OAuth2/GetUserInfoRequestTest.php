<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Request\OAuth2;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use HttpClientBundle\Tests\Request\RequestTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\GetUserInfoRequest;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * GetUserInfoRequest单元测试
 *
 * @internal
 */
#[CoversClass(GetUserInfoRequest::class)]
final class GetUserInfoRequestTest extends RequestTestCase
{
    private GetUserInfoRequest $request;

    public function testGetRequestPath(): void
    {
        $this->assertEquals('https://api.weixin.qq.com/sns/userinfo', $this->request->getRequestPath());
    }

    public function testGetRequestMethod(): void
    {
        $this->assertEquals('GET', $this->request->getRequestMethod());
    }

    public function testGetRequestOptions(): void
    {
        $account = new Account();
        $this->request->setAccount($account);
        $this->request->setOpenId('test_openid');
        $this->request->setAccessToken('test_access_token');
        $this->request->setLang('en');

        $options = $this->request->getRequestOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('query', $options);
        $this->assertEquals([
            'access_token' => 'test_access_token',
            'openid' => 'test_openid',
            'lang' => 'en',
        ], $options['query']);
    }

    public function testDefaultLanguage(): void
    {
        $this->assertEquals('zh_CN', $this->request->getLang());
    }

    public function testSettersAndGetters(): void
    {
        $account = new Account();
        $this->request->setAccount($account);
        $this->request->setOpenId('test_openid');
        $this->request->setAccessToken('test_token');
        $this->request->setLang('en');

        $this->assertEquals($account, $this->request->getAccount());
        $this->assertEquals('test_openid', $this->request->getOpenId());
        $this->assertEquals('test_token', $this->request->getAccessToken());
        $this->assertEquals('en', $this->request->getLang());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new GetUserInfoRequest();
    }
}
