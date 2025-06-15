<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2UserRepository;

/**
 * 微信OAuth2用户实体
 * 存储通过OAuth2获取的微信用户信息
 */
#[ORM\Entity(repositoryClass: WechatOAuth2UserRepository::class)]
#[ORM\Table(name: 'wechat_oauth2_user', options: ['comment' => '微信OAuth2用户信息'])]
#[ORM\Index(columns: ['access_token_expires_time'], name: 'wechat_oauth2_user_idx_token_expires')]
#[ORM\UniqueConstraint(columns: ['openid', 'config_id'])]
class WechatOAuth2User implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => '主键ID'])]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: SnowflakeIdGenerator::class)]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: WechatOAuth2Config::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private WechatOAuth2Config $config;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 64, options: ['comment' => '用户OpenID'])]
    private string $openid;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 64, nullable: true, options: ['comment' => '用户UnionID'])]
    private ?string $unionid = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '用户昵称'])]
    private ?string $nickname = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '用户性别(0:未知,1:男性,2:女性)'])]
    private ?int $sex = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '用户省份'])]
    private ?string $province = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '用户城市'])]
    private ?string $city = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '用户国家'])]
    private ?string $country = null;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true, options: ['comment' => '用户头像URL'])]
    private ?string $headimgurl = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '用户特权信息'])]
    private ?array $privilege = null;

    #[ORM\Column(type: Types::STRING, length: 512, options: ['comment' => '访问令牌'])]
    private string $accessToken;

    #[ORM\Column(type: Types::STRING, length: 512, options: ['comment' => '刷新令牌'])]
    private string $refreshToken;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '令牌有效期(秒)'])]
    private int $expiresIn;

    #[IndexColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['comment' => '访问令牌过期时间'])]
    private \DateTimeInterface $accessTokenExpiresTime;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '授权作用域'])]
    private ?string $scope = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '原始用户数据'])]
    private ?array $rawData = null;

    public function __toString(): string
    {
        return sprintf('WechatOAuth2User[%s](%s)', $this->id, $this->nickname ?? $this->openid);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getConfig(): WechatOAuth2Config
    {
        return $this->config;
    }

    public function setConfig(WechatOAuth2Config $config): static
    {
        $this->config = $config;
        return $this;
    }

    public function getOpenid(): string
    {
        return $this->openid;
    }

    public function setOpenid(string $openid): static
    {
        $this->openid = $openid;
        return $this;
    }

    public function getUnionid(): ?string
    {
        return $this->unionid;
    }

    public function setUnionid(?string $unionid): static
    {
        $this->unionid = $unionid;
        return $this;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): static
    {
        $this->nickname = $nickname;
        return $this;
    }

    public function getSex(): ?int
    {
        return $this->sex;
    }

    public function setSex(?int $sex): static
    {
        $this->sex = $sex;
        return $this;
    }

    public function getProvince(): ?string
    {
        return $this->province;
    }

    public function setProvince(?string $province): static
    {
        $this->province = $province;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function getHeadimgurl(): ?string
    {
        return $this->headimgurl;
    }

    public function setHeadimgurl(?string $headimgurl): static
    {
        $this->headimgurl = $headimgurl;
        return $this;
    }

    public function getPrivilege(): ?array
    {
        return $this->privilege;
    }

    public function setPrivilege(?array $privilege): static
    {
        $this->privilege = $privilege;
        return $this;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): static
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): static
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }

    public function setExpiresIn(int $expiresIn): static
    {
        $this->expiresIn = $expiresIn;
        $this->accessTokenExpiresTime = new \DateTime('+' . $expiresIn . ' seconds');
        return $this;
    }

    public function getAccessTokenExpiresTime(): \DateTimeInterface
    {
        return $this->accessTokenExpiresTime;
    }

    public function isTokenExpired(): bool
    {
        return $this->accessTokenExpiresTime < new \DateTime();
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(?string $scope): static
    {
        $this->scope = $scope;
        return $this;
    }

    public function getRawData(): ?array
    {
        return $this->rawData;
    }

    public function setRawData(?array $rawData): static
    {
        $this->rawData = $rawData;
        return $this;
    }

    public function getSexText(): string
    {
        return match ($this->sex) {
            1 => '男',
            2 => '女',
            default => '未知',
        };
    }
}