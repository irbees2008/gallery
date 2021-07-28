<?php

/*
 * Configuration file for plugin
 */

// Protect against hack attempts
if (!defined('BBCMS')) die ('HAL');

// Load lang files
Lang::loadPlugin($plugin, 'admin', '', ':');

// Set default values if values are not set [for new variables]
foreach ([
    'if_description' => 1,
    'if_keywords' => 1,
    'galleries_count' => 6,
    'skin' => 'basic',
    'cache' => 1,
    'cache_expire' => 60,
    ] as $k => $v ) {
    if (pluginGetVariable($plugin, $k) == null) {
        pluginSetVariable($plugin, $k, $v);
    }
}

// Micro sRouter =)
switch ($action)
{
    case 'list':
    case 'update':
    case 'dell':
    case 'edit_submit':
    case 'move_up':
    case 'move_down':
        showList($plugin, $action);
        break;

    case 'edit':
        edit($plugin, $action);
        break;

    case 'widget_list':
    case 'widget_edit_submit':
    case 'widget_dell':
        showWidgetList($plugin, $action);
        break;

    case 'widget_add':
        editWidget($plugin, $action);
        break;

    default:
        main($plugin, $action);
        break;
}

function main($plugin, $action)
{

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();

    // Prepare configuration parameters
    if (empty($skList = $cPlugin->getThemeSkin($plugin))) {
        msg(array( 'type' => 'danger', 'message' => __('msg.no_skin')));
    }

    // Check to dependence plugin
    $dependence = [];
    if (!$cPlugin->isActive('comments')) {
        $dependence['comments'] = 'comments';
    }

    // Fill configuration parameters
    $cfg = array(
        'description' => __($plugin.':description'),
        'dependence' => $dependence,
        'navigation' => array(
            array('class' => 'active','href' => 'admin.php?mod=extra-config&plugin=gallery','title' => __('config')),
            array('href' => 'admin.php?mod=extra-config&plugin=gallery&action=list','title' => __('gallery:button_list')),
            array('href' => 'admin.php?mod=extra-config&plugin=gallery&action=widget_list','title' => __('widgetList')),
        ),
        'submit' => array(array('type' => 'default'),array('type' => 'reinstall'),array('type' => 'clearCacheFiles'))
    );

    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'seo_title',
            'title' => __('seo_title'),
            'descr' => __('seo_title#desc'),
            'type' => 'input',
            'value' => pluginGetVariable($plugin, 'seo_title'),
        ));
        array_push($cfgX, array(
            'name' => 'seo_description',
            'title' => __('seo_description'),
            'descr' => __('seo_description#desc'),
            'type' => 'input',
            'value' => pluginGetVariable($plugin, 'seo_description'),
        ));
        array_push($cfgX, array(
            'name' => 'seo_keywords',
            'title' => __('seo_keywords'),
            'descr' => __('seo_keywords#desc'),
            'type' => 'input',
            'value' => pluginGetVariable($plugin, 'seo_keywords'),
        ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('group.seo'),
        'entries' => $cfgX,
        ));

    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'if_description',
            'title' => __('gallery:label_if_description'),
            'descr' => __('gallery:desc_if_description'),
            'type' => 'select',
            'values' => array('1' => __('yesa'), '0' => __('noa')),
            'value' => pluginGetVariable($plugin, 'if_description'),
            ));
        array_push($cfgX, array(
            'name' => 'if_keywords',
            'title' => __('gallery:label_if_keywords'),
            'descr' => __('gallery:desc_if_keywords'),
            'type' => 'select',
            'values' => array('1' => __('yesa'), '0' => __('noa')),
            'value' => pluginGetVariable($plugin, 'if_keywords'),
            ));
        array_push($cfgX, array(
            'name' => 'galleries_count',
            'title' => __('gallery:label_images_count'),
            'descr' => __('gallery:desc_images_count'),
            'type' => 'input',
            'value' => pluginGetVariable($plugin, 'galleries_count'),
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('group.general'),
        'entries' => $cfgX,
        ));

    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'skin',
            'title' => __('skin'),
            'descr' => __('skin#desc'),
            'type' => 'select',
            'values' => $skList,
            'value' => pluginGetVariable($plugin, 'skin'),
        ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('group.source'),
        'entries' => $cfgX,
        ));

    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'cache',
            'title' => __('cache'),
            'descr' => __('cache#desc'),
            'type' => 'select',
            'values' => array('1' => __('yesa'), '0' => __('noa')),
            'value' => pluginGetVariable($plugin, 'cache'),
            ));
        array_push($cfgX, array(
            'name' => 'cache_expire',
            'title' => __('cache_expire'),
            'descr' => __('cache_expire#desc'),
            'type' => 'input',
            'value' => pluginGetVariable($plugin, 'cache_expire'),
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('group.cache'),
        'entries' => $cfgX,
        ));

    // RUN
    if ('commit' == $action) {
        // If submit requested, do config save
        commit_plugin_config_changes($plugin, $cfg);
    }

    generate_config_page($plugin, $cfg);
}

