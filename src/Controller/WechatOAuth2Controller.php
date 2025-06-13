<?php

namespace Tourze\WechatOfficialAccountOAuth2Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * 微信网页授权控制器
 */
#[Route('/wechat/oauth2', name: 'wechat_oauth2_')]
class WechatOAuth2Controller extends AbstractController
{
}