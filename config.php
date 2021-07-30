<?php

// Configuration file for plugin

// Protect against hack attempts
if (! defined('NGCMS')) {
    die('HAL');
}

// Load lang files
loadPluginLang($plugin, 'admin', '', '', ':');

// Set default values if values are not set [for new variables]
foreach ([
    'if_description' => 1,
    'if_keywords' => 1,
    'galleries_count' => 6,
    'skin' => 'basic',
    'cache' => 1,
    'cache_expire' => 60,
] as $k => $v) {
    if (is_null(pluginGetVariable($plugin, $k))) {
        pluginSetVariable($plugin, $k, $v);
    }
}

// Micro sRouter =)
switch ($section = $_REQUEST['section']) {
    case 'list':
    case 'update':
    case 'dell':
    case 'edit_submit':
    case 'move_up':
    case 'move_down':
        showList($plugin, $section);
        break;

    case 'edit':
        edit($plugin, $section);
        break;

    case 'widget_list':
    case 'widget_edit_submit':
    case 'widget_dell':
        showWidgetList($plugin, $section);
        break;

    case 'widget_add':
        editWidget($plugin, $section);
        break;

    default:
        main($plugin, $section ?: '');
        break;
}

function main(string $plugin, string $section = '')
{
    global $lang, $twig;

    // Prepare configuration parameters
    if (empty($skList = skinsListForPlugin($plugin))) {
        msg(['type' => 'error', 'text' => $lang[$plugin.':msg.no_skin']]);
    }

    $data = [
        'section' => '',
    ];

    // Check to dependence plugin
    $data['dependencies'] = [];
    if (! getPluginStatusActive('comments')) {
        $data['dependencies'][] = 'comments';
    }

    // Fill configuration parameters

    $tpath = locatePluginTemplates(['navigation'], 'gallery', 0, '', 'admin');

    $cfg = [
        [
            'descr' => $lang[$plugin.':description'],
        ],
        [
            'name' => 'section',
            'type' => 'hidden',
            'value' => 'commit',
        ],
        [
            'type' => 'flat',
            'input' => $twig->render(
                $tpath['navigation'].'navigation.tpl', $data
            ),
        ],
    ];

    $cfgX = [];
    array_push($cfgX, [
        'name' => 'seo_title',
        'title' => $lang[$plugin.':seo_title'],
        'descr' => $lang[$plugin.':seo_title#desc'],
        'type' => 'input',
        'value' => pluginGetVariable($plugin, 'seo_title'),
    ]);
    array_push($cfgX, [
        'name' => 'seo_description',
        'title' => $lang[$plugin.':seo_description'],
        'descr' => $lang[$plugin.':seo_description#desc'],
        'type' => 'input',
        'value' => pluginGetVariable($plugin, 'seo_description'),
    ]);
    array_push($cfgX, [
        'name' => 'seo_keywords',
        'title' => $lang[$plugin.':seo_keywords'],
        'descr' => $lang[$plugin.':seo_keywords#desc'],
        'type' => 'input',
        'value' => pluginGetVariable($plugin, 'seo_keywords'),
    ]);
    array_push($cfg, [
        'mode' => 'group',
        'title' => $lang[$plugin.':group.seo'],
        'entries' => $cfgX,
    ]);

    $cfgX = [];
    array_push($cfgX, [
        'name' => 'if_description',
        'title' => $lang[$plugin.':label_if_description'],
        'descr' => $lang[$plugin.':desc_if_description'],
        'type' => 'select',
        'values' => ['1' => $lang['yesa'], '0' => $lang['noa']],
        'value' => pluginGetVariable($plugin, 'if_description'),
    ]);
    array_push($cfgX, [
        'name' => 'if_keywords',
        'title' => $lang[$plugin.':label_if_keywords'],
        'descr' => $lang[$plugin.':desc_if_keywords'],
        'type' => 'select',
        'values' => ['1' => $lang['yesa'], '0' => $lang['noa']],
        'value' => pluginGetVariable($plugin, 'if_keywords'),
    ]);
    array_push($cfgX, [
        'name' => 'galleries_count',
        'title' => $lang[$plugin.':label_images_count'],
        'descr' => $lang[$plugin.':desc_images_count'],
        'type' => 'input',
        'value' => pluginGetVariable($plugin, 'galleries_count'),
    ]);
    array_push($cfg, [
        'mode' => 'group',
        'title' => $lang[$plugin.':group.general'],
        'entries' => $cfgX,
    ]);

    $cfgX = [];
    array_push($cfgX, [
        'name' => 'skin',
        'title' => $lang[$plugin.':skin'],
        'descr' => $lang[$plugin.':skin#desc'],
        'type' => 'select',
        'values' => $skList,
        'value' => pluginGetVariable($plugin, 'skin'),
    ]);
    array_push($cfg, [
        'mode' => 'group',
        'title' => $lang[$plugin.':group.source'],
        'entries' => $cfgX,
    ]);

    $cfgX = [];
    array_push($cfgX, [
        'name' => 'cache',
        'title' => $lang[$plugin.':cache'],
        'descr' => $lang[$plugin.':cache#desc'],
        'type' => 'select',
        'values' => ['1' => $lang['yesa'], '0' => $lang['noa']],
        'value' => pluginGetVariable($plugin, 'cache'),
    ]);
    array_push($cfgX, [
        'name' => 'cache_expire',
        'title' => $lang[$plugin.':cache_expire'],
        'descr' => $lang[$plugin.':cache_expire#desc'],
        'type' => 'input',
        'value' => pluginGetVariable($plugin, 'cache_expire'),
    ]);
    array_push($cfg, [
        'mode' => 'group',
        'title' => $lang[$plugin.':group.cache'],
        'entries' => $cfgX,
    ]);

    if ('commit' === $section) {
        commit_plugin_config_changes($plugin, $cfg);

        msg(['text' => $lang['commited']]);

        return main($plugin, '');
    }

    generate_config_page($plugin, $cfg);
}

