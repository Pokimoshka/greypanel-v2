<?php

use GreyPanel\Core\RouteCollector;

return function (RouteCollector $r) {
    $r->addRoute('GET', '/', 'HomeController@index');
    $r->addRoute(['GET', 'POST'], '/login', 'AuthController@login')->addMiddleware('guest')->addMiddleware('csrf')->addMiddleware('rate_limit:login');
    $r->addRoute(['GET', 'POST'], '/register', 'AuthController@register')->addMiddleware('guest')->addMiddleware('csrf')->addMiddleware('rate_limit:register');
    $r->addRoute('GET', '/logout', 'AuthController@logout')->addMiddleware('auth');

    $r->addRoute('GET', '/profile', 'UserController@profile')->addMiddleware('auth');
    $r->addRoute(['GET', 'POST'], '/settings', 'UserController@settings')->addMiddleware('auth')->addMiddleware('csrf');
    $r->addRoute('GET', '/profile/referrals', 'UserController@referrals')->addMiddleware('auth');

    $r->addRoute(['GET', 'POST'], '/admin/themes', 'AdminController@themes')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');
    $r->addRoute(['GET', 'POST'], '/admin/theme', 'AdminController@themeSettings')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');

    $r->addRoute('GET', '/admin', 'AdminController@index')->addMiddleware('auth')->addMiddleware('role:4');
    $r->addRoute('GET', '/admin/users', 'AdminController@users')->addMiddleware('auth')->addMiddleware('role:4');
    $r->addRoute(['GET', 'POST'], '/admin/users/edit/{id:\d+}', 'AdminController@userEdit')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');
    $r->addRoute('GET', '/admin/logs', 'AdminController@logs')->addMiddleware('auth')->addMiddleware('role:4');
    $r->addRoute('GET', '/admin/stats/registrations', 'AdminController@statsRegistrations')->addMiddleware('auth')->addMiddleware('role:4');

    $r->addRoute('GET', '/admin/server-settings', 'AdminServerSettingsController@index')->addMiddleware('auth')->addMiddleware('role:4');
    $r->addRoute(['GET', 'POST'], '/admin/server-settings/add', 'AdminServerSettingsController@form')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');
    $r->addRoute(['GET', 'POST'], '/admin/server-settings/edit/{id:\d+}', 'AdminServerSettingsController@form')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/server-settings/delete/{id:\d+}', 'AdminServerSettingsController@delete')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');
    $r->addRoute('GET', '/admin/server-settings/test/{id:\d+}', 'AdminServerSettingsController@testConnection')->addMiddleware('auth')->addMiddleware('role:4');

    $r->addRoute('GET', '/admin/forum/categories', 'AdminForumController@categories')->addMiddleware('auth')->addMiddleware('role:4');
    $r->addRoute(['GET', 'POST'], '/admin/forum/categories/add', 'AdminForumController@categoryForm')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');
    $r->addRoute(['GET', 'POST'], '/admin/forum/categories/edit/{id:\d+}', 'AdminForumController@categoryForm')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/forum/categories/delete/{id:\d+}', 'AdminForumController@categoryDelete')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/forum/categories/sort', 'AdminForumController@sortCategories')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');

    $r->addRoute('GET', '/admin/forum/categories/{id:\d+}/forums', 'AdminForumController@forums')->addMiddleware('auth')->addMiddleware('role:4');
    $r->addRoute(['GET', 'POST'], '/admin/forum/categories/{id:\d+}/forums/add', 'AdminForumController@forumForm')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');
    $r->addRoute(['GET', 'POST'], '/admin/forum/categories/{id:\d+}/forums/edit/{fid:\d+}', 'AdminForumController@forumForm')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/forum/categories/{id:\d+}/forums/delete/{fid:\d+}', 'AdminForumController@forumDelete')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/forum/forums/sort', 'AdminForumController@sortForums')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');

    $r->addRoute('POST', '/admin/upload-image', 'AdminController@uploadImage')->addMiddleware('auth')->addMiddleware('role:2')->addMiddleware('csrf')->addMiddleware('rate_limit:upload_image');

    $r->addRoute('GET', '/admin/modules', 'AdminModuleController@index')->addMiddleware('auth')->addMiddleware('role:4');
    $r->addRoute('POST', '/admin/modules/toggle', 'AdminModuleController@toggle')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');

    $r->addRoute(['GET', 'POST'], '/admin/seo', 'AdminSeoController@index')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/seo/regenerate', 'AdminSeoController@regenerateSitemap')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');

    $r->addRoute(['GET', 'POST'], '/admin/security', 'AdminSecurityController@index')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/security/save', 'AdminSecurityController@save')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');

    $r->addRoute(['GET', 'POST'], '/admin/site-settings', 'AdminSiteController@index')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/site-settings/save', 'AdminSiteController@save')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');

    $r->addRoute('GET', '/balance', 'BalanceController@index')->addMiddleware('auth');
    $r->addRoute('GET', '/balance/history', 'BalanceController@history')->addMiddleware('auth');

    $r->addRoute('GET', '/vip', 'VipController@index')->addMiddleware('auth');
    $r->addRoute('GET', '/vip/{id:\d+}', 'VipController@privileges')->addMiddleware('auth');
    $r->addRoute('POST', '/vip/confirm', 'VipController@confirm')->addMiddleware('auth')->addMiddleware('csrf');
    $r->addRoute('POST', '/vip/activate', 'VipController@activate')->addMiddleware('auth')->addMiddleware('csrf')->addMiddleware('rate_limit:vip_activate');
    $r->addRoute('GET', '/vip/success', 'VipController@success')->addMiddleware('auth');

    $r->addRoute('GET', '/monitor', 'MonitorController@index');
    $r->addRoute('GET', '/monitor/data', 'MonitorController@data');

    $r->addRoute('GET', '/forum', 'ForumController@index');
    $r->addRoute('GET', '/forum/forum/{id:\d+}', 'ForumController@forum');
    $r->addRoute('GET', '/forum/thread/{id:\d+}', 'ForumController@thread');
    $r->addRoute('GET', '/forum/forum/{id:\d+}/create', 'ForumController@createThreadForm')->addMiddleware('auth');
    $r->addRoute('POST', '/forum/thread/create', 'ForumController@createThread')->addMiddleware('auth')->addMiddleware('csrf');
    $r->addRoute('POST', '/forum/post/create', 'ForumController@createPost')->addMiddleware('auth')->addMiddleware('csrf');
    $r->addRoute('POST', '/forum/like', 'ForumController@like')->addMiddleware('auth')->addMiddleware('csrf');
    $r->addRoute(['GET', 'POST'], '/forum/thread/edit/{id:\d+}', 'ForumController@editThread')->addMiddleware('auth')->addMiddleware('csrf');
    $r->addRoute('POST', '/forum/thread/delete/{id:\d+}', 'ForumController@deleteThread')->addMiddleware('auth')->addMiddleware('csrf');
    $r->addRoute(['GET', 'POST'], '/forum/post/edit/{id:\d+}', 'ForumController@editPost')->addMiddleware('auth')->addMiddleware('csrf');
    $r->addRoute('POST', '/forum/post/delete/{id:\d+}', 'ForumController@deletePost')->addMiddleware('auth')->addMiddleware('csrf');
    $r->addRoute('GET', '/forum/search', 'ForumController@search');

    $r->addRoute(['GET', 'POST'], '/admin/payments', 'AdminController@paymentSettings')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');
    $r->addRoute('GET', '/payment', 'PaymentController@index')->addMiddleware('auth');
    $r->addRoute('POST', '/payment/yoomoney', 'PaymentController@yoomoneyForm')->addMiddleware('auth')->addMiddleware('csrf');
    $r->addRoute('POST', '/payment/yoomoney/notify', 'PaymentController@yoomoneyNotify');
    $r->addRoute('GET', '/payment/success', 'PaymentController@success')->addMiddleware('auth');

    $r->addRoute(['GET', 'POST'], '/admin/bans/settings', 'AdminController@banSettings')->addMiddleware('auth')->addMiddleware('role:4')->addMiddleware('csrf');
    $r->addRoute('GET', '/bans', 'BanController@index');
    $r->addRoute('POST', '/bans/request', 'BanController@requestUnban')->addMiddleware('auth')->addMiddleware('csrf');
    $r->addRoute('POST', '/bans/paid', 'BanController@paidUnban')->addMiddleware('auth')->addMiddleware('csrf');

    $r->addRoute('GET', '/online/data', 'OnlineController@data');

    $r->addRoute('GET', '/cron/{key}', 'CronController@run');

    $r->addRoute('GET', '/chat/messages', 'ChatController@fetchMessages');
    $r->addRoute('POST', '/chat/send', 'ChatController@sendMessage')->addMiddleware('auth')->addMiddleware('csrf')->addMiddleware('rate_limit:chat_send');
    $r->addRoute('DELETE', '/chat/message/{id:\d+}', 'ChatController@deleteMessage')->addMiddleware('auth')->addMiddleware('role:2')->addMiddleware('csrf');

    $r->addRoute('GET', '/news', 'NewsController@index');
    $r->addRoute('GET', '/news/{slug}', 'NewsController@show');
    $r->addRoute('GET', '/admin/news', 'AdminNewsController@index')->addMiddleware('auth')->addMiddleware('role:3');
    $r->addRoute(['GET', 'POST'], '/admin/news/create', 'AdminNewsController@form')->addMiddleware('auth')->addMiddleware('role:3')->addMiddleware('csrf');
    $r->addRoute(['GET', 'POST'], '/admin/news/edit/{id:\d+}', 'AdminNewsController@form')->addMiddleware('auth')->addMiddleware('role:3')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/news/delete/{id:\d+}', 'AdminNewsController@delete')->addMiddleware('auth')->addMiddleware('role:3')->addMiddleware('csrf');

    $r->addRoute('GET', '/api/forum/last-topics', 'ForumController@lastTopics');
    $r->addRoute('GET', '/api/bans/last-bans', 'BanController@lastBans');

    $r->addRoute('GET', '/sitemap.xml', 'SitemapController@index');
};