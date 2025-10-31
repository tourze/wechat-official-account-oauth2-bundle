<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AccessToken;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * @internal
 */
#[CoversClass(OAuth2AccessToken::class)]
final class OAuth2AccessTokenTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new OAuth2AccessToken();
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

    private OAuth2AccessToken $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->token = new OAuth2AccessToken();
    }

    public function testBasicProperties(): void
    {
        $this->token->setAccessToken('test_token');
        $this->assertEquals('test_token', $this->token->getAccessToken());

        $this->token->setRefreshToken('refresh_token');
        $this->assertEquals('refresh_token', $this->token->getRefreshToken());

        $this->token->setOpenId('test_openid');
        $this->assertEquals('test_openid', $this->token->getOpenId());

        $this->token->setUnionId('test_unionid');
        $this->assertEquals('test_unionid', $this->token->getUnionId());

        $this->token->setScopes('snsapi_base');
        $this->assertEquals('snsapi_base', $this->token->getScopes());
    }

    public function testAccountRelation(): void
    {
        // 使用具体的 Account Entity 进行 Mock
        // 理由1：Account 是外部 Bundle 的 Entity 类，没有定义接口，Mock 具体类是唯一选择
        // 理由2：测试需要验证与微信公众号账户实体的关联关系，Mock 可以作为测试数据
        // 理由3：避免依赖实际数据库中的账户数据，确保测试的独立性
        $account = $this->createMock(Account::class);
        $this->token->setWechatAccount($account);
        $this->assertSame($account, $this->token->getWechatAccount());
    }

    public function testExpirationMethods(): void
    {
        $expiresAt = new \DateTime('+1 hour');
        $this->token->setAccessTokenExpiresAt($expiresAt);
        $this->assertEquals($expiresAt, $this->token->getAccessTokenExpiresAt());

        $refreshExpiresAt = new \DateTime('+30 days');
        $this->token->setRefreshTokenExpiresAt($refreshExpiresAt);
        $this->assertEquals($refreshExpiresAt, $this->token->getRefreshTokenExpiresAt());

        $this->assertFalse($this->token->isAccessTokenExpired());
        $this->assertFalse($this->token->isRefreshTokenExpired());
    }

    public function testValidation(): void
    {
        $this->token->setRevoked(false);
        $this->token->setAccessTokenExpiresAt(new \DateTime('+1 hour'));
        $this->assertTrue($this->token->isValid());

        $this->token->setRevoked(true);
        $this->assertFalse($this->token->isValid());
    }
}
