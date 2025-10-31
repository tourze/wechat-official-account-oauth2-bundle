<?php

declare(strict_types=1);

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use Tourze\WechatOfficialAccountOAuth2Bundle\WechatOfficialAccountOAuth2Bundle;

/**
 * @internal
 */
#[CoversClass(WechatOfficialAccountOAuth2Bundle::class)]
#[RunTestsInSeparateProcesses]
final class WechatOfficialAccountOAuth2BundleTest extends AbstractBundleTestCase
{
}