function showList($plugin, $section)
{
    global $twig, $lang, $mysql, $parse;

    $data = [
        'section' => 'list',
    ];

    // Fill configuration parameters

    $tpath = locatePluginTemplates(['navigation'], 'gallery', 0, '', 'admin');

    $cfg = [
        [
            'name' => 'section',
            'type' => 'hidden',
            'value' => 'update',
        ],
        [
            'type' => 'flat',
            'input' => $twig->render(
                $tpath['navigation'].'navigation.tpl', $data
            ),
        ],
    ];

    $lang['commit_change'] = $lang[$plugin.':button_update'];

    // RUN
    do {
        if ('update' === $section) {
            $gallery = $mysql->select('select name from '.prefix.'_gallery');
            $next_order = count($gallery) + 1;
            if ($dir = opendir(images_dir)) {
                while ($file = readdir($dir)) {
                    if (! is_dir(images_dir.'/'.$file) or $file == '.' or $file == '..' or GetKeyFromName($file, $gallery) !== false) {
                        continue;
                    }
                    $mysql->query('insert '.prefix.'_gallery '.
                        '(name, title, position) values '.
                        '('.db_squote($file).', '.db_squote($file).', '.db_squote($next_order).')');
                    $next_order++;
                }
                closedir($dir);
                msg(['text' => $lang[$plugin.':info_update_record']]);
            }
        } elseif ('edit_submit' === $section) {
            if (! isset($_POST['id']) or ! isset($_POST['title']) or ! isset($_POST['if_active']) or ! isset($_POST['skin']) or ! isset($_POST['icon']) or ! isset($_POST['description']) or ! isset($_POST['keywords']) or ! isset($_POST['images_count'])) {
                msg(['type' => 'error', 'text' => $lang[$plugin.':msg.no_save_params']]);
                break;
            }
            $id = intval($_POST['id']);

            $gallery = $mysql->record('select * from '.prefix.'_gallery where `id`='.db_squote($id).' limit 1');

            if (! $gallery) {
                msg(['type' => 'error', 'text' => $lang[$plugin.':no_gallery']]);
                break;
            }

            $title = secure_html($_POST['title']);
            $skin = secure_html($_POST['skin']);
            $images_count = ! empty($_POST['images_count']) ? abs(intval($_POST['images_count'])) : 12;
            $if_active = intval($_POST['if_active']);
            $icon = secure_html($_POST['icon']);
            $description = secure_html(str_replace(["\r\n", "\n", '  '], [' '], $_POST['description']));
            $keywords = secure_html($_POST['keywords']);

            $t_update = '';
            if ($title != $gallery['title']) {
                $t_update .= (($t_update ? ', ' : '').'`title`='.db_squote($title));
            }
            if ($skin != $gallery['skin']) {
                $t_update .= (($t_update ? ', ' : '').'`skin`='.db_squote($skin));
            }
            if ($images_count != $gallery['images_count']) {
                $t_update .= (($t_update ? ', ' : '').'`images_count`='.db_squote($images_count));
            }
            if ($if_active != $gallery['if_active']) {
                $t_update .= (($t_update ? ', ' : '').'`if_active`='.db_squote($if_active));
            }
            if ($icon != $gallery['icon']) {
                $t_update .= (($t_update ? ', ' : '').'`icon`='.db_squote($icon));
            }
            if ($description != $gallery['description']) {
                $t_update .= (($t_update ? ', ' : '').'`description`='.db_squote($description));
            }
            if ($keywords != $gallery['keywords']) {
                $t_update .= (($t_update ? ', ' : '').'`keywords`='.db_squote($keywords));
            }

            if ($t_update) {
                $mysql->query('update '.prefix.'_gallery set '.$t_update.' where id = '.db_squote($id).' limit 1');
                msg(['text' => $lang[$plugin.':info_update_record']]);
            } else {
                msg(['type' => 'info', $lang[$plugin.':msg.no_updated']]);
            }
        } elseif ('move_up' === $section or 'move_down' === $section) {
            if (empty($_REQUEST['id'])) {
                msg(['type' => 'error', 'text' => $lang[$plugin.':msg.no_gallery']]);
                break;
            }
            $id = intval($_REQUEST['id']);

            $gallery = $mysql->record('select id, position from '.prefix.'_gallery where `id`='.db_squote($id).' limit 1');
            if (! $gallery) {
                msg(['type' => 'error', 'text' => $lang[$plugin.':msg.no_gallery']]);
                break;
            }
            $count = 0;
            if (is_array($pcnt = $mysql->record('select count(*) as cnt from '.prefix.'_gallery'))) {
                $count = $pcnt['cnt'];
            }

            if ($section === 'move_up') {
                if ($gallery['position'] == 1) {
                    msg(['type' => 'error', 'text' => $lang[$plugin.':info_update_record']]);
                    break;
                }

                $gallery2 = $mysql->record('select id, position from '.prefix.'_gallery where position='.db_squote($gallery['position'] - 1).' limit 1');

                $mysql->query('update '.prefix.'_gallery set position='.db_squote($gallery['position']).'where `id`='.db_squote($gallery2['id']).' limit 1');
                $mysql->query('update '.prefix.'_gallery set position='.db_squote($gallery2['position']).'where `id`='.db_squote($gallery['id']).' limit 1');
            } elseif ($section === 'move_down') {
                if ($gallery['position'] == $count) {
                    msg(['type' => 'error', 'text' => $lang[$plugin.':info_update_record']]);
                    break;
                }

                $gallery2 = $mysql->record('select id, position from '.prefix.'_gallery where position='.db_squote($gallery['position'] + 1).' limit 1');

                $mysql->query('update '.prefix.'_gallery set position='.db_squote($gallery['position']).'where `id`='.db_squote($gallery2['id']).' limit 1');
                $mysql->query('update '.prefix.'_gallery set position='.db_squote($gallery2['position']).'where `id`='.db_squote($gallery['id']).' limit 1');
            }
            msg(['type' => 'info', 'text' => $lang[$plugin.':info_update_record']]);
        } elseif ('dell' === $section) {
            if (empty($_REQUEST['id'])) {
                msg(['type' => 'error', 'text' => $lang[$plugin.':msg.no_gallery']]);
                break;
            }
            $id = intval($_REQUEST['id']);
            $gallery = $mysql->record('select `title` from '.prefix.'_gallery where `id`='.db_squote($id).' limit 1');
            if (! $gallery) {
                msg(['type' => 'error', 'text' => $lang[$plugin.':msg.no_gallery']]);
                break;
            }
            $mysql->query('delete from '.prefix.'_gallery where `id`='.db_squote($id));
            $next_order = 1;
            foreach ($mysql->select('select id from '.prefix.'_gallery order by position') as $row) {
                $dir = opendir(images_dir);
                $mysql->query('update '.prefix.'_gallery set position='.db_squote($next_order).'where `id`='.db_squote($row['id']).' limit 1');
                $next_order++;
            }
            msg(['type' => 'info', 'text' => $lang[$plugin.':info_delete']]);
        }

        if ('list' !== $section and pluginGetVariable('gallery', 'cache')) {
            clearCacheFiles($plugin);
        }
    } while (0);

    $tVars = [];
    $rows = $mysql->select('select * from '.prefix.'_gallery order by position');
    foreach ($rows as $row) {
        // Prepare data for template
        $tVars['items'][] = [
            'isActive' => $row['if_active'],
            'id' => $row['id'],
            'name' => $row['name'],
            'title' => $row['title'],
            'url' => generatePluginLink('gallery', 'gallery', ['id' => $row['id'], 'name' => $row['name']]),
            'skin' => $row['skin'],
        ];
    }

    $tpath = locatePluginTemplates(['gallery.list'], 'gallery', 0, '', 'admin');

    array_push($cfg, [
        'type' => 'flat',
        'input' => $twig->render($tpath['gallery.list'].'gallery.list.tpl', $tVars),
    ]);

    generate_config_page($plugin, $cfg);
}

