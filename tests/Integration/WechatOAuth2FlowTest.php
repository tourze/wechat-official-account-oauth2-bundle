<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Tourze\IntegrationTestKernel\IntegrationTestKernel;
use Tourze\WechatOfficialAccountOAuth2Bundle\WechatOfficialAccountOAuth2Bundle;

/**
 * 微信OAuth2流程集成测试
 */
class WechatOAuth2FlowTest extends WebTestCase
{
    protected static function createKernel(array $options = []): KernelInterface
    {
        $env = $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test';
        $debug = $options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true;

        return new IntegrationTestKernel($env, $debug, [
            WechatOfficialAccountOAuth2Bundle::class => ['all' => true],
        ]);
    }

    public function testAuthorizeRedirectsToWechat(): void
    {
        $client = static::createClient();
        $this->createTestOAuth2Config($client);

        $client->request('GET', '/wechat/oauth2/authorize');

        $response = $client->getResponse();

        // Should redirect to Wechat authorization URL
        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertStringStartsWith('https://open.weixin.qq.com/connect/oauth2/authorize', $response->headers->get('Location'));
    }

    private function createTestOAuth2Config($client): void
    {
        $container = $client->getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');

        // 创建微信公众号账户
        $account = new \WechatOfficialAccountBundle\Entity\Account();
        $account->setName('Test Account');
        $account->setAppId('test_app_id');
        $account->setAppSecret('test_app_secret');
        // 设置时间字段使用 DateTime 而不是 DateTimeImmutable
        $account->setCreateTime(new \DateTime());
        $account->setUpdateTime(new \DateTime());
        $entityManager->persist($account);

        // 创建OAuth2配置
        $config = new \Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config();
        $config->setAccount($account);
        $config->setScope('snsapi_base');
        $config->setValid(true);
        $config->setIsDefault(true);
        // 设置时间字段使用 DateTimeImmutable
        $config->setCreateTime(new \DateTimeImmutable());
        $config->setUpdateTime(new \DateTimeImmutable());
        $entityManager->persist($config);

        $entityManager->flush();
    }

    public function testAuthorizeWithScope(): void
    {
        $client = static::createClient();
        $this->createTestOAuth2Config($client);

        $client->request('GET', '/wechat/oauth2/authorize?scope=snsapi_userinfo');

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('scope=snsapi_userinfo', $location);
    }

    public function testCallbackWithoutParameters(): void
    {
        $client = static::createClient();

        $client->request('GET', '/wechat/oauth2/callback');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testCallbackWithError(): void
    {
        $client = static::createClient();
        $this->createTestOAuth2Config($client);

        $client->request('GET', '/wechat/oauth2/callback?error=access_denied&error_description=User%20denied%20access');

        $response = $client->getResponse();

        // Should render error template
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringContainsString('access_denied', $response->getContent());
    }

    public function testCallbackWithInvalidState(): void
    {
        $client = static::createClient();
        $this->createTestOAuth2Config($client);

        $client->request('GET', '/wechat/oauth2/callback?code=test_code&state=invalid_state');

        $response = $client->getResponse();

        // Should render error template
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringContainsString('callback_failed', $response->getContent());
    }

    /**
     * @group functional
     */
    public function testCompleteOAuth2Flow(): void
    {
        $this->markTestSkipped('This test requires a valid Wechat OAuth2 configuration and state setup');

        // This test would require:
        // 1. Setting up a valid config in the database
        // 2. Creating a valid state
        // 3. Mocking Wechat API responses
        // 4. Testing the complete flow
    }
}