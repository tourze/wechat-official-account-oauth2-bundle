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
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\OAuth2AccessToken;

/**
 * OAuth2访问令牌管理控制器
 */
#[AdminCrud(routePath: '/wechat-oauth2/access-token', routeName: 'wechat_oauth2_access_token')]
final class OAuth2AccessTokenCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return OAuth2AccessToken::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('OAuth2访问令牌')
            ->setEntityLabelInPlural('OAuth2访问令牌')
            ->setPageTitle('index', 'OAuth2访问令牌管理')
            ->setPageTitle('detail', '查看OAuth2访问令牌')
            ->setPageTitle('edit', '编辑OAuth2访问令牌')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['accessToken', 'openid', 'unionid', 'scopes'])
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

        yield TextField::new('accessToken', '访问令牌')
            ->formatValue(function ($value) {
                return is_string($value) && '' !== $value ? substr($value, 0, 20) . '...' : '';
            })
        ;

        yield TextField::new('refreshToken', '刷新令牌')
            ->formatValue(function ($value) {
                return is_string($value) && '' !== $value ? substr($value, 0, 20) . '...' : '';
            })
            ->hideOnIndex()
        ;

        yield TextField::new('openid', 'OpenID');

        yield TextField::new('unionid', 'UnionID')
            ->hideOnIndex()
        ;

        yield TextField::new('scopes', '授权范围');

        yield DateTimeField::new('accessTokenExpiresAt', '访问令牌过期时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;

        yield DateTimeField::new('refreshTokenExpiresAt', '刷新令牌过期时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnIndex()
        ;

        yield BooleanField::new('isExpired', '已过期')
            ->renderAsSwitch(false)
            ->onlyOnIndex()
        ;

        yield AssociationField::new('wechatAccount', '微信账号')
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
            ->add('accessToken')
            ->add('openid')
            ->add('unionid')
            ->add('scopes')
            ->add('accessTokenExpiresAt')
        ;
    }
}