function edit($plugin, $section)
{
    global $lang, $mysql, $twig;

    // Prepare configuration parameters
    if (empty($skList = skinsListForPlugin($plugin))) {
        msg(['type' => 'error', 'text' => $lang['msg.no_skin']]);
    }

    if (empty($_REQUEST['id'])) {
        msg(['type' => 'error', 'text' => $lang[$plugin.':msg.no_gallery']]);
        return;
    }
    $id = intval($_REQUEST['id']);
    $gallery = $mysql->record('select * from '.prefix.'_gallery where `id`='.db_squote($id).' limit 1');
    if (! $gallery) {
        msg(['type' => 'error', 'text' => $lang[$plugin.':msg.no_gallery']]);
        return;
    }
    $icon_list = [];
    foreach ($mysql->select('select name from '.prefix.'_images where folder='.db_squote($gallery['name'])) as $row) {
        $icon_list[$row['name']] = $row['name'];
    }

    $data = [
        'section' => 'list',
    ];

    // Fill configuration parameters

    $tpath = locatePluginTemplates(['navigation'], 'gallery', 0, '', 'admin');

    $cfg = [
        [
            'name' => 'section',
            'type' => 'hidden',
            'value' => 'edit_submit',
        ],
        [
            'type' => 'flat',
            'input' => $twig->render(
                $tpath['navigation'].'navigation.tpl', $data
            ),
        ],
        [
            'name' => 'id',
            'type' => 'hidden',
            'value' => $id,
        ],
    ];

    $cfgX = [];
    array_push($cfgX, [
        'name' => 'if_active',
        'title' => $lang[$plugin.':label_if_active'],
        'descr' => $lang[$plugin.':desc_if_active'],
        'type' => 'select',
        'values' => ['1' => $lang['yesa'], '0' => $lang['noa']],
        'value' => $gallery['if_active'],
    ]);
    array_push($cfgX, [
        'name' => 'name',
        'title' => $lang[$plugin.':label_name'],
        'descr' => $lang[$plugin.':desc_name'],
        'type' => 'input',
        'html_flags' => 'readonly',
        'value' => $gallery['name'],
    ]);
    array_push($cfgX, [
        'name' => 'title',
        'title' => $lang[$plugin.':label_title'],
        'descr' => $lang[$plugin.':desc_title'],
        'type' => 'input',
        'value' => $gallery['title'],
    ]);
    array_push($cfg, [
        'mode' => 'group',
        'title' => $lang[$plugin.':legend_general'],
        'entries' => $cfgX,
    ]);

    $cfgX = [];
    array_push($cfgX, [
        'name' => 'skin',
        'title' => $lang[$plugin.':skin'],
        'descr' => $lang[$plugin.':skin#desc'],
        'type' => 'select',
        'values' => $skList,
        'value' => $gallery['skin'],
    ]);
    array_push($cfgX, [
        'name' => 'images_count',
        'title' => $lang[$plugin.':label_images_count_gallery'],
        'descr' => $lang[$plugin.':desc_images_count_gallery'],
        'type' => 'input',
        'value' => $gallery['images_count'],
    ]);
    array_push($cfgX, [
        'name' => 'icon',
        'title' => $lang[$plugin.':label_icon'],
        'descr' => $lang[$plugin.':desc_icon'],
        'type' => 'select',
        'values' => $icon_list,
        'value' => $gallery['icon'],
    ]);
    array_push($cfg, [
        'mode' => 'group',
        'title' => $lang[$plugin.':legend_gallery_one'],
        'entries' => $cfgX,
    ]);

    $cfgX = [];
    array_push($cfgX, [
        'name' => 'description',
        'title' => $lang[$plugin.':label_description'],
        'descr' => $lang[$plugin.':desc_description'],
        'type' => 'input',
        'value' => $gallery['description'],
    ]);
    array_push($cfgX, [
        'name' => 'keywords',
        'title' => $lang[$plugin.':label_keywords'],
        'descr' => $lang[$plugin.':desc_keywords'],
        'type' => 'input',
        'value' => $gallery['keywords'],
    ]);
    array_push($cfg, [
        'mode' => 'group',
        'title' => $lang[$plugin.':legend_description'],
        'entries' => $cfgX,
    ]);

    generate_config_page($plugin, $cfg);
}

