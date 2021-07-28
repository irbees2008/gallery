<?php

// Configuration file for plugin

// Protect against hack attempts
if (! defined('NGCMS')) {
    die('HAL');
}

// Load lang files
loadPluginLang($plugin, 'admin', '', '', ':');

$db_update = [
    [
        'table' => 'images',
        'action' => 'modify',
        'fields' => [
            ['action' => 'drop', 'name' => 'com'],
            ['action' => 'drop', 'name' => 'views'],
        ],
    ],
    [
        'table' => 'gallery',
        'action' => 'drop',
    ],
];

if ('commit' == $action) {
    if (fixdb_plugin_install('gallery', $db_update, 'deinstall')) {
        $ULIB = new UrlLibrary();
        $ULIB->loadConfig();
        $ULIB->removeCommand('gallery', 'image');
        $ULIB->removeCommand('gallery', 'gallery');
        $ULIB->removeCommand('gallery', '');

        $UHANDLER = new UrlHandler();
        $UHANDLER->loadConfig();
        $UHANDLER->removePluginHandlers('gallery', 'image');
        $UHANDLER->removePluginHandlers('gallery', 'gallery');
        $UHANDLER->removePluginHandlers('gallery', '');

        // Load CORE Plugin
        $cPlugin = CPlugin::instance();
        // Load list of active plugins
        $plugins = $cPlugin->getList();
        unset($plugins['config']['gallery']);
        // Save configuration parameters of plugins
        $cPlugin->setConfig($plugins['config']);
        // Save configuration parameters of plugins
        $cPlugin->saveConfig();
        $ULIB->saveConfig();
        $UHANDLER->saveConfig();

        plugin_mark_deinstalled('gallery');
    }
} else {
    generate_install_page('gallery', $lang['gallery:desc_deinstall'], 'deinstall');
}
