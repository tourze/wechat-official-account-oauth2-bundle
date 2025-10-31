<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AuthorizationCode;

/**
 * OAuth2授权码管理控制器
 */
#[AdminCrud(routePath: '/wechat-oauth2/authorization-code', routeName: 'wechat_oauth2_authorization_code')]
final class OAuth2AuthorizationCodeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return OAuth2AuthorizationCode::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('OAuth2授权码')
            ->setEntityLabelInPlural('OAuth2授权码')
            ->setPageTitle('index', 'OAuth2授权码管理')
            ->setPageTitle('detail', '查看OAuth2授权码')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['code', 'openid', 'scopes'])
            ->setPaginatorPageSize(20)
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->disable(Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();

        yield TextField::new('code', '授权码')
            ->formatValue(function ($value) {
                return is_string($value) && '' !== $value ? substr($value, 0, 20) . '...' : '';
            })
        ;

        yield TextField::new('openid', 'OpenID');

        yield TextField::new('scopes', '授权范围');

        yield TextField::new('redirectUri', '回调地址')
            ->hideOnIndex()
        ;

        yield TextField::new('state', '状态参数')
            ->hideOnIndex()
        ;

        yield DateTimeField::new('expiresAt', '过期时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;

        yield AssociationField::new('wechatAccount', '微信账号')
            ->hideOnIndex()
        ;

        yield DateTimeField::new('createdAt', '创建时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->onlyOnDetail()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('code')
            ->add('openid')
            ->add('scopes')
            ->add('expiresAt')
        ;
    }
}
