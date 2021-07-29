<?php

// Protect against hack attempts
if (! defined('NGCMS')) {
    die('HAL');
}

// // Если не активированы плагин комментариев, то выходим.
// if (! getPluginStatusActive('comments')) {
//     return false;
// }

// Подгрузка языковых файлов плагинов.
LoadPluginLang('gallery', 'site', '', '', ':');
loadPluginLang('comments', 'main', '', '', ':');

loadPluginLibrary('gallery', 'library');

$gallery = new PluginGallery();

registerActionHandler('index', [$gallery, 'categoryAction']);
registerActionHandler('index_post', [$gallery, 'widgetAction']);

register_plugin_page('gallery', '', 'plugin_gallery_index_page_action');
register_plugin_page('gallery', 'gallery', 'plugin_gallery_page_action');
register_plugin_page('gallery', 'image', 'plugin_gallery_image_action');

function plugin_gallery_index_page_action(array $params = [])
{
    return (new PluginGallery)
        ->indexPageAction($params);
}

function plugin_gallery_page_action(array $params = [])
{
    return (new PluginGallery)
        ->galleryPageAction($params);
}

function plugin_gallery_image_action(array $params = [])
{
    return (new PluginGallery)
        ->imagePageAction($params);
}
