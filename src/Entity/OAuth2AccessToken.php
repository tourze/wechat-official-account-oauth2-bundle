<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\OAuth2AccessTokenRepository;
use WechatOfficialAccountBundle\Entity\Account;

#[ORM\Entity(repositoryClass: OAuth2AccessTokenRepository::class)]
#[ORM\Table(name: 'wechat_oauth2_access_token', options: ['comment' => 'OAuth2访问令牌'])]
class OAuth2AccessToken implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => '访问令牌'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $accessToken = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '刷新令牌'])]
    #[Assert\Length(max: 100)]
    private ?string $refreshToken = null;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '微信用户OpenID'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $openid = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '微信用户UnionID'])]
    #[Assert\Length(max: 100)]
    private ?string $unionid = null;

    #[ORM\Column(type: Types::STRING, length: 200, nullable: true, options: ['comment' => '授权范围'])]
    #[Assert\Length(max: 200)]
    private ?string $scopes = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '访问令牌过期时间'])]
    #[Assert\NotNull]
    private ?\DateTimeInterface $accessTokenExpiresAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '刷新令牌过期时间'])]
    #[Assert\Type(type: '\DateTimeInterface')]
    private ?\DateTimeInterface $refreshTokenExpiresAt = null;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(name: 'wechat_account_id', referencedColumnName: 'id', nullable: false)]
    private ?Account $wechatAccount = null;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '已撤销', 'default' => 0])]
    #[Assert\Type(type: 'bool')]
    private ?bool $revoked = false;

    public function __toString(): string
    {
        if (null === $this->getId()) {
            return '';
        }

        return "{$this->getAccessToken()}({$this->getOpenId()})";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getOpenId(): ?string
    {
        return $this->openid;
    }

    public function setOpenId(string $openid): void
    {
        $this->openid = $openid;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    public function getUnionId(): ?string
    {
        return $this->unionid;
    }

    public function setUnionId(?string $unionid): void
    {
        $this->unionid = $unionid;
    }

    public function getScopes(): ?string
    {
        return $this->scopes;
    }

    public function setScopes(?string $scopes): void
    {
        $this->scopes = $scopes;
    }

    /**
     * @return array<string>
     */
    public function getScopesArray(): array
    {
        if (null === $this->scopes) {
            return [];
        }

        return array_filter(array_map('trim', explode(' ', $this->scopes)), static fn ($value): bool => '' !== $value);
    }

    public function getAccessTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->accessTokenExpiresAt;
    }

    public function setAccessTokenExpiresAt(\DateTimeInterface $accessTokenExpiresAt): void
    {
        $this->accessTokenExpiresAt = $accessTokenExpiresAt;
    }

    public function getRefreshTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->refreshTokenExpiresAt;
    }

    public function setRefreshTokenExpiresAt(?\DateTimeInterface $refreshTokenExpiresAt): void
    {
        $this->refreshTokenExpiresAt = $refreshTokenExpiresAt;
    }

    public function isRefreshTokenExpired(): bool
    {
        return null !== $this->refreshTokenExpiresAt && $this->refreshTokenExpiresAt < new \DateTimeImmutable();
    }

    public function getWechatAccount(): ?Account
    {
        return $this->wechatAccount;
    }

    public function setWechatAccount(?Account $wechatAccount): void
    {
        $this->wechatAccount = $wechatAccount;
    }

    public function setRevoked(?bool $revoked): void
    {
        $this->revoked = $revoked;
    }

    public function isValid(): bool
    {
        return !($this->isRevoked() ?? false) && !$this->isAccessTokenExpired();
    }

    public function isRevoked(): ?bool
    {
        return $this->revoked;
    }

    public function isAccessTokenExpired(): bool
    {
        return null !== $this->accessTokenExpiresAt && $this->accessTokenExpiresAt < new \DateTimeImmutable();
    }
}
