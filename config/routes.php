<?php

declare(strict_types=1);

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

return function (RouteCollection $routes): void {
    $routes->add('home', new Route('/', [
        '_controller' => 'HomeController@index',
    ]));
    $routes->add('monitor', new Route('/monitor', [
        '_controller' => 'MonitorController@index',
    ]));
    $routes->add('monitor_data', new Route('/monitor/data', [
        '_controller' => 'MonitorController@data',
        '_middleware' => ['rate_limit:api_monitor'],
    ]));
    $routes->add('forum', new Route('/forum', [
        '_controller' => 'ForumController@index',
    ]));
    $routes->add('forum_forum', new Route('/forum/forum/{id}', [
        '_controller' => 'ForumController@forum',
    ], ['id' => '\d+']));
    $routes->add('forum_thread', new Route('/forum/thread/{id}', [
        '_controller' => 'ForumController@thread',
    ], ['id' => '\d+']));
    $routes->add('forum_search', new Route('/forum/search', [
        '_controller' => 'ForumController@search',
    ]));
    $routes->add('news', new Route('/news', [
        '_controller' => 'NewsController@index',
    ]));
    $routes->add('news_show', new Route('/news/{slug}', [
        '_controller' => 'NewsController@show',
    ]));
    $routes->add('bans', new Route('/bans', [
        '_controller' => 'BanController@index',
    ]));
    $routes->add('stats', new Route('/stats', [
        '_controller' => 'StatisticsController@index',
    ]));
    $routes->add('stats_player', new Route('/stats/player/{id}', [
        '_controller' => 'StatisticsController@player',
    ], ['id' => '\d+']));
    $routes->add('chat_messages', new Route('/chat/messages', [
        '_controller' => 'ChatController@fetchMessages',
    ]));
    $routes->add('online_data', new Route('/online/data', [
        '_controller' => 'OnlineController@data',
    ]));
    $routes->add('api_forum_last_topics', new Route('/api/forum/last-topics', [
        '_controller' => 'ForumController@lastTopics',
    ]));
    $routes->add('api_bans_last_bans', new Route('/api/bans/last-bans', [
        '_controller' => 'BanController@lastBans',
    ]));
    $routes->add('api_top_donators', new Route('/api/top-donators', [
        '_controller' => 'UserController@topDonators',
    ]));
    $routes->add('sitemap', new Route('/sitemap.xml', [
        '_controller' => 'SitemapController@index',
    ]));
    $routes->add('cron', new Route('/cron', [
        '_controller' => 'CronController@run',
        '_middleware' => ['rate_limit:cron'],
    ]));
    $routes->add('robots', new Route('/robots.txt', [
        '_controller' => 'AdminSeoController@robots',
    ]));
    $routes->add('language_switch', new Route('/language/{lang}', [
        '_controller' => 'LanguageController@switch',
    ]));

    $routes->add('login', new Route('/login', [
        '_controller' => 'AuthController@login',
        '_middleware' => ['guest', 'csrf', 'rate_limit:login'],
    ], methods: ['GET', 'POST']));
    $routes->add('register', new Route('/register', [
        '_controller' => 'AuthController@register',
        '_middleware' => ['guest', 'csrf', 'rate_limit:register'],
    ], methods: ['GET', 'POST']));

    $routes->add('logout', new Route('/logout', [
        '_controller' => 'AuthController@logout',
        '_middleware' => ['auth'],
    ]));
    $routes->add('profile', new Route('/profile', [
        '_controller' => 'UserController@profile',
        '_middleware' => ['auth'],
    ]));
    $routes->add('profile_show', new Route('/profile/{id}', [
        '_controller' => 'UserController@profile',
        '_middleware' => ['auth'],
    ], ['id' => '\d+']));
    $routes->add('settings', new Route('/settings', [
        '_controller' => 'UserController@settings',
        '_middleware' => ['auth', 'csrf'],
    ], methods: ['GET', 'POST']));
    $routes->add('profile_referrals', new Route('/profile/referrals', [
        '_controller' => 'UserController@referrals',
        '_middleware' => ['auth'],
    ]));
    $routes->add('balance', new Route('/balance', [
        '_controller' => 'BalanceController@index',
        '_middleware' => ['auth'],
    ]));
    $routes->add('balance_history', new Route('/balance/history', [
        '_controller' => 'BalanceController@history',
        '_middleware' => ['auth'],
    ]));
    $routes->add('payment', new Route('/payment', [
        '_controller' => 'PaymentController@index',
        '_middleware' => ['auth'],
    ]));
    $routes->add('payment_success', new Route('/payment/success', [
        '_controller' => 'PaymentController@success',
        '_middleware' => ['auth'],
    ]));
    $routes->add('payment_yoomoney_form', new Route('/payment/yoomoney', [
        '_controller' => 'PaymentController@yoomoneyForm',
        '_middleware' => ['auth'],
    ]));
    $routes->add('payment_yoomoney_notify', new Route('/payment/yoomoney/notify', [
        '_controller' => 'PaymentController@yoomoneyNotify',
    ]));

    $routes->add('forum_create_thread_form', new Route('/forum/forum/{forumId}/create', [
        '_controller' => 'ForumController@createThreadForm',
        '_middleware' => ['auth'],
    ], ['forumId' => '\d+']));
    $routes->add('forum_create_thread', new Route('/forum/thread/create', [
        '_controller' => 'ForumController@createThread',
        '_middleware' => ['auth', 'csrf'],
    ]));
    $routes->add('forum_create_post', new Route('/forum/post/create', [
        '_controller' => 'ForumController@createPost',
        '_middleware' => ['auth', 'csrf'],
    ]));
    $routes->add('forum_like', new Route('/forum/like', [
        '_controller' => 'ForumController@like',
        '_middleware' => ['auth', 'csrf'],
    ]));
    $routes->add('forum_edit_thread', new Route('/forum/thread/edit/{id}', [
        '_controller' => 'ForumController@editThread',
        '_middleware' => ['auth', 'csrf'],
    ], ['id' => '\d+'], methods: ['GET', 'POST']));
    $routes->add('forum_delete_thread', new Route('/forum/thread/delete/{id}', [
        '_controller' => 'ForumController@deleteThread',
        '_middleware' => ['auth', 'csrf'],
    ], ['id' => '\d+']));
    $routes->add('forum_edit_post', new Route('/forum/post/edit/{id}', [
        '_controller' => 'ForumController@editPost',
        '_middleware' => ['auth', 'csrf'],
    ], ['id' => '\d+'], methods: ['GET', 'POST']));
    $routes->add('forum_delete_post', new Route('/forum/post/delete/{id}', [
        '_controller' => 'ForumController@deletePost',
        '_middleware' => ['auth', 'csrf'],
    ], ['id' => '\d+']));

    $routes->add('chat_send', new Route('/chat/send', [
        '_controller' => 'ChatController@sendMessage',
        '_middleware' => ['auth', 'csrf', 'rate_limit:chat_send'],
    ]));

    $routes->add('bans_request', new Route('/bans/request', [
        '_controller' => 'BanController@requestUnban',
        '_middleware' => ['auth', 'csrf'],
    ]));
    $routes->add('bans_paid', new Route('/bans/paid', [
        '_controller' => 'BanController@paidUnban',
        '_middleware' => ['auth', 'csrf'],
    ]));
    $routes->add('chat_delete', new Route('/chat/message/{id}', [
        '_controller' => 'ChatController@deleteMessage',
        '_middleware' => ['auth', 'permission:c', 'csrf'],
    ], ['id' => '\d+']));
    $routes->add('upload_image', new Route('/upload-image', [
        '_controller' => 'AdminController@uploadImage',
        '_middleware' => ['auth', 'csrf', 'rate_limit:upload_image'],
    ]));

    $routes->add('admin_dashboard', new Route('/admin', [
        '_controller' => 'AdminController@index',
        '_middleware' => ['auth', 'permission:a'],
    ]));
    $routes->add('admin_users', new Route('/admin/users', [
        '_controller' => 'AdminController@users',
        '_middleware' => ['auth', 'permission:a'],
    ]));
    $routes->add('admin_user_edit', new Route('/admin/users/edit/{id}', [
        '_controller' => 'AdminController@userEdit',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], ['id' => '\d+'], methods: ['GET', 'POST']));
    $routes->add('admin_logs', new Route('/admin/logs', [
        '_controller' => 'AdminController@logs',
        '_middleware' => ['auth', 'permission:a'],
    ]));
    $routes->add('admin_stats_registrations', new Route('/admin/stats/registrations', [
        '_controller' => 'AdminController@statsRegistrations',
        '_middleware' => ['auth', 'permission:a'],
    ]));

    $routes->add('admin_groups', new Route('/admin/groups', [
        '_controller' => 'AdminUserGroupController@index',
        '_middleware' => ['auth', 'permission:a'],
    ]));
    $routes->add('admin_groups_add', new Route('/admin/groups/add', [
        '_controller' => 'AdminUserGroupController@form',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], methods: ['GET', 'POST']));
    $routes->add('admin_groups_edit', new Route('/admin/groups/edit/{id}', [
        '_controller' => 'AdminUserGroupController@form',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], ['id' => '\d+'], methods: ['GET', 'POST']));
    $routes->add('admin_groups_delete', new Route('/admin/groups/delete/{id}', [
        '_controller' => 'AdminUserGroupController@delete',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], ['id' => '\d+']));

    $routes->add('admin_server_settings', new Route('/admin/server-settings', [
        '_controller' => 'AdminServerSettingsController@index',
        '_middleware' => ['auth', 'permission:a'],
    ]));
    $routes->add('admin_server_settings_add', new Route('/admin/server-settings/add', [
        '_controller' => 'AdminServerSettingsController@form',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], methods: ['GET', 'POST']));
    $routes->add('admin_server_settings_edit', new Route('/admin/server-settings/edit/{id}', [
        '_controller' => 'AdminServerSettingsController@form',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], ['id' => '\d+'], methods: ['GET', 'POST']));
    $routes->add('admin_server_settings_delete', new Route('/admin/server-settings/delete/{id}', [
        '_controller' => 'AdminServerSettingsController@delete',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], ['id' => '\d+']));
    $routes->add('admin_server_settings_test', new Route('/admin/server-settings/test/{id}', [
        '_controller' => 'AdminServerSettingsController@testConnection',
        '_middleware' => ['auth', 'permission:a'],
    ], ['id' => '\d+']));

    $routes->add('admin_services', new Route('/admin/services', [
        '_controller' => 'AdminServiceController@index',
        '_middleware' => ['auth', 'permission:a'],
    ]));
    $routes->add('admin_services_add', new Route('/admin/services/add', [
        '_controller' => 'AdminServiceController@form',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], methods: ['GET', 'POST']));
    $routes->add('admin_services_edit', new Route('/admin/services/edit/{id}', [
        '_controller' => 'AdminServiceController@form',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], ['id' => '\d+'], methods: ['GET', 'POST']));
    $routes->add('admin_services_delete', new Route('/admin/services/delete/{id}', [
        '_controller' => 'AdminServiceController@delete',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], ['id' => '\d+']));
    $routes->add('admin_services_api_tariffs', new Route('/api/services/{id}/tariffs', [
        '_controller' => 'AdminServiceController@apiTariffs',
        '_middleware' => ['auth', 'permission:a'],
    ], ['id' => '\d+']));
    $routes->add('admin_services_api_update', new Route('/api/services/{id}', [
        '_controller' => 'AdminServiceController@apiUpdateService',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], ['id' => '\d+'], methods: ['PUT']));
    $routes->add('admin_services_api_tariff_update', new Route('/api/services/{sid}/tariffs/{id}', [
        '_controller' => 'AdminServiceController@apiUpdateTariff',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], ['sid' => '\d+', 'id' => '\d+'], methods: ['PUT']));

    $routes->add('admin_user_services', new Route('/admin/users/{id}/services', [
        '_controller' => 'AdminUserServiceController@listForUser',
        '_middleware' => ['auth', 'permission:a'],
    ], ['id' => '\d+']));
    $routes->add('admin_user_services_add', new Route('/admin/users/{id}/services/add', [
        '_controller' => 'AdminUserServiceController@addForm',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], ['id' => '\d+'], methods: ['GET', 'POST']));
    $routes->add('admin_user_services_edit', new Route('/admin/users/{id}/services/edit/{usid}', [
        '_controller' => 'AdminUserServiceController@editForm',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], ['id' => '\d+', 'usid' => '\d+'], methods: ['GET', 'POST']));
    $routes->add('admin_user_services_delete', new Route('/admin/users/{id}/services/delete/{usid}', [
        '_controller' => 'AdminUserServiceController@delete',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], ['id' => '\d+', 'usid' => '\d+']));

    $routes->add('admin_forum_categories', new Route('/admin/forum/categories', [
        '_controller' => 'AdminForumController@categories',
        '_middleware' => ['auth', 'permission:a'],
    ]));
    $routes->add('admin_forum_categories_add', new Route('/admin/forum/categories/add', [
        '_controller' => 'AdminForumController@categoryForm',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], methods: ['GET', 'POST']));
    $routes->add('admin_forum_categories_edit', new Route('/admin/forum/categories/edit/{id}', [
        '_controller' => 'AdminForumController@categoryForm',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], ['id' => '\d+'], methods: ['GET', 'POST']));
    $routes->add('admin_forum_categories_delete', new Route('/admin/forum/categories/delete/{id}', [
        '_controller' => 'AdminForumController@categoryDelete',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], ['id' => '\d+']));
    $routes->add('admin_forum_categories_sort', new Route('/admin/forum/categories/sort', [
        '_controller' => 'AdminForumController@sortCategories',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ]));
    $routes->add('admin_forum_categories_forums', new Route('/admin/forum/categories/{categoryId}/forums', [
        '_controller' => 'AdminForumController@forums',
        '_middleware' => ['auth', 'permission:a'],
    ], ['categoryId' => '\d+']));
    $routes->add('admin_forum_category_forum_add', new Route('/admin/forum/categories/{categoryId}/forums/add', [
        '_controller' => 'AdminForumController@forumForm',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], ['categoryId' => '\d+'], methods: ['GET', 'POST']));
    $routes->add('admin_forum_category_forum_edit', new Route('/admin/forum/categories/{categoryId}/forums/edit/{fid}', [
        '_controller' => 'AdminForumController@forumForm',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], ['categoryId' => '\d+', 'fid' => '\d+'], methods: ['GET', 'POST']));
    $routes->add('admin_forum_category_forum_delete', new Route('/admin/forum/categories/{categoryId}/forums/delete/{fid}', [
        '_controller' => 'AdminForumController@forumDelete',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], ['categoryId' => '\d+', 'fid' => '\d+']));
    $routes->add('admin_forum_forums_sort', new Route('/admin/forum/forums/sort', [
        '_controller' => 'AdminForumController@sortForums',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ]));

    $routes->add('admin_news', new Route('/admin/news', [
        '_controller' => 'AdminNewsController@index',
        '_middleware' => ['auth', 'permission:a'],
    ]));
    $routes->add('admin_news_create', new Route('/admin/news/create', [
        '_controller' => 'AdminNewsController@form',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], methods: ['GET', 'POST']));
    $routes->add('admin_news_edit', new Route('/admin/news/edit/{id}', [
        '_controller' => 'AdminNewsController@form',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], ['id' => '\d+'], methods: ['GET', 'POST']));
    $routes->add('admin_news_delete', new Route('/admin/news/delete/{id}', [
        '_controller' => 'AdminNewsController@delete',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], ['id' => '\d+']));

    $routes->add('admin_site_settings', new Route('/admin/site-settings', [
        '_controller' => 'AdminSiteController@index',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], methods: ['GET', 'POST']));
    $routes->add('admin_site_settings_save', new Route('/admin/site-settings/save', [
        '_controller' => 'AdminSiteController@save',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ]));
    $routes->add('admin_security', new Route('/admin/security', [
        '_controller' => 'AdminSecurityController@index',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], methods: ['GET', 'POST']));
    $routes->add('admin_security_save', new Route('/admin/security/save', [
        '_controller' => 'AdminSecurityController@save',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ]));
    $routes->add('admin_seo', new Route('/admin/seo', [
        '_controller' => 'AdminSeoController@index',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], methods: ['GET', 'POST']));
    $routes->add('admin_seo_regenerate', new Route('/admin/seo/regenerate', [
        '_controller' => 'AdminSeoController@regenerateSitemap',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ]));
    $routes->add('admin_payments', new Route('/admin/payments', [
        '_controller' => 'AdminController@paymentSettings',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], methods: ['GET', 'POST']));
    $routes->add('admin_bans_settings', new Route('/admin/bans/settings', [
        '_controller' => 'AdminController@banSettings',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], methods: ['GET', 'POST']));
    $routes->add('admin_themes', new Route('/admin/themes', [
        '_controller' => 'AdminController@themes',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], methods: ['GET', 'POST']));
    $routes->add('admin_theme', new Route('/admin/theme', [
        '_controller' => 'AdminController@themeSettings',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ], methods: ['GET', 'POST']));
    $routes->add('admin_modules', new Route('/admin/modules', [
        '_controller' => 'AdminModuleController@index',
        '_middleware' => ['auth', 'permission:a'],
    ]));
    $routes->add('admin_modules_toggle', new Route('/admin/modules/toggle', [
        '_controller' => 'AdminModuleController@toggle',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ]));
    $routes->add('admin_theme_editor', new Route('/admin/theme-editor', [
        '_controller' => 'AdminThemeEditorController@editor',
        '_middleware' => ['auth', 'permission:a'],
    ]));
    $routes->add('admin_theme_editor_get_file', new Route('/admin/theme-editor/get-file', [
        '_controller' => 'AdminThemeEditorController@getFileContent',
        '_middleware' => ['auth', 'permission:a'],
    ]));
    $routes->add('admin_theme_editor_save_file', new Route('/admin/theme-editor/save-file', [
        '_controller' => 'AdminThemeEditorController@saveFileContent',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ]));
    $routes->add('admin_theme_editor_create', new Route('/admin/theme-editor/create', [
        '_controller' => 'AdminThemeEditorController@create',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ]));
    $routes->add('admin_theme_editor_delete', new Route('/admin/theme-editor/delete', [
        '_controller' => 'AdminThemeEditorController@delete',
        '_middleware' => ['auth', 'permission:a', 'csrf'],
    ]));

    $routes->add('admin_upload_image', new Route('/admin/upload-image', [
        '_controller' => 'AdminController@uploadImage',
        '_middleware' => ['auth', 'permission:c', 'csrf', 'rate_limit:upload_image'],
    ]));
};
