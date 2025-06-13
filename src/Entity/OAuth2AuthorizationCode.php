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
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\OAuth2AuthorizationCodeRepository;
use WechatOfficialAccountBundle\Entity\Account;

#[AsPermission(title: 'OAuth2授权码')]
#[Deletable]
#[Editable]
#[Creatable]
#[ORM\Entity(repositoryClass: OAuth2AuthorizationCodeRepository::class)]
#[ORM\Table(name: 'wechat_oauth2_authorization_code', options: ['comment' => 'OAuth2授权码'])]
class OAuth2AuthorizationCode implements \Stringable
{
    use BlameableAware;
    use TimestampableAware;

    #[ListColumn(order: -1)]
    #[ExportColumn]
    #[ORM\Id]
    #[SnowflakeColumn]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[FormField]
    #[Keyword]
    #[ListColumn]
    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => '授权码'])]
    private ?string $code = null;

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
    #[ORM\Column(type: Types::STRING, length: 500, options: ['comment' => '重定向URI'])]
    private ?string $redirectUri = null;

    #[FormField]
    #[ListColumn]
    #[ORM\Column(type: Types::STRING, length: 200, nullable: true, options: ['comment' => '授权范围'])]
    private ?string $scopes = null;

    #[FormField]
    #[ListColumn]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '状态参数'])]
    private ?string $state = null;

    #[FormField]
    #[ListColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['comment' => '过期时间'])]
    private ?\DateTimeInterface $expiresAt = null;

    #[FormField]
    #[ListColumn]
    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(name: 'wechat_account_id', referencedColumnName: 'id', nullable: false)]
    private ?Account $wechatAccount = null;

    #[BoolColumn]
    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '已使用', 'default' => 0])]
    #[ListColumn(order: 97)]
    #[FormField(order: 97)]
    private ?bool $used = false;

    public function __toString(): string
    {
        if (!$this->getId()) {
            return '';
        }

        return "{$this->getCode()}({$this->getOpenid()})";
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

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

    public function getUnionid(): ?string
    {
        return $this->unionid;
    }

    public function setUnionid(?string $unionid): static
    {
        $this->unionid = $unionid;

        return $this;
    }

    public function getRedirectUri(): ?string
    {
        return $this->redirectUri;
    }

    public function setRedirectUri(string $redirectUri): static
    {
        $this->redirectUri = $redirectUri;

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

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeInterface $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt && $this->expiresAt < new \DateTime();
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

    public function isUsed(): ?bool
    {
        return $this->used;
    }

    public function setUsed(?bool $used): static
    {
        $this->used = $used;

        return $this;
    }
}
