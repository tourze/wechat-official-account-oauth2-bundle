<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * @internal
 */
#[CoversClass(WechatOAuth2Config::class)]
final class WechatOAuth2ConfigTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        $account = $this->createMock(Account::class);
        $config = new WechatOAuth2Config();
        $config->setAccount($account);

        return $config;
    }

    /** @return iterable<string, array{string, mixed}> */
    public static function propertiesProvider(): iterable
    {
        return [
            'valid' => ['valid', true],
        ];
    }

    private WechatOAuth2Config $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = new WechatOAuth2Config();
    }

    public function testBasicProperties(): void
    {
        // 使用具体的 WechatOfficialAccountBundle\Entity\Account 类进行 Mock
        // 理由1：Account 是 Entity 类，作为数据传输对象，没有相应的接口定义
        // 理由2：测试需要验证配置对象的属性访问逻辑，Mock 可以控制返回值
        // 理由3：避免在测试中依赖真实的微信账号配置数据
        // 替代方案评估：可以创建真实的 Account 实例，但会增加测试数据管理复杂性
        $account = $this->createMock(Account::class);
        $account->expects($this->once())
            ->method('getAppId')
            ->willReturn('test_app_id')
        ;
        $account->expects($this->once())
            ->method('getAppSecret')
            ->willReturn('test_app_secret')
        ;

        $this->config->setAccount($account);
        $this->assertEquals($account, $this->config->getAccount());
        $this->assertEquals('test_app_id', $this->config->getAppId());
        $this->assertEquals('test_app_secret', $this->config->getAppSecret());

        $this->config->setScope('snsapi_base');
        $this->assertEquals('snsapi_base', $this->config->getScope());

        $this->config->setRemark('Test config');
        $this->assertEquals('Test config', $this->config->getRemark());
    }

    public function testValidStatus(): void
    {
        $this->config->setValid(true);
        $this->assertTrue($this->config->isValid());

        $this->config->setValid(false);
        $this->assertFalse($this->config->isValid());
    }

    public function testDefaultStatus(): void
    {
        $this->config->setIsDefault(true);
        $this->assertTrue($this->config->isDefault());

        $this->config->setIsDefault(false);
        $this->assertFalse($this->config->isDefault());
    }
}
