<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * WechatOAuth2Controller集成测试
 */
class WechatOAuth2ControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private Account $testAccount;

    public function testAuthorizeWithValidParameters(): void
    {
        $client = static::createClient();

        $client->request('GET', '/wechat/oauth2/authorize/' . $this->testAccount->getId(), [
            'redirect_uri' => 'https://example.com/callback',
            'scope' => 'snsapi_base',
            'state' => 'test_state',
        ]);

        // 应该重定向到微信授权页面
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $location = $client->getResponse()->headers->get('Location');
        $this->assertStringContainsString('open.weixin.qq.com/connect/oauth2/authorize', $location);
        $this->assertStringContainsString('appid=' . $this->testAccount->getAppId(), $location);
    }

    public function testAuthorizeWithMissingClientId(): void
    {
        $client = static::createClient();

        $client->request('GET', '/oauth2/authorize', [
            'redirect_uri' => 'https://example.com/callback',
            'response_type' => 'code',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('invalid_request', $response['error']);
    }

    public function testAuthorizeWithInvalidClientId(): void
    {
        $client = static::createClient();

        $client->request('GET', '/oauth2/authorize', [
            'client_id' => 'invalid_client_id',
            'redirect_uri' => 'https://example.com/callback',
            'response_type' => 'code',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('invalid_client', $response['error']);
    }

    public function testAuthorizeWithInvalidRedirectUri(): void
    {
        $client = static::createClient();

        $client->request('GET', '/oauth2/authorize', [
            'client_id' => 'test_client_id',
            'redirect_uri' => 'https://malicious.com/callback',
            'response_type' => 'code',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('invalid_redirect_uri', $response['error']);
    }

    public function testTokenEndpointWithValidAuthorizationCode(): void
    {
        $client = static::createClient();

        // 首先创建一个有效的授权码
        $authCode = new OAuth2AuthorizationCode();
        $authCode->setCode('test_auth_code_123');
        $authCode->setOpenid('test_openid');
        $authCode->setRedirectUri('https://example.com/callback');
        $authCode->setScopes('snsapi_base');
        $authCode->setState('test_state');
        $authCode->setWechatAccount($this->testAccount);
        $authCode->setExpiresAt(new \DateTime('+10 minutes'));

        $this->entityManager->persist($authCode);
        $this->entityManager->flush();

        $client->request('POST', '/oauth2/token', [
            'grant_type' => 'authorization_code',
            'code' => 'test_auth_code_123',
            'redirect_uri' => 'https://example.com/callback',
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('access_token', $response);
        $this->assertArrayHasKey('token_type', $response);
        $this->assertEquals('Bearer', $response['token_type']);
        $this->assertEquals('test_openid', $response['openid']);
        $this->assertEquals('snsapi_base', $response['scope']);
    }

    public function testTokenEndpointWithInvalidAuthorizationCode(): void
    {
        $client = static::createClient();

        $client->request('POST', '/oauth2/token', [
            'grant_type' => 'authorization_code',
            'code' => 'invalid_code',
            'redirect_uri' => 'https://example.com/callback',
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('invalid_grant', $response['error']);
    }

    public function testTokenEndpointWithInvalidClient(): void
    {
        $client = static::createClient();

        $client->request('POST', '/oauth2/token', [
            'grant_type' => 'authorization_code',
            'code' => 'test_code',
            'redirect_uri' => 'https://example.com/callback',
            'client_id' => 'test_client_id',
            'client_secret' => 'invalid_secret',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('invalid_client', $response['error']);
    }

    public function testUserInfoEndpointWithoutToken(): void
    {
        $client = static::createClient();

        $client->request('GET', '/oauth2/userinfo');

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('invalid_request', $response['error']);
    }

    public function testIntrospectEndpointWithoutToken(): void
    {
        $client = static::createClient();

        $client->request('POST', '/oauth2/introspect');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($response['active']);
    }

    public function testRevokeEndpointWithMissingParameters(): void
    {
        $client = static::createClient();

        $client->request('POST', '/oauth2/revoke', [
            'token' => 'some_token',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('invalid_request', $response['error']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $this->createTestData();
    }

    private function createTestData(): void
    {
        // 创建测试微信账号
        $this->testAccount = new Account();
        $this->testAccount->setAppId('test_app_id');
        $this->testAccount->setAppSecret('test_app_secret');
        $this->testAccount->setName('测试账号');
        $this->testAccount->setAccessToken('test_wechat_access_token');
        $this->testAccount->setAccessTokenExpireTime(new \DateTime('+2 hours'));
        $this->testAccount->setValid(true);

        $this->entityManager->persist($this->testAccount);
        $this->entityManager->flush();

        // 设置OAuth2字段
        $sql = '
            UPDATE wechat_official_account_account 
            SET oauth2_client_id = :clientId, 
                oauth2_client_secret = :clientSecret,
                oauth2_redirect_uris = :redirectUris,
                oauth2_scopes = :scopes
            WHERE id = :id
        ';

        $this->entityManager->getConnection()->executeStatement($sql, [
            'id' => $this->testAccount->getId(),
            'clientId' => 'test_client_id',
            'clientSecret' => 'test_client_secret',
            'redirectUris' => "https://example.com/callback\nhttps://test.com/callback",
            'scopes' => 'snsapi_base snsapi_userinfo'
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // 清理测试数据
        if ($this->testAccount) {
            try {
                $this->entityManager->remove($this->testAccount);
                $this->entityManager->flush();
            } catch (\Exception $e) {
                // 忽略清理错误
            }
        }
    }
}