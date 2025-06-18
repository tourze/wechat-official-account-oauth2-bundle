<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\WechatOfficialAccountOAuth2Bundle\Repository\WechatOAuth2ConfigRepository;
use WechatOfficialAccountBundle\Entity\Account;

/**
 * 微信OAuth2配置实体
 */
#[ORM\Entity(repositoryClass: WechatOAuth2ConfigRepository::class)]
#[ORM\Table(name: 'wechat_oauth2_config', options: ['comment' => '微信OAuth2配置'])]
#[ORM\Index(columns: ['valid', 'is_default'], name: 'wechat_oauth2_config_idx_valid_default')]
#[ORM\UniqueConstraint(columns: ['account_id'])]
class WechatOAuth2Config implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => '主键ID'])]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: SnowflakeIdGenerator::class)]
    private ?string $id = null;

    /**
     * 关联的微信公众号账户
     */
    #[ORM\ManyToOne(targetEntity: Account::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Account $account;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '授权作用域'])]
    private ?string $scope = null;

    #[IndexColumn]
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true, 'comment' => '是否启用'])]
    private bool $valid = true;

    #[IndexColumn]
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false, 'comment' => '是否为默认配置'])]
    private bool $isDefault = false;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注信息'])]
    private ?string $remark = null;

    public function __toString(): string
    {
        return sprintf('WechatOAuth2Config[%s](%s)', $this->id, $this->account->getName() ?? $this->account->getAppId());
    }

    public function getAppId(): string
    {
        return $this->account->getAppId() ?? '';
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): static
    {
        $this->account = $account;
        return $this;
    }

    public function getAppSecret(): string
    {
        return $this->account->getAppSecret() ?? '';
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

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): static
    {
        $this->valid = $valid;
        return $this;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): static
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): static
    {
        $this->remark = $remark;
        return $this;
    }
}