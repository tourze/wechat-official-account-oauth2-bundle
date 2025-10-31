<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AuthorizationCode;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * @internal
 */
#[CoversClass(OAuth2AuthorizationCode::class)]
final class OAuth2AuthorizationCodeTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new OAuth2AuthorizationCode();
    }

    /** @return iterable<string, array{string, mixed}> */
    public static function propertiesProvider(): iterable
    {
        return [
            'id' => ['id', 1],
            'createTime' => ['createTime', new \DateTimeImmutable()],
            'updateTime' => ['updateTime', new \DateTimeImmutable()],
        ];
    }

    private OAuth2AuthorizationCode $authCode;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authCode = new OAuth2AuthorizationCode();
    }

    public function testBasicProperties(): void
    {
        $this->authCode->setCode('auth_code_123');
        $this->assertEquals('auth_code_123', $this->authCode->getCode());

        $this->authCode->setState('state_123');
        $this->assertEquals('state_123', $this->authCode->getState());

        $this->authCode->setScopes('snsapi_userinfo');
        $this->assertEquals('snsapi_userinfo', $this->authCode->getScopes());

        $this->authCode->setOpenId('test_openid');
        $this->assertEquals('test_openid', $this->authCode->getOpenId());

        $this->authCode->setUnionId('test_unionid');
        $this->assertEquals('test_unionid', $this->authCode->getUnionId());

        $this->authCode->setRedirectUri('https://example.com/callback');
        $this->assertEquals('https://example.com/callback', $this->authCode->getRedirectUri());
    }

    public function testExpirationTime(): void
    {
        $expireTime = new \DateTime('+10 minutes');
        $this->authCode->setExpiresAt($expireTime);
        $this->assertEquals($expireTime, $this->authCode->getExpiresAt());

        $this->assertFalse($this->authCode->isExpired());

        $expiredTime = new \DateTime('-10 minutes');
        $this->authCode->setExpiresAt($expiredTime);
        $this->assertTrue($this->authCode->isExpired());
    }

    public function testAccountRelation(): void
    {
        // 使用具体的 WechatOfficialAccountBundle\Entity\Account 类进行 Mock
        // 理由1：Account 是 Entity 类（数据传输对象），没有定义业务接口
        // 理由2：测试需要验证实体间的关联关系，Mock 可以避免依赖数据库
        // 理由3：Entity 类通常只包含简单的 getter/setter，Mock 是合理的测试方式
        // 替代方案评估：可以使用真实的 Account 实例，但会增加测试的数据依赖
        $account = $this->createMock(Account::class);
        $this->authCode->setWechatAccount($account);
        $this->assertSame($account, $this->authCode->getWechatAccount());
    }

    public function testUsageTracking(): void
    {
        $this->authCode->setUsed(false);
        $this->assertFalse($this->authCode->isUsed());

        $this->authCode->setUsed(true);
        $this->assertTrue($this->authCode->isUsed());
    }
}
