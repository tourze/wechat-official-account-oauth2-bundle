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

    public function testAuthorizeRedirectsToWechat(): void
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
        $this->assertStringContainsString('scope=snsapi_base', $location);
    }

    public function testAuthorizeWithMissingRedirectUri(): void
    {
        $client = static::createClient();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('缺少 redirect_uri 参数');

        $client->request('GET', '/wechat/oauth2/authorize/' . $this->testAccount->getId());
    }

    public function testAuthorizeWithInvalidScope(): void
    {
        $client = static::createClient();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('无效的授权范围');

        $client->request('GET', '/wechat/oauth2/authorize/' . $this->testAccount->getId(), [
            'redirect_uri' => 'https://example.com/callback',
            'scope' => 'invalid_scope',
        ]);
    }

    public function testAuthorizeWithNonExistentAccount(): void
    {
        $client = static::createClient();

        $client->request('GET', '/wechat/oauth2/authorize/999999', [
            'redirect_uri' => 'https://example.com/callback',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCallbackWithMissingCode(): void
    {
        $client = static::createClient();

        $client->request('GET', '/wechat/oauth2/callback/' . $this->testAccount->getId(), [
            'redirect_uri' => base64_encode('https://example.com/callback'),
            'error' => 'access_denied',
        ]);

        // 应该重定向到原始回调地址，携带错误信息
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $location = $client->getResponse()->headers->get('Location');
        $this->assertStringContainsString('https://example.com/callback', $location);
        $this->assertStringContainsString('error=access_denied', $location);
    }

    public function testHelperBuildUrl(): void
    {
        $client = static::createClient();

        $client->request('GET', '/wechat/oauth2/helper/build-url/' . $this->testAccount->getId(), [
            'redirect_uri' => 'https://example.com/callback',
            'scope' => 'snsapi_userinfo',
            'state' => 'test_state',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('authorize_url', $response);
        $this->assertArrayHasKey('account_id', $response);
        $this->assertArrayHasKey('app_id', $response);
        $this->assertEquals($this->testAccount->getId(), $response['account_id']);
        $this->assertEquals($this->testAccount->getAppId(), $response['app_id']);
        $this->assertEquals('snsapi_userinfo', $response['scope']);
    }

    public function testHelperGetAccounts(): void
    {
        $client = static::createClient();

        $client->request('GET', '/wechat/oauth2/helper/accounts');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('accounts', $response);
        $this->assertArrayHasKey('count', $response);
        $this->assertGreaterThan(0, $response['count']);

        $firstAccount = $response['accounts'][0];
        $this->assertArrayHasKey('id', $firstAccount);
        $this->assertArrayHasKey('name', $firstAccount);
        $this->assertArrayHasKey('app_id', $firstAccount);
        $this->assertArrayHasKey('authorize_url_template', $firstAccount);
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