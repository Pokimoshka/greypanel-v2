<?php

declare(strict_types=1);

use GreyPanel\Core\RouteCollector;

return function (RouteCollector $r) {
    // Публичные маршруты
    $r->addRoute('GET', '/', 'HomeController@index');
    $r->addRoute(['GET', 'POST'], '/login', 'AuthController@login')
        ->addMiddleware('guest')->addMiddleware('csrf')->addMiddleware('rate_limit:login');
    $r->addRoute(['GET', 'POST'], '/register', 'AuthController@register')
        ->addMiddleware('guest')->addMiddleware('csrf')->addMiddleware('rate_limit:register');
    $r->addRoute('GET', '/logout', 'AuthController@logout')->addMiddleware('auth');

    // Профиль и настройки
    $r->addRoute('GET', '/profile', 'UserController@profile')->addMiddleware('auth');
    $r->addRoute(['GET', 'POST'], '/settings', 'UserController@settings')
        ->addMiddleware('auth')->addMiddleware('csrf');
    $r->addRoute('GET', '/profile/referrals', 'UserController@referrals')->addMiddleware('auth');

    // Админка: группы пользователей
    $r->addRoute('GET', '/admin/groups', 'AdminUserGroupController@index')
        ->addMiddleware('auth')->addMiddleware('permission:a');
    $r->addRoute(['GET', 'POST'], '/admin/groups/add', 'AdminUserGroupController@form')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute(['GET', 'POST'], '/admin/groups/edit/{id:\d+}', 'AdminUserGroupController@form')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/groups/delete/{id:\d+}', 'AdminUserGroupController@delete')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');

    // Админка: основные разделы
    $r->addRoute('GET', '/admin', 'AdminController@index')
        ->addMiddleware('auth')->addMiddleware('permission:a');
    $r->addRoute('GET', '/admin/users', 'AdminController@users')
        ->addMiddleware('auth')->addMiddleware('permission:a');
    $r->addRoute(['GET', 'POST'], '/admin/users/edit/{id:\d+}', 'AdminController@userEdit')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute('GET', '/admin/logs', 'AdminController@logs')
        ->addMiddleware('auth')->addMiddleware('permission:a');
    $r->addRoute('GET', '/admin/stats/registrations', 'AdminController@statsRegistrations')
        ->addMiddleware('auth')->addMiddleware('permission:a');
    $r->addRoute('POST', '/admin/upload-image', 'AdminController@uploadImage')
        ->addMiddleware('auth')->addMiddleware('permission:c')->addMiddleware('csrf')
        ->addMiddleware('rate_limit:upload_image');

    // Админка: серверы
    $r->addRoute('GET', '/admin/server-settings', 'AdminServerSettingsController@index')
        ->addMiddleware('auth')->addMiddleware('permission:a');
    $r->addRoute(['GET', 'POST'], '/admin/server-settings/add', 'AdminServerSettingsController@form')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute(['GET', 'POST'], '/admin/server-settings/edit/{id:\d+}', 'AdminServerSettingsController@form')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/server-settings/delete/{id:\d+}', 'AdminServerSettingsController@delete')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute('GET', '/admin/server-settings/test/{id:\d+}', 'AdminServerSettingsController@testConnection')
        ->addMiddleware('auth')->addMiddleware('permission:a');

    // Админка: форум
    $r->addRoute('GET', '/admin/forum/categories', 'AdminForumController@categories')
        ->addMiddleware('auth')->addMiddleware('permission:a');
    $r->addRoute(['GET', 'POST'], '/admin/forum/categories/add', 'AdminForumController@categoryForm')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute(['GET', 'POST'], '/admin/forum/categories/edit/{id:\d+}', 'AdminForumController@categoryForm')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/forum/categories/delete/{id:\d+}', 'AdminForumController@categoryDelete')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/forum/categories/sort', 'AdminForumController@sortCategories')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute('GET', '/admin/forum/categories/{id:\d+}/forums', 'AdminForumController@forums')
        ->addMiddleware('auth')->addMiddleware('permission:a');
    $r->addRoute(['GET', 'POST'], '/admin/forum/categories/{id:\d+}/forums/add', 'AdminForumController@forumForm')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute(['GET', 'POST'], '/admin/forum/categories/{id:\d+}/forums/edit/{fid:\d+}', 'AdminForumController@forumForm')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/forum/categories/{id:\d+}/forums/delete/{fid:\d+}', 'AdminForumController@forumDelete')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/forum/forums/sort', 'AdminForumController@sortForums')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');

    // Админка: темы, модули, SEO, безопасность, сайт, платежи
    $r->addRoute(['GET', 'POST'], '/admin/themes', 'AdminController@themes')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute(['GET', 'POST'], '/admin/theme', 'AdminController@themeSettings')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute('GET', '/admin/modules', 'AdminModuleController@index')
        ->addMiddleware('auth')->addMiddleware('permission:a');
    $r->addRoute('POST', '/admin/modules/toggle', 'AdminModuleController@toggle')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute(['GET', 'POST'], '/admin/seo', 'AdminSeoController@index')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/seo/regenerate', 'AdminSeoController@regenerateSitemap')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute(['GET', 'POST'], '/admin/security', 'AdminSecurityController@index')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/security/save', 'AdminSecurityController@save')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute(['GET', 'POST'], '/admin/site-settings', 'AdminSiteController@index')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/site-settings/save', 'AdminSiteController@save')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute(['GET', 'POST'], '/admin/payments', 'AdminController@paymentSettings')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute(['GET', 'POST'], '/admin/bans/settings', 'AdminController@banSettings')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute('GET', '/admin/theme-editor', 'AdminThemeEditorController@editor')
        ->addMiddleware('auth')->addMiddleware('permission:a');
    $r->addRoute('GET', '/admin/theme-editor/get-file', 'AdminThemeEditorController@getFileContent')
        ->addMiddleware('auth')->addMiddleware('permission:a');
    $r->addRoute('POST', '/admin/theme-editor/save-file', 'AdminThemeEditorController@saveFileContent')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/theme-editor/create', 'AdminThemeEditorController@create')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/theme-editor/delete', 'AdminThemeEditorController@delete')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');

    // Админка: новости
    $r->addRoute('GET', '/admin/news', 'AdminNewsController@index')
        ->addMiddleware('auth')->addMiddleware('permission:b');
    $r->addRoute(['GET', 'POST'], '/admin/news/create', 'AdminNewsController@form')
        ->addMiddleware('auth')->addMiddleware('permission:b')->addMiddleware('csrf');
    $r->addRoute(['GET', 'POST'], '/admin/news/edit/{id:\d+}', 'AdminNewsController@form')
        ->addMiddleware('auth')->addMiddleware('permission:b')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/news/delete/{id:\d+}', 'AdminNewsController@delete')
        ->addMiddleware('auth')->addMiddleware('permission:b')->addMiddleware('csrf');

    // Пользовательские разделы: баланс, платежи, мониторинг, форум, новости, чат, баны
    $r->addRoute('GET', '/balance', 'BalanceController@index')->addMiddleware('auth');
    $r->addRoute('GET', '/balance/history', 'BalanceController@history')->addMiddleware('auth');
    $r->addRoute('GET', '/payment', 'PaymentController@index')->addMiddleware('auth');
    $r->addRoute('POST', '/payment/yoomoney', 'PaymentController@yoomoneyForm')
        ->addMiddleware('auth')->addMiddleware('csrf');
    $r->addRoute('POST', '/payment/yoomoney/notify', 'PaymentController@yoomoneyNotify');
    $r->addRoute('GET', '/payment/success', 'PaymentController@success')->addMiddleware('auth');
    $r->addRoute('GET', '/monitor', 'MonitorController@index');
    $r->addRoute('GET', '/monitor/data', 'MonitorController@data')
        ->addMiddleware('rate_limit:api_monitor');

    $r->addRoute('GET', '/forum', 'ForumController@index');
    $r->addRoute('GET', '/forum/forum/{id:\d+}', 'ForumController@forum');
    $r->addRoute('GET', '/forum/thread/{id:\d+}', 'ForumController@thread');
    $r->addRoute('GET', '/forum/forum/{id:\d+}/create', 'ForumController@createThreadForm')
        ->addMiddleware('auth');
    $r->addRoute('POST', '/forum/thread/create', 'ForumController@createThread')
        ->addMiddleware('auth')->addMiddleware('csrf');
    $r->addRoute('POST', '/forum/post/create', 'ForumController@createPost')
        ->addMiddleware('auth')->addMiddleware('csrf');
    $r->addRoute('POST', '/forum/like', 'ForumController@like')
        ->addMiddleware('auth')->addMiddleware('csrf');
    $r->addRoute(['GET', 'POST'], '/forum/thread/edit/{id:\d+}', 'ForumController@editThread')
        ->addMiddleware('auth')->addMiddleware('csrf');
    $r->addRoute('POST', '/forum/thread/delete/{id:\d+}', 'ForumController@deleteThread')
        ->addMiddleware('auth')->addMiddleware('csrf');
    $r->addRoute(['GET', 'POST'], '/forum/post/edit/{id:\d+}', 'ForumController@editPost')
        ->addMiddleware('auth')->addMiddleware('csrf');
    $r->addRoute('POST', '/forum/post/delete/{id:\d+}', 'ForumController@deletePost')
        ->addMiddleware('auth')->addMiddleware('csrf');
    $r->addRoute('GET', '/forum/search', 'ForumController@search');

    $r->addRoute('GET', '/news', 'NewsController@index');
    $r->addRoute('GET', '/news/{slug}', 'NewsController@show');

    $r->addRoute('GET', '/bans', 'BanController@index');
    $r->addRoute('POST', '/bans/request', 'BanController@requestUnban')
        ->addMiddleware('auth')->addMiddleware('csrf');
    $r->addRoute('POST', '/bans/paid', 'BanController@paidUnban')
        ->addMiddleware('auth')->addMiddleware('csrf');

    // Управление услугами и тарифами
    $r->addRoute('GET', '/admin/services', 'AdminServiceController@index')
        ->addMiddleware('auth')->addMiddleware('permission:a');
    $r->addRoute(['GET', 'POST'], '/admin/services/add', 'AdminServiceController@form')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute(['GET', 'POST'], '/admin/services/edit/{id:\d+}', 'AdminServiceController@form')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/services/delete/{id:\d+}', 'AdminServiceController@delete')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/services/{id:\d+}/tariffs/add', 'AdminServiceController@createTariff')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute('POST', '/admin/services/{id:\d+}/tariffs/delete/{tid:\d+}', 'AdminServiceController@deleteTariff')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');

    $r->addRoute('GET', '/stats', 'StatisticsController@index');
    $r->addRoute('GET', '/stats/player/{id:\d+}', 'StatisticsController@player');

    $r->addRoute('GET', '/chat/messages', 'ChatController@fetchMessages');
    $r->addRoute('POST', '/chat/send', 'ChatController@sendMessage')
        ->addMiddleware('auth')->addMiddleware('csrf')->addMiddleware('rate_limit:chat_send');
    $r->addRoute('DELETE', '/chat/message/{id:\d+}', 'ChatController@deleteMessage')
        ->addMiddleware('auth')->addMiddleware('permission:c')->addMiddleware('csrf');

    $r->addRoute('GET', '/online/data', 'OnlineController@data');
    $r->addRoute('GET', '/api/forum/last-topics', 'ForumController@lastTopics');
    $r->addRoute('GET', '/api/bans/last-bans', 'BanController@lastBans');
    $r->addRoute('GET', '/sitemap.xml', 'SitemapController@index');
    $r->addRoute('GET', '/api/services/{id:\d+}/tariffs', 'AdminServiceController@apiTariffs')
        ->addMiddleware('auth')->addMiddleware('permission:a');
    $r->addRoute('PUT', '/api/services/{id:\d+}', 'AdminServiceController@apiUpdateService')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');
    $r->addRoute('PUT', '/api/services/{sid:\d+}/tariffs/{id:\d+}', 'AdminServiceController@apiUpdateTariff')
        ->addMiddleware('auth')->addMiddleware('permission:a')->addMiddleware('csrf');

    // Системные маршруты
    $r->addRoute('POST', '/cron', 'CronController@run')->addMiddleware('rate_limit:cron');
};