function showWidgetList($plugin, $section)
{
    global $twig, $lang, $mysql, $parse;

    $data = [
        'section' => 'widget_list',
    ];

    // Fill configuration parameters

    $tpath = locatePluginTemplates(['navigation'], 'gallery', 0, '', 'admin');

    $cfg = [
        [
            'name' => 'section',
            'type' => 'hidden',
            'value' => 'widget_add',
        ],
        [
            'type' => 'flat',
            'input' => $twig->render(
                $tpath['navigation'].'navigation.tpl', $data
            ),
        ],
        [
            'name' => 'id',
            'type' => 'hidden',
            'value' => $id,
        ],
    ];

    $lang['commit_change'] = $lang[$plugin.':button_widget_add'];

    // RUN
    do {
        if ('widget_edit_submit' === $section) {
            if (empty($_POST['name']) or empty($_POST['title']) or empty($_POST['if_active']) or empty($_POST['skin']) or empty($_POST['images_count']) or ! isset($_POST['if_rand'])) {
                msg(['type' => 'error', 'text' => $lang[$plugin.':msg.no_save_params']]);
                break;
            }

            $widgets = pluginGetVariable('gallery', 'widgets') ?: [];

            $id = isset($_POST['id']) ? intval($_POST['id']) : array_key_last($widgets) + 1;
            $name = $parse->translit($_POST['name']);
            $title = secure_html($_POST['title']);
            $if_active = intval($_POST['if_active']);
            $skin = secure_html($_POST['skin']);
            $images_count = intval($_POST['images_count']);
            $if_rand = intval($_POST['if_rand']);
            $gallery = secure_html($_POST['gallery']);

            $widgets[$id]['name'] = $name;
            $widgets[$id]['title'] = $title;
            $widgets[$id]['if_active'] = $if_active;
            $widgets[$id]['skin'] = $skin;
            $widgets[$id]['images_count'] = $images_count;
            $widgets[$id]['if_rand'] = $if_rand;
            $widgets[$id]['gallery'] = $gallery;

            pluginSetVariable('gallery', 'widgets', $widgets);

            // Save configuration parameters of plugins
            pluginsSaveConfig();

            msg(['text' => $lang[$plugin.':info_update_record']]);
        } elseif ('widget_dell' === $section) {
            if (empty($_REQUEST['id'])) {
                msg(['type' => 'error', 'text' => $lang[$plugin.':msg.no_widget']]);
                break;
            }
            $id = intval($_REQUEST['id']);
            $widgets = pluginGetVariable('gallery', 'widgets') ?: [];
            if (empty($widgets[$id])) {
                msg(['type' => 'error', 'text' => $lang[$plugin.':msg.no_widget']]);
                break;
            }
            if (isset($widgets[$id])) {
                unset($widgets[$id]);
                pluginSetVariable('gallery', 'widgets', $widgets);

                // Save configuration parameters of plugins
                pluginsSaveConfig();
            }
            msg(['type' => 'info', 'text' => $lang[$plugin.':info_delete']]);
        }

        if ('widget_list' != $section and pluginGetVariable('gallery', 'cache')) {
            clearCacheFiles($plugin);
        }
    } while (0);

    $items = [];
    if (is_array($widgets = pluginGetVariable('gallery', 'widgets'))) {
        foreach ($widgets as $id => $row) {
            // Prepare data for template
            $items[] = [
                'isActive' => $row['if_active'],
                'id' => $id,
                'name' => $row['name'],
                'title' => $row['title'],
                'gallery' => $row['gallery'],
                'skin' => $row['skin'],
                'rand' => $row['if_rand'] ? $lang[$plugin.':label_yes'] : $lang[$plugin.':label_no'],
            ];
        }
    }
    $tVars['items'] = $items;
    $tpath = locatePluginTemplates(['widget.list'], 'gallery', 0, '', 'admin');
    array_push($cfg, [
        'type' => 'flat',
        'input' => $twig->render($tpath['widget.list'].'widget.list.tpl', $tVars),
    ]);
    generate_config_page($plugin, $cfg);
}