function showList($plugin, $action)
{
    global $twig, $mysql, $parse;

    // Fill configuration parameters
    $cfg = array(
        'navigation' => array(
            array('href' => 'admin.php?mod=extra-config&plugin=gallery','title' => __('group.config')),
            array('class' => 'active','href' => 'admin.php?mod=extra-config&plugin=gallery&action=list','title' => __('gallery:button_list')),
            array('href' => 'admin.php?mod=extra-config&plugin=gallery&action=widget_list','title' => __('gallery:button_widget_list')),
        ),
        'submit' => array(
            array('class' => 'btn btn-primary','href' => 'admin.php?mod=extra-config&plugin=gallery&action=update','title' => __('gallery:button_update')),
            )
    );

    // RUN
    do {
        if ('update' == $action) {
            $gallery = $mysql->select('select name from '.prefix.'_gallery');
            $next_order = count($gallery) + 1;
            if ($dir = opendir(images_dir)) {
                while($file = readdir($dir)) {
                    if (!is_dir(images_dir."/".$file) or $file == "." or $file == ".." or GetKeyFromName($file, $gallery) !== false)
                        continue;
                    $mysql->query('insert '.prefix.'_gallery '.
                        '(name, title, position) values '.
                        '('.db_squote($file).', '.db_squote($file).', '.db_squote($next_order).')');
                        $next_order ++;
                }
                closedir($dir);
                msg(array('message' => __('gallery:info_update_record')));
            }

        } elseif ('edit_submit' == $action) {

            if (!isset($_POST['id']) or !isset($_POST['title']) or !isset($_POST['if_active']) or !isset($_POST['skin']) or !isset($_POST['icon']) or !isset($_POST['description']) or !isset($_POST['keywords']) or !isset($_POST['images_count'])) {
                msg(array('type' => 'danger', 'message' => 'Не все параметры заданы'));
                break;
            }
            $id = intval($_POST['id']);
            
            $gallery = $mysql->record('select * from '.prefix.'_gallery where `id`='.db_squote($id).' limit 1');
            
            if (!$gallery) {
                msg(array('type' => 'danger', 'message' => ''));
                break;
            }

            $title = secure_html($_POST['title']);
            $skin = secure_html($_POST['skin']);
            $images_count = !empty($_POST['images_count']) ? abs(intval($_POST['images_count'])) : 12;
            $if_active = intval($_POST['if_active']);
            $icon = secure_html($_POST['icon']);
            $description = secure_html(str_replace(array("\r\n", "\n", '  '), array(' '), $_POST['description']));
            $keywords = secure_html($_POST['keywords']);

            $t_update = '';
            if ($title != $gallery['title']) 
                $t_update .= (($t_update?', ':'').'`title`='.db_squote($title));
            if ($skin != $gallery['skin']) 
                $t_update .= (($t_update?', ':'').'`skin`='.db_squote($skin));
            if ($images_count != $gallery['images_count']) 
                $t_update .= (($t_update?', ':'').'`images_count`='.db_squote($images_count));
            if ($if_active != $gallery['if_active']) 
                $t_update .= (($t_update?', ':'').'`if_active`='.db_squote($if_active));
            if ($icon != $gallery['icon']) 
                $t_update .= (($t_update?', ':'').'`icon`='.db_squote($icon));
            if ($description != $gallery['description']) 
                $t_update .= (($t_update?', ':'').'`description`='.db_squote($description));
            if ($keywords != $gallery['keywords']) 
                $t_update .= (($t_update?', ':'').'`keywords`='.db_squote($keywords));
            
            if ($t_update){
                $mysql->query('update '.prefix.'_gallery set '.$t_update.' where id = '.db_squote($id).' limit 1');
                msg(array('message' => __('gallery:info_update_record')));
            } else {
                msg(array('type' => 'info', 'message' => 'Изменений нет'));
            }

        } elseif ('move_up' == $action or 'move_down' == $action) {

            if (empty($_REQUEST['id'])) {
                msg(array('type' => 'danger', 'message' => __('gallery:msg.no_gallery')));
                break;
            }
            $id = intval($_REQUEST['id']);

            $gallery = $mysql->record('select id, position from '.prefix.'_gallery where `id`='.db_squote($id).' limit 1');
            if (!$gallery) {
                msg(array('type' => 'danger', 'message' => __('gallery:msg.no_gallery')));
                break;
            }
            $count = 0;
            if (is_array($pcnt = $mysql->record('select count(*) as cnt from '.prefix.'_gallery')))
                $count = $pcnt['cnt'];

            if ($action == 'move_up') {
                if ($gallery['position'] == 1) {
                    msg(array('type' => 'danger', 'message' => __('gallery:info_update_record')));
                    break;
                }

                $gallery2 = $mysql->record('select id, position from '.prefix.'_gallery where position='.db_squote($gallery['position'] - 1).' limit 1');

                $mysql->query('update '.prefix.'_gallery set position='.db_squote($gallery['position']).'where `id`='.db_squote($gallery2['id']).' limit 1');
                $mysql->query('update '.prefix.'_gallery set position='.db_squote($gallery2['position']).'where `id`='.db_squote($gallery['id']).' limit 1');
            } elseif ($action == 'move_down') {
                if ($gallery['position'] == $count) {
                    msg(array('type' => 'danger', 'message' => __('gallery:info_update_record')));
                    break;
                }

                $gallery2 = $mysql->record('select id, position from '.prefix.'_gallery where position='.db_squote($gallery['position'] + 1).' limit 1');

                $mysql->query('update '.prefix.'_gallery set position='.db_squote($gallery['position']).'where `id`='.db_squote($gallery2['id']).' limit 1');
                $mysql->query('update '.prefix.'_gallery set position='.db_squote($gallery2['position']).'where `id`='.db_squote($gallery['id']).' limit 1');
            }
            msg(array('type' => 'info', 'message' => __('gallery:info_update_record')));

        } elseif ('dell' == $action) {
            if (empty($_REQUEST['id'])) {
                msg(array('type' => 'danger', 'message' => __('gallery:msg.no_gallery')));
                break;
            }
            $id = intval($_REQUEST['id']);
            $gallery = $mysql->record('select `title` from '.prefix.'_gallery where `id`='.db_squote($id).' limit 1');
            if (!$gallery) {
                msg(array('type' => 'danger', 'message' => __('gallery:msg.no_gallery')));
                break;
            }
            $mysql->query('delete from '.prefix.'_gallery where `id`='.db_squote($id));
            $next_order = 1;
            foreach($mysql->select('select id from '.prefix.'_gallery order by position') as $row) {
                $dir = opendir(images_dir);
                $mysql->query('update '.prefix.'_gallery set position='.db_squote($next_order).'where `id`='.db_squote($row['id']).' limit 1');
                $next_order ++;
            }
            msg(array('type' => 'info', 'message' => __('gallery:info_delete')));
        }

        if ('list' != $action and pluginGetVariable('gallery', 'cache')) {
            clearCacheFiles($plugin);}

    } while (0);

    $tVars = [];
    $rows = $mysql->select('select * from '.prefix.'_gallery order by position');
    foreach($rows as $row) {
        // Prepare data for template
        $tVars['items'][] = [
            'isActive' => $row['if_active'],
            'id' => $row['id'],
            'name' => $row['name'],
            'title' => $row['title'],
            'url' => generatePluginLink('gallery', 'gallery', array('id' => $row['id'], 'name' => $row['name'])),
            'skin' => $row['skin'],
        ];
    }

    $tpath = plugin_locateTemplates('gallery', array('gallery.list'));
    array_push($cfg, array(
            'type' => 'flat',
            'input' => $twig->render($tpath['gallery.list'] . 'gallery.list.tpl', $tVars)
            ));
    generate_config_page($plugin, $cfg);
}

