<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Service;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\Loader\AnnotationClassLoader;
use Symfony\Component\Routing\RouteCollection;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;
use Tourze\WechatOfficialAccountOAuth2Bundle\Controller\OAuth2Controller;

/**
 * OAuth2路由自动加载器
 */
#[AutoconfigureTag(RoutingAutoLoaderInterface::TAG_NAME)]
class OAuth2RoutingAutoLoader implements RoutingAutoLoaderInterface
{
    public function __construct(
        private readonly AnnotationClassLoader $annotationClassLoader,
    ) {
    }

    public function autoload(): RouteCollection
    {
        $collection = new RouteCollection();

        // 加载OAuth2控制器路由
        $oauth2Routes = $this->annotationClassLoader->load(OAuth2Controller::class);
        $collection->addCollection($oauth2Routes);

        return $collection;
    }
}