function editWidget($plugin, $section)
{
    global $lang, $mysql, $twig;

    // Prepare configuration parameters
    if (empty($skList = skinsListForPlugin($plugin))) {
        msg(['type' => 'error', 'text' => $lang['msg.no_skin']]);
    }

    if (($galleries = cacheRetrieveFile('galleries.dat', 86400, 'gallery')) === false) {
        $rows = $mysql->select('SELECT *, (SELECT count(*) FROM '.prefix.'_images WHERE folder='.prefix.'_gallery.name) AS count FROM '.prefix.'_gallery WHERE if_active=1 ORDER BY position');
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $name = $folder = secure_html($row['name']);
            $icon = secure_html($row['icon']);
            $galleries[$name] = [
                'id' => $id,
                'name' => $name,
                'title' => secure_html($row['title']),
                'url' => generatePluginLink('gallery', 'gallery', ['id' => $id, 'name' => $name]),
                'count' => $row['count'], // count images in gallery
                'images_count' => $row['images_count'], // count images in gallery for display in page gallery
                'description' => secure_html($row['description']),
                'keywords' => secure_html($row['keywords']),
                'position' => (int) $row['position'],
                'skin' => secure_html($row['skin']),
                'icon' => images_url.'/'.$folder.'/'.$icon,
                'icon_thumb' => file_exists(images_dir.'/'.$folder.'/thumb/'.$icon)
                    ? images_url.'/'.$folder.'/thumb/'.$icon
                    : images_url.'/'.$folder.'/'.$icon,
            ];
            $galleriesSelect [$name] = secure_html($row['title']);
        }
        cacheStoreFile('galleries.dat', serialize($galleries), 'gallery');
    } else {
        $galleries = unserialize($galleries);
        foreach ($galleries as $row) {
            $galleriesSelect [secure_html($row['name'])] = secure_html($row['title']);
        }
    }

    $widgets = pluginGetVariable('gallery', 'widgets') ?: [];

    $id = array_key_last($widgets) + 1;
    $if_active = 1;
    $name = '';
    $title = '';
    $skin = '';
    $images_count = 12;
    $if_rand = 0;
    $gallery = '';
    if (isset($_REQUEST['id'])) {
        $id = intval($_REQUEST['id']);
        if (empty($widgets[$id])) {
            $id = array_key_last($widgets) + 1;
        } else {
            $if_active = $widgets[$id]['if_active'];
            $name = $widgets[$id]['name'];
            $title = $widgets[$id]['title'];
            $skin = $widgets[$id]['skin'];
            $images_count = $widgets[$id]['images_count'];
            $if_rand = $widgets[$id]['if_rand'];
            $gallery = $widgets[$id]['gallery'];
        }
    }

    $data = [
        'section' => 'widget_list',
    ];

    // Fill configuration parameters

    $tpath = locatePluginTemplates(['navigation'], 'gallery', 0, '', 'admin');

    $cfg = [
        [
            'name' => 'section',
            'type' => 'hidden',
            'value' => 'widget_edit_submit',
        ],
        [
            'type' => 'flat',
            'input' => $twig->render(
                $tpath['navigation'].'navigation.tpl', $data
            ),
        ],
        [
            'name' => 'id',
            'type' => 'hidden',
            'value' => $id,
        ],
    ];

    $cfgX = [];
    array_push($cfgX, [
        'name' => 'if_active',
        'title' => $lang[$plugin.':label_widget_if_active'],
        'descr' => $lang[$plugin.':desc_widget_if_active'],
        'type' => 'select',
        'values' => ['1' => $lang['yesa'], '0' => $lang['noa']],
        'value' => $if_active,
    ]);
    array_push($cfgX, [
        'name' => 'name',
        'title' => $lang[$plugin.':label_widget_name'],
        'descr' => $lang[$plugin.':desc_widget_name'],
        'type' => 'input',
        'value' => $name,
    ]);
    array_push($cfgX, [
        'name' => 'title',
        'title' => $lang[$plugin.':label_widget_title'],
        'descr' => $lang[$plugin.':desc_widget_title'],
        'type' => 'input',
        'value' => $title,
    ]);
    array_push($cfgX, [
        'name' => 'gallery',
        'title' => $lang[$plugin.':label_gallery'],
        'descr' => $lang[$plugin.':desc_gallery'],
        'type' => 'select',
        'values' => $galleriesSelect,
        'value' => $gallery,
    ]);
    array_push($cfg, [
        'mode' => 'group',
        'title' => $lang[$plugin.':legend_general'],
        'entries' => $cfgX,
    ]);

    $cfgX = [];
    array_push($cfgX, [
        'name' => 'skin',
        'title' => $lang[$plugin.':skin'],
        'descr' => $lang[$plugin.':skin#desc'],
        'type' => 'select',
        'values' => $skList,
        'value' => $skin,
    ]);
    array_push($cfgX, [
        'name' => 'images_count',
        'title' => $lang[$plugin.':label_images_count_widget'],
        'descr' => $lang[$plugin.':desc_images_count_widget'],
        'type' => 'input',
        'value' => $images_count,
    ]);
    array_push($cfgX, [
        'name' => 'if_rand',
        'title' => 'Сортировка',
        'descr' => 'Порядок вывода изображений',
        'type' => 'select',
        'values' => [0 => 'по умолчанию', 1 => 'случайно', 2 => 'просмотры', 3 => 'комментарии'],
        'value' => $if_rand,
    ]);
    array_push($cfg, [
        'mode' => 'group',
        'title' => $lang[$plugin.':legend_widget_one'],
        'entries' => $cfgX,
    ]);

    generate_config_page($plugin, $cfg);
}

