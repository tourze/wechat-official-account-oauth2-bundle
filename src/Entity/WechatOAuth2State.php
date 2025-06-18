<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2StateRepository;

/**
 * 微信OAuth2状态实体
 * 用于防止CSRF攻击，存储授权流程中的state参数
 */
#[ORM\Entity(repositoryClass: WechatOAuth2StateRepository::class)]
#[ORM\Table(name: 'wechat_oauth2_state', options: ['comment' => '微信OAuth2授权状态'])]
#[ORM\Index(columns: ['valid'], name: 'wechat_oauth2_state_idx_valid')]
class WechatOAuth2State implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => '主键ID'])]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: SnowflakeIdGenerator::class)]
    private ?string $id = null;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 64, unique: true, options: ['comment' => '状态值'])]
    private string $state;

    #[ORM\ManyToOne(targetEntity: WechatOAuth2Config::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private WechatOAuth2Config $config;

    #[ORM\Column(type: Types::STRING, length: 128, nullable: true, options: ['comment' => '会话ID'])]
    private ?string $sessionId = null;

    #[IndexColumn]
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true, 'comment' => '是否有效'])]
    private bool $valid = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '使用时间'])]
    private ?\DateTimeInterface $usedTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '过期时间'])]
    private \DateTimeInterface $expiresTime;

    public function __construct(string $state, WechatOAuth2Config $config)
    {
        $this->state = $state;
        $this->config = $config;
        $this->expiresTime = new \DateTime('+5 minutes');
    }

    public function __toString(): string
    {
        return sprintf('WechatOAuth2State[%s](%s)', $this->id, $this->state);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getConfig(): WechatOAuth2Config
    {
        return $this->config;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): static
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): static
    {
        $this->valid = $valid;
        return $this;
    }

    public function isUsed(): bool
    {
        return !$this->valid;
    }

    public function markAsUsed(): static
    {
        $this->valid = false;
        $this->usedTime = new \DateTime();
        return $this;
    }

    public function getUsedTime(): ?\DateTimeInterface
    {
        return $this->usedTime;
    }

    public function getExpiresTime(): \DateTimeInterface
    {
        return $this->expiresTime;
    }

    public function setExpiresTime(\DateTimeInterface $expiresTime): static
    {
        $this->expiresTime = $expiresTime;
        return $this;
    }

    public function isValidState(): bool
    {
        return $this->valid && !$this->isExpired();
    }

    public function isExpired(): bool
    {
        return $this->expiresTime < new \DateTime();
    }
}