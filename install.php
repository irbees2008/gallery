<?php

if (!defined('BBCMS')) die ('HAL');

function plugin_gallery_install($action)
{
    global $mysql, $config;

    if ($action != 'autoapply') {
        Lang::loadPlugin('gallery', 'admin', '', ':');
    }

    // Fill DB_UPDATE configuration scheme
    $db_update = array(
        array(
            'table' => 'gallery',
            'action' => 'cmodify',
            'key' => 'primary key(id)',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'id', 'type' => 'int(11)', 'params' => 'auto_increment'),
                array('action' => 'cmodify', 'name' => 'icon', 'type' => 'varchar(255)', 'params' => "default ''"),
                array('action' => 'cmodify', 'name' => 'name', 'type' => 'varchar(255)', 'params' => "default ''"),
                array('action' => 'cmodify', 'name' => 'title', 'type' => 'varchar(50)', 'params' => "default ''"),
                array('action' => 'cmodify', 'name' => 'description', 'type' => 'text', 'params' => "default ''"),
                array('action' => 'cmodify', 'name' => 'keywords', 'type' => 'text', 'params' => "default ''"),
                array('action' => 'cmodify', 'name' => 'position', 'type' => 'int(11)', 'params' => "default 0"),
                array('action' => 'cmodify', 'name' => 'images_count', 'type' => 'smallint(3)', 'params' => "default 12"),
                array('action' => 'cmodify', 'name' => 'if_active', 'type' => 'tinyint(1)', 'params' => "default 0"),
                array('action' => 'cmodify', 'name' => 'skin', 'type' => 'varchar(25)', 'params' => "default 'basic'"),
            )
        ),
        array(
            'table'  => 'comments',
            'action' => 'cmodify',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'module', 'type' => 'char(100)', 'params' => "default 'news'"),
            )
        ),
        array(
            'table'  => 'images',
            'action' => 'cmodify',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'user_id', 'type' => 'int(11)', 'params' => "default 0"),
                array('action' => 'cmodify', 'name' => 'com', 'type' => 'int(11)', 'params' => "default 0"),
                array('action' => 'cmodify', 'name' => 'views', 'type' => 'int(11)', 'params' => "default 0"),
                array('action' => 'cmodify', 'name' => 'allow_com', 'type' => 'tinyint(1)', 'params' => "default '2'"),
            )
        ),
    );

    $ULIB = new UrlLibrary();
    $ULIB->loadConfig();
    $ULIB->registerCommand('gallery', '',
        array ('vars' => array(
                '' => array(
                    'matchRegex' => '.+?', 
                    'descr' => array(
                        $config['default_lang'] => __('gallery:ULIB_main')
                    )
                ),
                'page' => array(
                    'matchRegex' => '\d{1,4}', 
                    'descr' => array(
                        $config['default_lang'] => __('gallery:ULIB_page')
                    )
                ),
            ),
            'descr' => array ($config['default_lang'] => __('gallery:ULIB_main_d')),
        )
    );

    $ULIB->registerCommand('gallery', 'gallery',
        array ('vars' => array(
                'name' => array(
                    'matchRegex' => '.+?', 
                    'descr' => array(
                        $config['default_lang'] => __('gallery:ULIB_name')
                        )
                    ),
                'id' => array(
                    'matchRegex' => '\d{1,4}', 
                    'descr' => array(
                        $config['default_lang'] => __('gallery:ULIB_id')
                        )
                    ),
                'page' => array(
                    'matchRegex' => '\d{1,4}', 
                    'descr' => array(
                        $config['default_lang'] => __('gallery:ULIB_page')
                    )
                ),
            ),
            'descr' => array ($config['default_lang'] => __('gallery:ULIB_gallery_d')),
        )
    );

    $ULIB->registerCommand('gallery', 'image',
        array ('vars' => array(
                'gallery' => array(
                    'matchRegex' => '.+?', 
                    'descr' => array(
                        $config['default_lang'] => __('gallery:ULIB_name')
                        )
                    ),
                'name' => array(
                    'matchRegex' => '.+?', 
                    'descr' => array(
                        $config['default_lang'] => __('gallery:ULIB_image_name')
                        )
                    ),
                'id' => array(
                    'matchRegex' => '\d{1,4}', 
                    'descr' => array(
                        $config['default_lang'] => __('gallery:ULIB_image_id')
                        )
                    ),
            ),
            'descr' => array ($config['default_lang'] => __('gallery:ULIB_image_d')),
        )
    );

    $ULIB->registerCommand('gallery', 'widget',
        array ('vars' => array(
                'name' => array(
                    'matchRegex' => '.+?', 
                    'descr' => array(
                        $config['default_lang'] => __('gallery:label_widget_name')
                        )
                    ),
                'id' => array(
                    'matchRegex' => '\d{1,4}', 
                    'descr' => array(
                        $config['default_lang'] => 'Код виджета'
                        )
                    ),
                'sort' => array(
                    'matchRegex' => '\d{1,4}', 
                    'descr' => array(
                        $config['default_lang'] => 'Сортировка'
                    )
                ),
            ),
            'descr' => array ($config['default_lang'] => __('gallery:ULIB_gallery_d')),
        )
    );

    $UHANDLER = new UrlHandler();
    $UHANDLER->loadConfig();
    $UHANDLER->registerHandler(0,
        array (
    'pluginName' => 'gallery',
    'handlerName' => 'gallery',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/plugin/gallery/{name}[/page-{page}]/',
      'regex' => '#^/plugin/gallery/(.+?)(?:/page-(\\d{1,4})){0,1}/$#',
      'regexMap' => 
      array (
        1 => 'name',
        2 => 'page',
      ),
      'reqCheck' => 
      array (
      ),
      'setVars' => 
      array (
      ),
      'genrMAP' => 
      array (
        0 => 
        array (
          0 => 0,
          1 => '/plugin/gallery/',
          2 => 0,
        ),
        1 => 
        array (
          0 => 1,
          1 => 'name',
          2 => 0,
        ),
        2 => 
        array (
          0 => 0,
          1 => '/page-',
          2 => 1,
        ),
        3 => 
        array (
          0 => 1,
          1 => 'page',
          2 => 1,
        ),
        4 => 
        array (
          0 => 0,
          1 => '/',
          2 => 0,
        ),
      ),
    ),
  )
    );

    $UHANDLER->registerHandler(0,
        array (
    'pluginName' => 'gallery',
    'handlerName' => 'image',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/plugin/gallery/{gallery}/image/{name}[/page-{page}]/',
      'regex' => '#^/plugin/gallery/(.+?)/image/(.+?)(?:/page-()){0,1}/$#',
      'regexMap' => 
      array (
        1 => 'gallery',
        2 => 'name',
        3 => 'page',
      ),
      'reqCheck' => 
      array (
      ),
      'setVars' => 
      array (
      ),
      'genrMAP' => 
      array (
        0 => 
        array (
          0 => 0,
          1 => '/plugin/gallery/',
          2 => 0,
        ),
        1 => 
        array (
          0 => 1,
          1 => 'gallery',
          2 => 0,
        ),
        2 => 
        array (
          0 => 0,
          1 => '/image/',
          2 => 0,
        ),
        3 => 
        array (
          0 => 1,
          1 => 'name',
          2 => 0,
        ),
        4 => 
        array (
          0 => 0,
          1 => '/page-',
          2 => 1,
        ),
        5 => 
        array (
          0 => 1,
          1 => 'page',
          2 => 1,
        ),
        6 => 
        array (
          0 => 0,
          1 => '/',
          2 => 0,
        ),
      ),
    ),
  )
    );

    $UHANDLER->registerHandler(0,
        array (
    'pluginName' => 'gallery',
    'handlerName' => '',
    'flagPrimary' => true,
    'flagFailContinue' => false,
    'flagDisabled' => false,
    'rstyle' => 
    array (
      'rcmd' => '/plugin/gallery[/page-{page}]/',
      'regex' => '#^/plugin/gallery(?:/page-(\\d{1,4})){0,1}/$#',
      'regexMap' => 
      array (
        1 => 'page',
      ),
      'reqCheck' => 
      array (
      ),
      'setVars' => 
      array (
      ),
      'genrMAP' => 
      array (
        0 => 
        array (
          0 => 0,
          1 => '/plugin/gallery',
          2 => 0,
        ),
        1 => 
        array (
          0 => 0,
          1 => '/page-',
          2 => 1,
        ),
        2 => 
        array (
          0 => 1,
          1 => 'page',
          2 => 1,
        ),
        3 => 
        array (
          0 => 0,
          1 => '/',
          2 => 0,
        ),
      ),
    ),
  )
    );

    // Apply requested action
    switch ($action) {
        case 'confirm':
            generate_install_page('gallery', __('gallery:desc_install'));
            break;
        case 'autoapply':
        case 'apply':
            if (fixdb_plugin_install('gallery', $db_update, 'install', ('autoapply' == $action) ? true : false)) {

                // Обновляем поле module в комментариях, если не задано
                $mysql->query("update ".prefix."_comments set module='news' where module=''");

                // Set default values if values are not set [for new variables]
                foreach ([
                    'if_description' => 1,
                    'if_keywords' => 1,
                    'galleries_count' => 6,
                    'skin' => 'basic',
                    'cache' => 1,
                    'cache_expire' => 60,
                    ] as $k => $v ) {
                        pluginSetVariable('gallery', $k, $v);
                }

                // Load CORE Plugin
                $cPlugin = CPlugin::instance();
                // Save configuration parameters of plugins
                $cPlugin->saveConfig();
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
