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
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2User;

/**
 * 微信OAuth2用户管理控制器
 */
#[AdminCrud(routePath: '/wechat-oauth2/user', routeName: 'wechat_oauth2_user')]
final class WechatOAuth2UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return WechatOAuth2User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('OAuth2用户')
            ->setEntityLabelInPlural('OAuth2用户')
            ->setPageTitle('index', 'OAuth2用户管理')
            ->setPageTitle('detail', '查看OAuth2用户')
            ->setPageTitle('edit', '编辑OAuth2用户')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['openid', 'unionid', 'nickname'])
            ->setPaginatorPageSize(20)
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();

        yield TextField::new('openid', 'OpenID');

        yield TextField::new('unionid', 'UnionID')
            ->hideOnIndex()
        ;

        yield TextField::new('nickname', '昵称');

        yield ChoiceField::new('sex', '性别')
            ->setChoices([
                '未知' => 0,
                '男性' => 1,
                '女性' => 2,
            ])
            ->renderExpanded(false)
            ->hideOnIndex()
        ;

        yield TextField::new('city', '城市')
            ->hideOnIndex()
        ;

        yield TextField::new('province', '省份')
            ->hideOnIndex()
        ;

        yield TextField::new('country', '国家')
            ->hideOnIndex()
        ;

        yield ImageField::new('headimgurl', '头像')
            ->setBasePath('/')
            ->setUploadDir('public/uploads/wechat/user-avatar')
            ->hideOnIndex()
        ;

        yield TextField::new('accessToken', '访问令牌')
            ->formatValue(function ($value) {
                return is_string($value) && '' !== $value ? substr($value, 0, 20) . '...' : '';
            })
            ->hideOnIndex()
        ;

        yield DateTimeField::new('accessTokenExpiresTime', '令牌过期时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnIndex()
        ;

        yield AssociationField::new('config', 'OAuth2配置')
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
            ->add('openid')
            ->add('unionid')
            ->add('nickname')
            ->add('sex')
            ->add('country')
            ->add('province')
            ->add('city')
        ;
    }
}