function GetKeyFromName($name, $array)
{
    $count = count($array);
    for ($i = 0; $i < $count; $i++) {
        if ($array[$i]['name'] == $name) {
            return $i;
        }
    }
    return false;
}

function skinsListForPlugin($plugin)
{
    $skList = [];

    $templateDirectorySkins = tpl_site."plugins/{$plugin}/skins";
    $pluginDirectorySkins = extras_dir."/{$plugin}/tpl/skins";

    $skinsDirectory = opendir(
        is_dir($templateDirectorySkins) ? $templateDirectorySkins : $pluginDirectorySkins
    );

    if (false !== $skinsDirectory) {

        while ($skFile = readdir($skinsDirectory)) {
            if (! preg_match('/^\./', $skFile)) {
                $skList[$skFile] = $skFile;
            }
        }

        closedir($skinsDirectory);
    }

    return $skList;
}

// clear Cache Files
function clearCacheFiles(string $plugin = '')
{
    $error = false;
    $listSkip = '';
    $cacheDir = $plugin ? get_plugcache_dir($plugin) : root . 'cache/';

    $dirIterator = new RecursiveDirectoryIterator($cacheDir, RecursiveDirectoryIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::LEAVES_ONLY);

    foreach ($iterator as $object) {
        if ($object->isFile() or $object->isDir()) {
            if (! @unlink($object->getPathname())) {
                $listSkip .= '<br>' . $object->getBasename();
                $error = true;
            }
        }
    }

    if ($error) {
        msg([
            'type' => 'error',
            'text' => 'Не весь кэш удалось очистить!<hr>Список пропущенных файлов:' . $listSkip,
        ]);
    } else {
        msg([
            'text' => $plugin ? 'Кэш плагина очищен!' : 'Кэш системы очищен!',
        ]);
    }

    // Clear cache OPCache
    if(function_exists('opcache_get_status')) {
        opcache_reset();
    }

    // Create a protective .htaccess
    create_access_htaccess();
}
