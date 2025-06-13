<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Unit\Request\OAuth2;

use PHPUnit\Framework\TestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Request\OAuth2\GetAccessTokenRequest;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * GetAccessTokenRequest单元测试
 */
class GetAccessTokenRequestTest extends TestCase
{
    private GetAccessTokenRequest $request;

    public function testGetRequestPath(): void
    {
        $this->assertEquals('https://api.weixin.qq.com/sns/oauth2/access_token', $this->request->getRequestPath());
    }

    public function testGetRequestMethod(): void
    {
        $this->assertEquals('GET', $this->request->getRequestMethod());
    }

    public function testGetRequestOptions(): void
    {
        $account = new Account();
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_app_secret');

        $this->request->setAccount($account);
        $this->request->setCode('test_code');
        $this->request->setGrantType('authorization_code');

        $options = $this->request->getRequestOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('query', $options);
        $this->assertEquals([
            'appid' => 'test_app_id',
            'secret' => 'test_app_secret',
            'code' => 'test_code',
            'grant_type' => 'authorization_code'
        ], $options['query']);
    }

    public function testDefaultGrantType(): void
    {
        $this->assertEquals('authorization_code', $this->request->getGrantType());
    }

    public function testSettersAndGetters(): void
    {
        $account = new Account();
        $this->request->setAccount($account);
        $this->request->setCode('test_code');
        $this->request->setGrantType('custom_grant');

        $this->assertEquals($account, $this->request->getAccount());
        $this->assertEquals('test_code', $this->request->getCode());
        $this->assertEquals('custom_grant', $this->request->getGrantType());
    }

    protected function setUp(): void
    {
        $this->request = new GetAccessTokenRequest();
    }
}