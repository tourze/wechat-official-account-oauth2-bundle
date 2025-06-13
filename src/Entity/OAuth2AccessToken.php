<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Attribute\SnowflakeColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Column\BoolColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Filter\Keyword;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\OAuth2AccessTokenRepository;
use WechatOfficialAccountBundle\Entity\Account;

#[AsPermission(title: 'OAuth2访问令牌')]
#[Deletable]
#[Editable]
#[Creatable]
#[ORM\Entity(repositoryClass: OAuth2AccessTokenRepository::class)]
#[ORM\Table(name: 'wechat_oauth2_access_token', options: ['comment' => 'OAuth2访问令牌'])]
class OAuth2AccessToken implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;

    #[ListColumn(order: -1)]
    #[ExportColumn]
    #[ORM\Id]
    #[SnowflakeColumn]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[FormField]
    #[Keyword]
    #[ListColumn]
    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => '访问令牌'])]
    private ?string $accessToken = null;

    #[FormField]
    #[ListColumn]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '刷新令牌'])]
    private ?string $refreshToken = null;

    #[FormField]
    #[ListColumn]
    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '微信用户OpenID'])]
    private ?string $openid = null;

    #[FormField]
    #[ListColumn]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '微信用户UnionID'])]
    private ?string $unionid = null;

    #[FormField]
    #[ListColumn]
    #[ORM\Column(type: Types::STRING, length: 200, nullable: true, options: ['comment' => '授权范围'])]
    private ?string $scopes = null;

    #[FormField]
    #[ListColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['comment' => '访问令牌过期时间'])]
    private ?\DateTimeInterface $accessTokenExpiresAt = null;

    #[FormField]
    #[ListColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '刷新令牌过期时间'])]
    private ?\DateTimeInterface $refreshTokenExpiresAt = null;

    #[FormField]
    #[ListColumn]
    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(name: 'wechat_account_id', referencedColumnName: 'id', nullable: false)]
    private ?Account $wechatAccount = null;

    #[BoolColumn]
    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '已撤销', 'default' => 0])]
    #[ListColumn(order: 97)]
    #[FormField(order: 97)]
    private ?bool $revoked = false;

    public function __toString(): string
    {
        if (!$this->getId()) {
            return '';
        }

        return "{$this->getAccessToken()}({$this->getOpenid()})";
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getOpenid(): ?string
    {
        return $this->openid;
    }

    public function setOpenid(string $openid): static
    {
        $this->openid = $openid;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): static
    {
        $this->refreshToken = $refreshToken;

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

    public function getScopes(): ?string
    {
        return $this->scopes;
    }

    public function setScopes(?string $scopes): static
    {
        $this->scopes = $scopes;

        return $this;
    }

    public function getScopesArray(): array
    {
        if (!$this->scopes) {
            return [];
        }

        return array_filter(array_map('trim', explode(' ', $this->scopes)));
    }

    public function getAccessTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->accessTokenExpiresAt;
    }

    public function setAccessTokenExpiresAt(\DateTimeInterface $accessTokenExpiresAt): static
    {
        $this->accessTokenExpiresAt = $accessTokenExpiresAt;

        return $this;
    }

    public function getRefreshTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->refreshTokenExpiresAt;
    }

    public function setRefreshTokenExpiresAt(?\DateTimeInterface $refreshTokenExpiresAt): static
    {
        $this->refreshTokenExpiresAt = $refreshTokenExpiresAt;

        return $this;
    }

    public function isRefreshTokenExpired(): bool
    {
        return $this->refreshTokenExpiresAt && $this->refreshTokenExpiresAt < new \DateTime();
    }

    public function getWechatAccount(): ?Account
    {
        return $this->wechatAccount;
    }

    public function setWechatAccount(?Account $wechatAccount): static
    {
        $this->wechatAccount = $wechatAccount;

        return $this;
    }

    public function setRevoked(?bool $revoked): static
    {
        $this->revoked = $revoked;

        return $this;
    }

    public function isValid(): bool
    {
        return !$this->isRevoked() && !$this->isAccessTokenExpired();
    }

    public function isRevoked(): ?bool
    {
        return $this->revoked;
    }

    public function isAccessTokenExpired(): bool
    {
        return $this->accessTokenExpiresAt && $this->accessTokenExpiresAt < new \DateTime();
    }
}