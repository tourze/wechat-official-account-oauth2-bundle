<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Integration\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2ConfigRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2StateRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2UserRepository;
use Tourze\WechatOfficialAccountOAuth2Bundle\Service\WechatOAuth2Service;
use WechatOfficialAccountBundle\Service\OfficialAccountClient;

class WechatOAuth2ServiceTest extends KernelTestCase
{
    private WechatOAuth2Service $service;
    private OfficialAccountClient|MockObject $wechatClient;
    private WechatOAuth2ConfigRepository|MockObject $configRepository;
    private WechatOAuth2StateRepository|MockObject $stateRepository;
    private WechatOAuth2UserRepository|MockObject $userRepository;
    private EntityManagerInterface|MockObject $entityManager;
    private UrlGeneratorInterface|MockObject $urlGenerator;
    private LoggerInterface|MockObject $logger;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->wechatClient = $this->createMock(OfficialAccountClient::class);
        $this->configRepository = $this->createMock(WechatOAuth2ConfigRepository::class);
        $this->stateRepository = $this->createMock(WechatOAuth2StateRepository::class);
        $this->userRepository = $this->createMock(WechatOAuth2UserRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        
        $this->service = new WechatOAuth2Service(
            $this->wechatClient,
            $this->configRepository,
            $this->stateRepository,
            $this->userRepository,
            $this->entityManager,
            $this->urlGenerator,
            $this->logger
        );
    }

    public function testGenerateAuthorizationUrl(): void
    {
        $config = $this->createMock(WechatOAuth2Config::class);
        $config->method('getAppId')->willReturn('test_app_id');
        $config->method('getScope')->willReturn('snsapi_base');
        
        $this->configRepository->expects($this->once())
            ->method('findValidConfig')
            ->willReturn($config);
            
        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->willReturn('https://example.com/callback');
            
        $this->entityManager->expects($this->once())
            ->method('persist');
            
        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->service->generateAuthorizationUrl('session_id', 'snsapi_userinfo');

        $this->assertStringContainsString('https://open.weixin.qq.com/connect/oauth2/authorize', $result);
        $this->assertStringContainsString('appid=test_app_id', $result);
        $this->assertStringContainsString('scope=snsapi_userinfo', $result);
    }
}