<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2Config;

/**
 * 微信OAuth2配置管理控制器
 */
#[AdminCrud(routePath: '/wechat-oauth2/config', routeName: 'wechat_oauth2_config')]
final class WechatOAuth2ConfigCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return WechatOAuth2Config::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('OAuth2配置')
            ->setEntityLabelInPlural('OAuth2配置')
            ->setPageTitle('index', 'OAuth2配置管理')
            ->setPageTitle('new', '新建OAuth2配置')
            ->setPageTitle('edit', '编辑OAuth2配置')
            ->setPageTitle('detail', '查看OAuth2配置')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['scope', 'remark'])
            ->setPaginatorPageSize(20)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();

        yield AssociationField::new('account', '微信账号')
            ->setRequired(true)
        ;

        yield ChoiceField::new('scope', '授权范围')
            ->setChoices([
                '静默授权(snsapi_base)' => 'snsapi_base',
                '用户信息授权(snsapi_userinfo)' => 'snsapi_userinfo',
            ])
            ->setRequired(true)
        ;

        yield TextareaField::new('remark', '备注说明')
            ->setMaxLength(500)
            ->hideOnIndex()
        ;

        yield DateTimeField::new('createdAt', '创建时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->onlyOnDetail()
        ;

        yield DateTimeField::new('updatedAt', '更新时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->onlyOnDetail()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('scope')
            ->add('remark')
        ;
    }
}
