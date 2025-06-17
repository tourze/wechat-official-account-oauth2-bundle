<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use WechatOfficialAccountBundle\WechatOfficialAccountBundle;

class WechatOfficialAccountOAuth2Bundle extends Bundle implements BundleDependencyInterface
{
    /**
     * @return array<class-string<\Symfony\Component\HttpKernel\Bundle\BundleInterface>, array<string, bool>>
     */
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            WechatOfficialAccountBundle::class => ['all' => true],
        ];
    }
}
