<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\WechatOfficialAccountContracts\OfficialAccountInterface;
use Tourze\WechatOfficialAccountContracts\UserInterface;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2UserRepository;

/**
 * 微信OAuth2用户实体
 * 存储通过OAuth2获取的微信用户信息
 */
#[ORM\Entity(repositoryClass: WechatOAuth2UserRepository::class)]
#[ORM\Table(name: 'wechat_oauth2_user', options: ['comment' => '微信OAuth2用户信息'])]
#[ORM\UniqueConstraint(columns: ['openid', 'config_id'])]
class WechatOAuth2User implements \Stringable, UserInterface
{
    use SnowflakeKeyAware;
    use TimestampableAware;

    #[ORM\ManyToOne(targetEntity: WechatOAuth2Config::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private WechatOAuth2Config $config;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 64, options: ['comment' => '用户OpenID'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    private string $openid;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 64, nullable: true, options: ['comment' => '用户UnionID'])]
    #[Assert\Length(max: 64)]
    private ?string $unionid = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '用户昵称'])]
    #[Assert\Length(max: 255)]
    private ?string $nickname = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '用户性别(0:未知,1:男性,2:女性)'])]
    #[Assert\Range(min: 0, max: 2)]
    private ?int $sex = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '用户省份'])]
    #[Assert\Length(max: 50)]
    private ?string $province = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '用户城市'])]
    #[Assert\Length(max: 50)]
    private ?string $city = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '用户国家'])]
    #[Assert\Length(max: 50)]
    private ?string $country = null;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true, options: ['comment' => '用户头像URL'])]
    #[Assert\Length(max: 500)]
    #[Assert\Url(message: '头像 URL 格式不正确')]
    private ?string $headimgurl = null;

    /** @var array<string>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '用户特权信息'])]
    #[Assert\Type(type: 'array')]
    private ?array $privilege = null;

    #[ORM\Column(type: Types::STRING, length: 512, options: ['comment' => '访问令牌'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 512)]
    private string $accessToken;

    #[ORM\Column(type: Types::STRING, length: 512, options: ['comment' => '刷新令牌'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 512)]
    private string $refreshToken;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '令牌有效期(秒)'])]
    #[Assert\Positive]
    private int $expiresIn;

    #[IndexColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '访问令牌过期时间'])]
    #[Assert\NotNull]
    private \DateTimeInterface $accessTokenExpiresTime;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '授权作用域'])]
    #[Assert\Length(max: 100)]
    private ?string $scope = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '原始用户数据'])]
    #[Assert\Type(type: 'array')]
    private ?array $rawData = null;

    public function __toString(): string
    {
        return sprintf('WechatOAuth2User[%s](%s)', $this->id, $this->nickname ?? $this->getOpenId());
    }

    public function getConfig(): WechatOAuth2Config
    {
        return $this->config;
    }

    public function setConfig(WechatOAuth2Config $config): void
    {
        $this->config = $config;
    }

    public function getOpenId(): string
    {
        return $this->openid;
    }

    public function setOpenId(string $openid): void
    {
        $this->openid = $openid;
    }

    public function getUnionId(): ?string
    {
        return $this->unionid;
    }

    public function setUnionId(?string $unionid): void
    {
        $this->unionid = $unionid;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): void
    {
        $this->nickname = $nickname;
    }

    public function getSex(): ?int
    {
        return $this->sex;
    }

    public function setSex(?int $sex): void
    {
        $this->sex = $sex;
    }

    public function getProvince(): ?string
    {
        return $this->province;
    }

    public function setProvince(?string $province): void
    {
        $this->province = $province;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getHeadimgurl(): ?string
    {
        return $this->headimgurl;
    }

    public function setHeadimgurl(?string $headimgurl): void
    {
        $this->headimgurl = $headimgurl;
    }

    /**
     * @return array<string>|null
     */
    public function getPrivilege(): ?array
    {
        return $this->privilege;
    }

    /**
     * @param array<string>|null $privilege
     */
    public function setPrivilege(?array $privilege): void
    {
        $this->privilege = $privilege;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }

    public function setExpiresIn(int $expiresIn): void
    {
        $this->expiresIn = $expiresIn;
        $this->accessTokenExpiresTime = new \DateTimeImmutable('+' . $expiresIn . ' seconds');
    }

    public function getAccessTokenExpiresTime(): \DateTimeInterface
    {
        return $this->accessTokenExpiresTime;
    }

    public function isTokenExpired(): bool
    {
        return $this->accessTokenExpiresTime < new \DateTimeImmutable();
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(?string $scope): void
    {
        $this->scope = $scope;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRawData(): ?array
    {
        return $this->rawData;
    }

    /**
     * @param array<string, mixed>|null $rawData
     */
    public function setRawData(?array $rawData): void
    {
        $this->rawData = $rawData;
    }

    public function getSexText(): string
    {
        return match ($this->sex) {
            1 => '男',
            2 => '女',
            default => '未知',
        };
    }

    public function getAvatarUrl(): ?string
    {
        return $this->getHeadimgurl();
    }

    public function getOfficialAccount(): ?OfficialAccountInterface
    {
        return $this->getConfig()->getAccount();
    }
}
