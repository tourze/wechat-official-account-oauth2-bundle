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
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\OAuth2AuthorizationCodeRepository;
use WechatOfficialAccountBundle\Entity\Account;

#[ORM\Entity(repositoryClass: OAuth2AuthorizationCodeRepository::class)]
#[ORM\Table(name: 'wechat_oauth2_authorization_code', options: ['comment' => 'OAuth2授权码'])]
class OAuth2AuthorizationCode implements \Stringable
{
    use BlameableAware;
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => '授权码'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $code = null;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '微信用户OpenID'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $openid = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '微信用户UnionID'])]
    #[Assert\Length(max: 100)]
    private ?string $unionid = null;

    #[ORM\Column(type: Types::STRING, length: 500, options: ['comment' => '重定向URI'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 500)]
    private ?string $redirectUri = null;

    #[ORM\Column(type: Types::STRING, length: 200, nullable: true, options: ['comment' => '授权范围'])]
    #[Assert\Length(max: 200)]
    private ?string $scopes = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '状态参数'])]
    #[Assert\Length(max: 100)]
    private ?string $state = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '过期时间'])]
    #[Assert\NotNull]
    private ?\DateTimeInterface $expiresAt = null;

    #[ORM\ManyToOne(targetEntity: Account::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'wechat_account_id', referencedColumnName: 'id', nullable: false)]
    private ?Account $wechatAccount = null;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '已使用', 'default' => 0])]
    #[Assert\Type(type: 'bool')]
    private ?bool $used = false;

    public function __toString(): string
    {
        if (null === $this->getId()) {
            return '';
        }

        return "{$this->getCode()}({$this->getOpenId()})";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getOpenId(): ?string
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

    public function getRedirectUri(): ?string
    {
        return $this->redirectUri;
    }

    public function setRedirectUri(string $redirectUri): void
    {
        $this->redirectUri = $redirectUri;
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

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeInterface $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    public function isExpired(): bool
    {
        return null !== $this->expiresAt && $this->expiresAt < new \DateTimeImmutable();
    }

    public function getWechatAccount(): ?Account
    {
        return $this->wechatAccount;
    }

    public function setWechatAccount(?Account $wechatAccount): void
    {
        $this->wechatAccount = $wechatAccount;
    }

    public function isUsed(): ?bool
    {
        return $this->used;
    }

    public function setUsed(?bool $used): void
    {
        $this->used = $used;
    }
}
