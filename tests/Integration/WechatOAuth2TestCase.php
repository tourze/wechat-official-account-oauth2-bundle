<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle;
use Tourze\DoctrineTimestampBundle\DoctrineTimestampBundle;
use Tourze\IntegrationTestKernel\IntegrationTestKernel;
use Tourze\WechatOfficialAccountOAuth2Bundle\WechatOfficialAccountOAuth2Bundle;

abstract class WechatOAuth2TestCase extends KernelTestCase
{
    protected static function createKernel(array $options = []): KernelInterface
    {
        $env = $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test';
        $debug = $options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true;

        return new IntegrationTestKernel($env, $debug, [
            // Doctrine extensions
            DoctrineTimestampBundle::class => ['all' => true],
            DoctrineSnowflakeBundle::class => ['all' => true],
            // Core bundles
            WechatOfficialAccountOAuth2Bundle::class => ['all' => true],
        ]);
    }
}