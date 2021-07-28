<?php

// Protect against hack attempts
if (! defined('NGCMS')) {
    die('HAL');
}

function plugin_gallery_install($action)
{
    global $lang, $mysql, $config;

    if ('autoapply' !== $action) {
        loadPluginLang('gallery', 'admin', '', '', ':');
    }

    // Fill DB_UPDATE configuration scheme
    $db_update = [
        [
            'table' => 'gallery',
            'action' => 'cmodify',
            'key' => 'primary key(id)',
            'fields' => [
                ['action' => 'cmodify', 'name' => 'id', 'type' => 'int(11)', 'params' => 'auto_increment'],
                ['action' => 'cmodify', 'name' => 'icon', 'type' => 'varchar(255)', 'params' => "default ''"],
                ['action' => 'cmodify', 'name' => 'name', 'type' => 'varchar(255)', 'params' => "default ''"],
                ['action' => 'cmodify', 'name' => 'title', 'type' => 'varchar(50)', 'params' => "default ''"],
                ['action' => 'cmodify', 'name' => 'description', 'type' => 'text', 'params' => "default ''"],
                ['action' => 'cmodify', 'name' => 'keywords', 'type' => 'text', 'params' => "default ''"],
                ['action' => 'cmodify', 'name' => 'position', 'type' => 'int(11)', 'params' => 'default 0'],
                ['action' => 'cmodify', 'name' => 'images_count', 'type' => 'smallint(3)', 'params' => 'default 12'],
                ['action' => 'cmodify', 'name' => 'if_active', 'type' => 'tinyint(1)', 'params' => 'default 0'],
                ['action' => 'cmodify', 'name' => 'skin', 'type' => 'varchar(25)', 'params' => "default 'basic'"],
            ],
        ],
        [
            'table' => 'comments',
            'action' => 'cmodify',
            'fields' => [
                ['action' => 'cmodify', 'name' => 'module', 'type' => 'char(100)', 'params' => "default 'news'"],
            ],
        ],
        [
            'table' => 'images',
            'action' => 'cmodify',
            'fields' => [
                ['action' => 'cmodify', 'name' => 'user_id', 'type' => 'int(11)', 'params' => 'default 0'],
                ['action' => 'cmodify', 'name' => 'com', 'type' => 'int(11)', 'params' => 'default 0'],
                ['action' => 'cmodify', 'name' => 'views', 'type' => 'int(11)', 'params' => 'default 0'],
                ['action' => 'cmodify', 'name' => 'allow_com', 'type' => 'tinyint(1)', 'params' => "default '2'"],
            ],
        ],
    ];

    $ULIB = new UrlLibrary();
    $ULIB->loadConfig();
    $ULIB->registerCommand(
        'gallery',
        '',
        ['vars' => [
            '' => [
                'matchRegex' => '.+?',
                'descr' => [
                    $config['default_lang'] => $lang['gallery:ULIB_main'],
                ],
            ],
            'page' => [
                'matchRegex' => '\d{1,4}',
                'descr' => [
                    $config['default_lang'] => $lang['gallery:ULIB_page'],
                ],
            ],
        ],
            'descr' => [$config['default_lang'] => $lang['gallery:ULIB_main_d']],
        ]
    );

    $ULIB->registerCommand(
        'gallery',
        'gallery',
        ['vars' => [
            'name' => [
                'matchRegex' => '.+?',
                'descr' => [
                    $config['default_lang'] => $lang['gallery:ULIB_name'],
                ],
            ],
            'id' => [
                'matchRegex' => '\d{1,4}',
                'descr' => [
                    $config['default_lang'] => $lang['gallery:ULIB_id'],
                ],
            ],
            'page' => [
                'matchRegex' => '\d{1,4}',
                'descr' => [
                    $config['default_lang'] => $lang['gallery:ULIB_page'],
                ],
            ],
        ],
            'descr' => [$config['default_lang'] => $lang['gallery:ULIB_gallery_d']],
        ]
    );

    $ULIB->registerCommand(
        'gallery',
        'image',
        ['vars' => [
            'gallery' => [
                'matchRegex' => '.+?',
                'descr' => [
                    $config['default_lang'] => $lang['gallery:ULIB_name'],
                ],
            ],
            'name' => [
                'matchRegex' => '.+?',
                'descr' => [
                    $config['default_lang'] => $lang['gallery:ULIB_image_name'],
                ],
            ],
            'id' => [
                'matchRegex' => '\d{1,4}',
                'descr' => [
                    $config['default_lang'] => $lang['gallery:ULIB_image_id'],
                ],
            ],
        ],
            'descr' => [$config['default_lang'] => $lang['gallery:ULIB_image_d']],
        ]
    );

    $ULIB->registerCommand(
        'gallery',
        'widget',
        ['vars' => [
            'name' => [
                'matchRegex' => '.+?',
                'descr' => [
                    $config['default_lang'] => $lang['gallery:label_widget_name'],
                ],
            ],
            'id' => [
                'matchRegex' => '\d{1,4}',
                'descr' => [
                    $config['default_lang'] => 'Код виджета',
                ],
            ],
            'sort' => [
                'matchRegex' => '\d{1,4}',
                'descr' => [
                    $config['default_lang'] => 'Сортировка',
                ],
            ],
        ],
            'descr' => [$config['default_lang'] => $lang['gallery:ULIB_gallery_d']],
        ]
    );

    $UHANDLER = new UrlHandler();
    $UHANDLER->loadConfig();
    $UHANDLER->registerHandler(
        0,
        [
            'pluginName' => 'gallery',
            'handlerName' => 'gallery',
            'flagPrimary' => true,
            'flagFailContinue' => false,
            'flagDisabled' => false,
            'rstyle' => [
                'rcmd' => '/plugin/gallery/{name}[/page-{page}]/',
                'regex' => '#^/plugin/gallery/(.+?)(?:/page-(\\d{1,4})){0,1}/$#',
                'regexMap' => [
                    1 => 'name',
                    2 => 'page',
                ],
                'reqCheck' => [
                ],
                'setVars' => [
                ],
                'genrMAP' => [
                    0 => [
                        0 => 0,
                        1 => '/plugin/gallery/',
                        2 => 0,
                    ],
                    1 => [
                        0 => 1,
                        1 => 'name',
                        2 => 0,
                    ],
                    2 => [
                        0 => 0,
                        1 => '/page-',
                        2 => 1,
                    ],
                    3 => [
                        0 => 1,
                        1 => 'page',
                        2 => 1,
                    ],
                    4 => [
                        0 => 0,
                        1 => '/',
                        2 => 0,
                    ],
                ],
            ],
        ]
    );

    $UHANDLER->registerHandler(
        0,
        [
            'pluginName' => 'gallery',
            'handlerName' => 'image',
            'flagPrimary' => true,
            'flagFailContinue' => false,
            'flagDisabled' => false,
            'rstyle' => [
                'rcmd' => '/plugin/gallery/{gallery}/image/{name}[/page-{page}]/',
                'regex' => '#^/plugin/gallery/(.+?)/image/(.+?)(?:/page-()){0,1}/$#',
                'regexMap' => [
                    1 => 'gallery',
                    2 => 'name',
                    3 => 'page',
                ],
                'reqCheck' => [
                ],
                'setVars' => [
                ],
                'genrMAP' => [
                    0 => [
                        0 => 0,
                        1 => '/plugin/gallery/',
                        2 => 0,
                    ],
                    1 => [
                        0 => 1,
                        1 => 'gallery',
                        2 => 0,
                    ],
                    2 => [
                        0 => 0,
                        1 => '/image/',
                        2 => 0,
                    ],
                    3 => [
                        0 => 1,
                        1 => 'name',
                        2 => 0,
                    ],
                    4 => [
                        0 => 0,
                        1 => '/page-',
                        2 => 1,
                    ],
                    5 => [
                        0 => 1,
                        1 => 'page',
                        2 => 1,
                    ],
                    6 => [
                        0 => 0,
                        1 => '/',
                        2 => 0,
                    ],
                ],
            ],
        ]
    );

    $UHANDLER->registerHandler(
        0,
        [
            'pluginName' => 'gallery',
            'handlerName' => '',
            'flagPrimary' => true,
            'flagFailContinue' => false,
            'flagDisabled' => false,
            'rstyle' => [
                'rcmd' => '/plugin/gallery[/page-{page}]/',
                'regex' => '#^/plugin/gallery(?:/page-(\\d{1,4})){0,1}/$#',
                'regexMap' => [
                    1 => 'page',
                ],
                'reqCheck' => [
                ],
                'setVars' => [
                ],
                'genrMAP' => [
                    0 => [
                        0 => 0,
                        1 => '/plugin/gallery',
                        2 => 0,
                    ],
                    1 => [
                        0 => 0,
                        1 => '/page-',
                        2 => 1,
                    ],
                    2 => [
                        0 => 1,
                        1 => 'page',
                        2 => 1,
                    ],
                    3 => [
                        0 => 0,
                        1 => '/',
                        2 => 0,
                    ],
                ],
            ],
        ]
    );

    // Apply requested action
    switch ($action) {
        case 'confirm':
            generate_install_page('gallery', $lang['gallery:desc_install']);
            break;
        case 'autoapply':
        case 'apply':
            if (fixdb_plugin_install('gallery', $db_update, 'install', ('autoapply' === $action) ? true : false)) {

                // Обновляем поле module в комментариях, если не задано
                $mysql->query('update '.prefix."_comments set module='news' where module=''");

                // Set default values if values are not set [for new variables]
                foreach ([
                    'if_description' => 1,
                    'if_keywords' => 1,
                    'galleries_count' => 6,
                    'skin' => 'basic',
                    'cache' => 1,
                    'cache_expire' => 60,
                ] as $k => $v) {
                    pluginSetVariable('gallery', $k, $v);
                }

                // Save configuration parameters of plugins
                pluginsSaveConfig();
                $ULIB->saveConfig();
                $UHANDLER->saveConfig();

                plugin_mark_installed('gallery');
            } else {
                return false;
            }
            break;
    }
    return true;
}