function edit($plugin, $action)
{
    global $mysql;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();

    // Prepare configuration parameters
    if (empty($skList = $cPlugin->getThemeSkin($plugin))) {
        msg(array( 'type' => 'danger', 'message' => __('msg.no_skin')));
    }

    if (empty($_REQUEST['id'])) {
        msg(array('type' => 'danger', 'message' => __('gallery:msg.no_gallery')));
        return;
    }
    $id = intval($_REQUEST['id']);
    $gallery = $mysql->record('select * from '.prefix.'_gallery where `id`='.db_squote($id).' limit 1');
    if (!$gallery) {
        msg(array('type' => 'danger', 'message' => __('gallery:msg.no_gallery')));
        return;
    }
    $icon_list = array();
    foreach($mysql->select('select name from '.prefix.'_images where folder='.db_squote($gallery['name'])) as $row)
        $icon_list[$row['name']] = $row['name'];

    // Fill configuration parameters
    $cfg = array(
        'navigation' => array(
            array('href' => 'admin.php?mod=extra-config&plugin=gallery','title' => __('group.config')),
            array('class' => 'active','href' => 'admin.php?mod=extra-config&plugin=gallery&action=list','title' => __('gallery:button_list')),
            array('href' => 'admin.php?mod=extra-config&plugin=gallery&action=widget_list','title' => __('gallery:button_widget_list')),
        ),
        'action' => 'admin.php?mod=extra-config&plugin=gallery&action=edit_submit', // !!!
        'submit' => array(array('type' => 'default')),
        array(
            'name' => 'id',
            'type' => 'hidden',
            'value' => $id,
            )
    );

    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'if_active',
            'title' => __('gallery:label_if_active'),
            'descr' => __('gallery:desc_if_active'),
            'type' => 'select',
            'values' => array('1' => __('yesa'), '0' => __('noa')),
            'value' => $gallery['if_active'],
            ));
        array_push($cfgX, array(
            'name' => 'name',
            'title' => __('gallery:label_name'),
            'descr' => __('gallery:desc_name'),
            'type' => 'input',
            'html_flags' => 'readonly',
            'value' => $gallery['name'],
            ));
        array_push($cfgX, array(
            'name' => 'title',
            'title' => __('gallery:label_title'),
            'descr' => __('gallery:desc_title'),
            'type' => 'input',
            'value' => $gallery['title'],
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('gallery:legend_general'),
        'entries' => $cfgX,
        ));

    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'skin',
            'title' => __('skin'),
            'descr' => __('skin#desc'),
            'type' => 'select',
            'values' => $skList,
            'value' => $gallery['skin'],
            ));
        array_push($cfgX, array(
            'name' => 'images_count',
            'title' => __('gallery:label_images_count_gallery'),
            'descr' => __('gallery:desc_images_count_gallery'),
            'type' => 'input',
            'value' => $gallery['images_count'],
            ));
        array_push($cfgX, array(
            'name' => 'icon',
            'title' => __('gallery:label_icon'),
            'descr' => __('gallery:desc_icon'),
            'type' => 'select',
            'values' => $icon_list,
            'value' => $gallery['icon'],
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('gallery:legend_gallery_one'),
        'entries' => $cfgX,
        ));

    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'description',
            'title' => __('gallery:label_description'),
            'descr' => __('gallery:desc_description'),
            'type' => 'input',
            'value' => $gallery['description'],
            ));
        array_push($cfgX, array(
            'name' => 'keywords',
            'title' => __('gallery:label_keywords'),
            'descr' => __('gallery:desc_keywords'),
            'type' => 'input',
            'value' => $gallery['keywords'],
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('gallery:legend_description'),
        'entries' => $cfgX,
        ));

    generate_config_page($plugin, $cfg);
}

