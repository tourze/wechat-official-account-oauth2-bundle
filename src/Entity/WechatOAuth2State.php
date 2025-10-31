<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2StateRepository;

/**
 * 微信OAuth2状态实体
 * 用于防止CSRF攻击，存储授权流程中的state参数
 */
#[ORM\Entity(repositoryClass: WechatOAuth2StateRepository::class)]
#[ORM\Table(name: 'wechat_oauth2_state', options: ['comment' => '微信OAuth2授权状态'])]
class WechatOAuth2State implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;

    #[Assert\Length(max: 128)]
    #[ORM\Column(type: Types::STRING, length: 128, nullable: true, options: ['comment' => '会话ID'])]
    private ?string $sessionId = null;

    #[Assert\NotNull]
    #[IndexColumn]
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true, 'comment' => '是否有效'])]
    private bool $valid = true;

    #[Assert\DateTime]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '使用时间'])]
    private ?\DateTimeInterface $usedTime = null;

    #[Assert\NotNull]
    #[Assert\DateTime]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '过期时间'])]
    private \DateTimeInterface $expiresTime;

    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 64, unique: true, options: ['comment' => '状态值'])]
    private string $state;

    #[ORM\ManyToOne(targetEntity: WechatOAuth2Config::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private WechatOAuth2Config $config;

    public function __construct()
    {
        $this->expiresTime = new \DateTimeImmutable('+5 minutes');
    }

    public function __toString(): string
    {
        return sprintf('WechatOAuth2State[%s](%s)', $this->id, $this->state);
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getConfig(): WechatOAuth2Config
    {
        return $this->config;
    }

    public function setConfig(WechatOAuth2Config $config): void
    {
        $this->config = $config;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): void
    {
        $this->valid = $valid;
    }

    public function isUsed(): bool
    {
        return !$this->valid;
    }

    public function markAsUsed(): void
    {
        $this->valid = false;
        $this->usedTime = new \DateTimeImmutable();
    }

    public function getUsedTime(): ?\DateTimeInterface
    {
        return $this->usedTime;
    }

    public function getExpiresTime(): \DateTimeInterface
    {
        return $this->expiresTime;
    }

    public function setExpiresTime(\DateTimeInterface $expiresTime): void
    {
        $this->expiresTime = $expiresTime;
    }

    public function isValidState(): bool
    {
        return $this->valid && !$this->isExpired();
    }

    public function isExpired(): bool
    {
        return $this->expiresTime < new \DateTimeImmutable();
    }
}
