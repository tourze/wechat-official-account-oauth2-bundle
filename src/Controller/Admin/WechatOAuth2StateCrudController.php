<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Tourze\WechatOfficialAccountOAuth2Bundle\Entity\WechatOAuth2State;

/**
 * OAuth2状态参数管理控制器
 */
#[AdminCrud(routePath: '/wechat-oauth2/state', routeName: 'wechat_oauth2_state')]
final class WechatOAuth2StateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return WechatOAuth2State::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('OAuth2状态参数')
            ->setEntityLabelInPlural('OAuth2状态参数')
            ->setPageTitle('index', 'OAuth2状态参数管理')
            ->setPageTitle('detail', '查看OAuth2状态参数')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['state', 'sessionId'])
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

        yield TextField::new('state', '状态参数')
            ->formatValue(function ($value) {
                return is_string($value) && '' !== $value ? substr($value, 0, 30) . '...' : '';
            })
        ;

        yield DateTimeField::new('expiresTime', '过期时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;

        yield DateTimeField::new('createdAt', '创建时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->onlyOnDetail()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('state')
            ->add('sessionId')
            ->add('expiresTime')
        ;
    }
}