function showWidgetList($plugin, $action)
{
    global $twig, $mysql, $parse;

    // Fill configuration parameters
    $cfg = array(
        'navigation' => array(
            array('href' => 'admin.php?mod=extra-config&plugin=gallery','title' => __('group.config')),
            array('href' => 'admin.php?mod=extra-config&plugin=gallery&action=list','title' => __('gallery:button_list')),
            array('class' => 'active','href' => 'admin.php?mod=extra-config&plugin=gallery&action=widget_list','title' => __('gallery:button_widget_list')),
        ),
        'submit' => array(
            array('class' => 'btn btn-primary','href' => 'admin.php?mod=extra-config&plugin=gallery&action=widget_add','title' => __('gallery:button_widget_add')),
            )
    );

    // RUN
    do {
        if ('widget_edit_submit' == $action) {
            if (empty($_POST['name']) or empty($_POST['title']) or empty($_POST['if_active'])  or empty($_POST['skin']) or empty($_POST['images_count']) or !isset($_POST['if_rand'])) {
                msg(array('type' => 'danger', 'message' => 'Не все параметры заданы' . '<br><a href="#" onClick="history.back(1);" class="alert-link">Вернуться назад</a>'));
                break;
            }

            $widgets = pluginGetVariable('gallery', 'widgets');

            $id = isset($_POST['id']) ? intval($_POST['id']) : count($widgets) + 1;
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
            // Load CORE Plugin
            $cPlugin = CPlugin::instance();
            // Save configuration parameters of plugins
            $cPlugin->saveConfig();

            msg(array('message' => __('gallery:info_update_record')));
        } elseif ('widget_dell' == $action) {
            if (empty($_REQUEST['id'])) {
                msg(array('type' => 'danger', 'message' => __('gallery:msg.no_widget')));
                break;
            }
            $id = intval($_REQUEST['id']);
            $widgets = pluginGetVariable('gallery', 'widgets');
            if (empty($widgets[$id])){
                msg(array('type' => 'danger', 'message' => __('gallery:msg.no_widget')));
                break;
            }
            if (isset($widgets[$id])){
                unset($widgets[$id]);
                pluginSetVariable('gallery', 'widgets', $widgets);
                // Load CORE Plugin
                $cPlugin = CPlugin::instance();
                // Save configuration parameters of plugins
                $cPlugin->saveConfig();
            }
            msg(array('type' => 'info', 'message' => __('gallery:info_delete')));
        }

        if ('widget_list' != $action and pluginGetVariable('gallery', 'cache')) {
            clearCacheFiles($plugin);}

    } while (0);

    $items = array();
    if (is_array($widgets = pluginGetVariable('gallery', 'widgets'))){
        foreach($widgets as $id=>$row) {
            // Prepare data for template
            $items[] = [
                'isActive' => $row['if_active'],
                'id' => $id,
                'name' => $row['name'],
                'title' => $row['title'],
                'gallery' => $row['gallery'],
                'skin' => $row['skin'],
                'rand' => $row['if_rand'] ? __('gallery:label_yes') : __('gallery:label_no'),
            ];
        }
    }
    $tVars['items'] = $items;
    $tpath = plugin_locateTemplates('gallery', array('widget.list'));
    array_push($cfg, array(
            'type' => 'flat',
            'input' => $twig->render($tpath['widget.list'] . 'widget.list.tpl', $tVars)
            ));
    generate_config_page($plugin, $cfg);
}

function editWidget($plugin, $action)
{
    global $tpl, $mysql;

    // Load CORE Plugin
    $cPlugin = CPlugin::instance();

    // Prepare configuration parameters
    if (empty($skList = $cPlugin->getThemeSkin($plugin))) {
        msg(array( 'type' => 'danger', 'message' => __('msg.no_skin')));
    }
    
    if (($galleries = cacheRetrieveFile('galleries.dat', 86400, 'gallery')) === false) {
        $rows = $mysql->select('SELECT *, (SELECT count(*) FROM '.prefix.'_images WHERE folder='.prefix.'_gallery.name) AS count FROM '.prefix.'_gallery WHERE if_active=1 ORDER BY position');
        foreach($rows as $row) {
            $id = (int)$row['id'];
            $name = $folder = secure_html($row['name']);
            $icon = secure_html($row['icon']);
            $galleries[$name] = [
                'id' => $id,
                'name' => $name,
                'title' => secure_html($row['title']),
                'url' => generatePluginLink('gallery', 'gallery', array('id' => $id, 'name' => $name)),
                'count' => $row['count'], // count images in gallery
                'images_count' => $row['images_count'], // count images in gallery for display in page gallery
                'description' => secure_html($row['description']),
                'keywords' => secure_html($row['keywords']),
                'position' => (int)$row['position'],
                'skin' => secure_html($row['skin']),
                'icon' => images_url . '/' . $folder . '/' . $icon,
                'icon_thumb' => file_exists(images_dir . '/' . $folder . '/thumb/' . $icon)
                    ? images_url . '/' . $folder . '/thumb/' . $icon
                    : images_url . '/' . $folder . '/' . $icon,
            ];
            $galleriesSelect [$name] = secure_html($row['title']);
        }
        cacheStoreFile('galleries.dat', serialize($galleries), 'gallery');
    } else {
        $galleries = unserialize($galleries);
        foreach($galleries as $row) {
            $galleriesSelect [secure_html($row['name'])] = secure_html($row['title']);
        }
    }

    $widgets = pluginGetVariable('gallery', 'widgets');

    $id = count($widgets) + 1;
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
            $id = count($widgets) + 1;
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

    // Fill configuration parameters
    $cfg = array(
        'navigation' => array(
            array('href' => 'admin.php?mod=extra-config&plugin=gallery','title' => __('group.config')),
            array('href' => 'admin.php?mod=extra-config&plugin=gallery&action=list','title' => __('gallery:button_list')),
            array('class' => 'active','href' => 'admin.php?mod=extra-config&plugin=gallery&action=widget_list','title' => __('gallery:button_widget_list')),
        ),
        'action' => 'admin.php?mod=extra-config&plugin=gallery&action=widget_edit_submit', // !!!
        'submit' => array(array('type' => 'default')),
        array(
            'name' => 'id',
            'type' => 'hidden',
            'value' => $id,
            )
    );

    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'if_active',
            'title' => __('gallery:label_widget_if_active'),
            'descr' => __('gallery:desc_widget_if_active'),
            'type' => 'select',
            'values' => array('1' => __('yesa'), '0' => __('noa')),
            'value' => $if_active,
            ));
        array_push($cfgX, array(
            'name' => 'name',
            'title' => __('gallery:label_widget_name'),
            'descr' => __('gallery:desc_widget_name') . '<code>{{ plugin_gallery_ID }}</code>',
            'type' => 'input',
            'value' => $name,
            ));
        array_push($cfgX, array(
            'name' => 'title',
            'title' => __('gallery:label_widget_title'),
            'descr' => __('gallery:desc_widget_title'),
            'type' => 'input',
            'value' => $title,
            ));
        array_push($cfgX, array(
            'name' => 'gallery',
            'title' => __('gallery:label_gallery'),
            'descr' => __('gallery:desc_gallery'),
            'type' => 'select',
            'values' => $galleriesSelect,
            'value' => $gallery,
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('gallery:legend_general'),
        'entries' => $cfgX,
        ));

    $cfgX = array();
        array_push($cfgX, array(
            'name' => 'skin',
            'title' => __('skin'),
            'descr' => __('skin#desc'),
            'type' => 'select',
            'values' => $skList,
            'value' => $skin,
            ));
        array_push($cfgX, array(
            'name' => 'images_count',
            'title' => __('gallery:label_images_count_widget'),
            'descr' => __('gallery:desc_images_count_widget'),
            'type' => 'input',
            'value' => $images_count,
            ));
        array_push($cfgX, array(
            'name' => 'if_rand',
            'title' => 'Сортировка',
            'descr' => 'Порядок вывода изображений',
            'type' => 'select',
            'values' => array(0 => 'по умолчанию', 1 => 'случайно', 2 => 'просмотры', 3 => 'комментарии'),
            'value' => $if_rand,
            ));
    array_push($cfg, array(
        'mode' => 'group',
        'title' => __('gallery:legend_widget_one'),
        'entries' => $cfgX,
        ));

    generate_config_page($plugin, $cfg);
}

function GetKeyFromName($name, $array)
{
    $count = count($array);
    for ($i = 0; $i < $count; $i ++)
        if ($array[$i]['name'] == $name)
            return $i;
    return false;
}
