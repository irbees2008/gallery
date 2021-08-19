<?php


/*
 * Class PluginGallery
 * Description: PluginGallery
 */

class PluginGallery
{
    protected $lang = [];
    protected $params = [];

    protected $galleries = [];

    protected $pluginTitle;

    public function __construct()
    {
        global $lang;

        $this->lang = $lang;

        $this->loadParams();
        $this->loadGalleries();

        $this->pluginTitle = $this->params['seo_title'] ?: $this->lang['gallery:title'];
    }

    // Load all params of plugin gallery
    protected function loadParams()
    {
        global $PLUGINS;

        $this->params = $PLUGINS['config']['gallery'];
    }

    // Load info about galleries
    protected function loadGalleries()
    {
        global $mysql;

        if (($this->galleries = cacheRetrieveFile('galleries.dat', 86400, 'gallery')) === false) {
            $rows = $mysql->select('SELECT *, (SELECT count(*) FROM '.prefix.'_images WHERE folder='.prefix.'_gallery.name) AS count FROM '.prefix.'_gallery WHERE if_active=1 ORDER BY position');
            foreach ($rows as $row) {
                $id = (int) $row['id'];
                $name = $folder = secure_html($row['name']);
                $icon = secure_html($row['icon']);
                $this->galleries[$name] = [
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
            }
            cacheStoreFile('galleries.dat', serialize($this->galleries), 'gallery');
        } else {
            $this->galleries = unserialize($this->galleries);
        }
    }

    public function categoryAction()
    {
        global $twig, $mysql, $template;

        if ($this->params['cache']) {
            $cacheFileName = md5('gallery'.'category').'.txt';
            $cacheData = cacheRetrieveFile($cacheFileName, (int) $this->params['cache_expire'], 'gallery');
            if ($cacheData != false) {
                return $template['vars']['plugin_gallery_category'] = $cacheData;
            }
        }

        $tPath = locatePluginTemplates(
            ['category'],
            'gallery',
            0,
            pluginGetVariable('gallery', 'skin')
        );

        $tVars = [
            'plugin_title' => $this->pluginTitle,
            'url_tpl' => $tPath['url:category'],
            'url_main' => generatePluginLink('gallery', null),
            'galleries' => $this->galleries,
        ];
        $template['vars']['plugin_gallery_category'] = $twig->render($tPath['category'].'category.tpl', $tVars);

        if ($this->params['cache']) {
            cacheStoreFile($cacheFileName, $template['vars']['plugin_gallery_category'], 'gallery');
        }
    }

    public function widgetAction()
    {
        global $template, $twig, $mysql, $config;

        if (empty($this->params['widgets']) or ! is_array($widgets = $this->params['widgets'])) {
            return;
        }

        foreach ($widgets as $id => $widget) {
            if (! $widget['if_active']) {
                continue;
            }

            $widgetName = 'plugin_gallery_'.$widget['name'];

            if ($this->params['cache']) {
                $cacheFileName = md5($config['theme'].$config['default_lang'].$widget['skin'].$widgetName).'.txt';
                $cacheData = cacheRetrieveFile($cacheFileName, (int) $this->params['cache_expire'], 'gallery');
                if ($cacheData != false) {
                    // We got data from cache. Return it and stop
                    $template['vars'][$widgetName] = $cacheData;
                    continue;
                }
            }

            $where = " where folder <>''";
            if (! empty($widget['gallery'])) {
                $where = ' where folder='.db_squote($widget['gallery']);
            }

            $limit = 'limit '.$widget['images_count'];
            if ($widget['if_rand'] == 1) {
                $image_key = $mysql->select('select id from '.prefix.'_images '.$where);
                if (count($image_key)) {
                    shuffle($image_key);
                    if ($limit) {
                        $image_key = array_slice($image_key, 0, $widget['images_count']);
                    }
                    $where .= ' and ';
                    $t_key_list = '';
                    foreach ($image_key as $img) {
                        if ($t_key_list) {
                            $t_key_list .= ', ';
                        }
                        $t_key_list .= $img['id'];
                    }
                    $where .= ' id in ('.$t_key_list.')';
                }
            } elseif ($widget['if_rand'] == 2) {
                $where .= ' ORDER BY views desc';
            } elseif ($widget['if_rand'] == 3) {
                $where .= ' ORDER BY com desc';
            }

            $images = [];
            $rows = $mysql->select('SELECT id, name, folder, description, views, com FROM '.prefix.'_images '.$where.' '.$limit);
            foreach ($rows as $row) {
                $id = (int) $row['id'];
                $name = secure_html($row['name']);
                $folder = secure_html($row['folder']);
                $images[] = [
                    'id' => $id,
                    'title' => $name,
                    'com' => (int) $row['com'],
                    'views' => (int) $row['views'],
                    'description' => secure_html($row['description']),
                    'url' => generatePluginLink('gallery', 'image', ['gallery' => $folder, 'id' => $id, 'name' => $name]),
                    'src' => file_exists(images_dir.'/'.$folder.'/'.$name)
                        ? images_url.'/'.$folder.'/'.$name
                        : '',
                    'src_thumb' => file_exists(images_dir.'/'.$folder.'/thumb/'.$name)
                        ? images_url.'/'.$folder.'/thumb/'.$name
                        : images_url.'/'.$folder.'/'.$name,
                    'gallery_url' => $this->galleries[$row['folder']]['url'],
                    'gallery_title' => $this->galleries[$row['folder']]['title'],
                ];
            }

            $tPath = locatePluginTemplates(['widget'], 'gallery', 0, $widget['skin']);
            $tVars = [
                'url_tpl' => $tPath['url:widget'],
                'url_main' => generatePluginLink('gallery', null),
                'images' => $images,
                'widget_title' => $widget['title'],
            ];

            $template['vars'][$widgetName] = $twig->render($tPath['widget'].'widget.tpl', $tVars);

            if ($this->params['cache']) {
                cacheStoreFile($cacheFileName, $template['vars'][$widgetName], 'gallery');
            }
        }
    }

    public function indexPageAction($params)
    {
        global $userROW, $template, $twig, $lang, $mysql, $TemplateCache, $SYSTEM_FLAGS;

        $page = isset($params['page']) ? abs(intval($params['page'])) : 1;

        $SYSTEM_FLAGS['info']['title']['group'] = $this->pluginTitle;
        $SYSTEM_FLAGS['info']['title']['item'] = $this->lang['gallery:page'].' '.$page;
        if ($this->params['if_description'] and ! empty($this->params['seo_description'])) {
            $SYSTEM_FLAGS['meta']['description'] = $this->params['seo_description'];
        }
        if ($this->params['if_keywords'] and ! empty($this->params['seo_keywords'])) {
            $SYSTEM_FLAGS['meta']['keywords'] = $this->params['seo_keywords'];
        }
        $SYSTEM_FLAGS['info']['breadcrumbs'] = [['link' => generatePluginLink('gallery', null), 'text' => $this->pluginTitle]];

        if ($this->params['cache']) {
            $cacheFileName = md5('gallery'.'mainblock'.$page).'.txt';
            $cacheData = cacheRetrieveFile($cacheFileName, (int) $this->params['cache_expire'], 'gallery');
            if ($cacheData != false) {
                return $template['vars']['mainblock'] .= $cacheData;
            }
        }

        // Work with array galleries, below we [SLICE] array
        $galleries = $this->galleries;

        $pagesss = '';
        if ($this->params['galleries_count']) {
            $pagesCount = ceil(count($galleries) / $this->params['galleries_count']);
            if ($pagesCount > 1) {
                $galleries = array_slice($galleries, ($page - 1) * $this->params['galleries_count'], $this->params['galleries_count']);
                $paginationParams = ['pluginName' => 'gallery', 'pluginHandler' => '', 'params' => [], 'xparams' => [], 'paginator' => ['page', 0, false]];
                templateLoadVariables(true);
                $navigations = $TemplateCache['site']['#variables']['navigation'];
                $pagesss .= ($page > 1) ? str_replace('%page%', $this->lang['prev_page'], str_replace('%link%', generatePageLink($paginationParams, $page - 1), $navigations['prevlink'])) : '';
                $pagesss .= generatePagination($page, 1, $pagesCount, 10, $paginationParams, $navigations);
                $pagesss .= ($page < $pagesCount) ? str_replace('%page%', $this->lang['next_page'], str_replace('%link%', generatePageLink($paginationParams, $page + 1), $navigations['nextlink'])) : '';
            }
        }

        $tPath = locatePluginTemplates(
            ['page_index'],
            'gallery',
            0,
            pluginGetVariable('gallery', 'skin')
        );
        $tVars = [
            'plugin_title' => $this->pluginTitle,
            'url_tpl' => $tPath['url:page_index'],
            'url_main' => generatePluginLink('gallery', null),
            'galleries' => $galleries,
            'pagesss' => $pagesss,
        ];
        $template['vars']['mainblock'] .= $output = $twig->render($tPath['page_index'].'page_index.tpl', $tVars);

        if ($this->params['cache']) {
            cacheStoreFile($cacheFileName, $output, 'gallery');
        }
    }

    public function galleryPageAction($params)
    {
        global $userROW, $template, $twig, $lang, $mysql, $TemplateCache, $SYSTEM_FLAGS;

        $page = ! empty($params['page']) ? intval($params['page']) : 1;
        if ($page < 1) {
            $page = 1;
        }
        $gallery['name'] = ! empty($params['name']) ? secure_html($params['name']) : (! empty($_REQUEST['name']) ? secure_html($_REQUEST['name']) : false);

        if (! $gallery['name']) {
            msg(['type' => 'danger', 'message' => 'Не все параметры заданы']);
            return false;
        }

        if (! is_array($gallery = $this->galleries[$gallery['name']])) {
            msg(['type' => 'danger', 'message' => 'Не все параметры заданы']);
            return false;
        }

        $SYSTEM_FLAGS['info']['title']['group'] = $this->pluginTitle.' '.$gallery['title'];
        $SYSTEM_FLAGS['info']['title']['item'] = $this->lang['gallery:page'].' '.$page;
        if ($this->params['if_description']) {
            $SYSTEM_FLAGS['meta']['description'] = $gallery['description'];
        }
        if ($this->params['if_keywords']) {
            $SYSTEM_FLAGS['meta']['keywords'] = $gallery['keywords'];
        }
        $SYSTEM_FLAGS['info']['breadcrumbs'] = [
            ['link' => generatePluginLink('gallery', null), 'text' => $this->pluginTitle],
            ['link' => generatePluginLink('gallery', 'gallery', ['id' => $gallery['id'], 'name' => $gallery['name']]), 'text' => $gallery['title']],
        ];

        if ($this->params['cache']) {
            $cacheFileName = md5('gallery'.$gallery['id'].$gallery['name'].$page).'.txt';
            $cacheData = cacheRetrieveFile($cacheFileName, (int) $this->params['cache_expire'], 'gallery');
            if ($cacheData != false) {
                return $template['vars']['mainblock'] .= $cacheData;
            }
        }

        $images = [];
        $limit = 'LIMIT '.($page - 1) * $gallery['images_count'].', '.$gallery['images_count'];
        $rows = $mysql->select('SELECT id, name, description, folder, views, com,width,height FROM '.prefix.'_images WHERE folder='.db_squote($gallery['name']).' ORDER BY date asc, id asc '.$limit);
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $name = secure_html($row['name']);
            $folder = secure_html($row['folder']);

            $images[] = [
                'id' => $id,
                'name' => $name,
                'com' => (int) $row['com'],
                'views' => (int) $row['views'],
                'width' => (int) $row['width'],
                'height' => (int) $row['height'],
                'size' => is_readable($fname = images_dir.'/'.$folder.'/'.$name) ? formatSize(filesize($fname)) : '-',
                'description' => secure_html($row['description']),
                'url' => generatePluginLink('gallery', 'image', ['gallery' => $folder, 'id' => $id, 'name' => $name]),
                'src' => images_url.'/'.$folder.'/'.$name,
                'src_thumb' => file_exists(images_url.'/'.$folder.'/thumb/'.$name)
                    ? images_url.'/'.$folder.'/thumb/'.$folder
                    : images_url.'/'.$folder.'/'.$name,
            ];
        }

        $pagesss = '';
        if ($gallery['images_count']) {
            $count = 0;
            if (is_array($pcnt = $mysql->record('select count(*) as cnt from '.prefix.'_images where folder='.db_squote($gallery['name'])))) {
                $count = $pcnt['cnt'];
            }
            $pagesCount = ceil($count / $gallery['images_count']);
            if ($pagesCount > 1) {
                $paginationParams = ['pluginName' => 'gallery', 'pluginHandler' => 'gallery', 'params' => ['id' => $gallery['id'], 'name' => $gallery['name']], 'xparams' => [], 'paginator' => ['page', 0, false]];
                templateLoadVariables(true);
                $navigations = $TemplateCache['site']['#variables']['navigation'];
                $pagesss .= ($page > 1) ? str_replace('%page%', $this->lang['prev_page'], str_replace('%link%', generatePageLink($paginationParams, $page - 1), $navigations['prevlink'])) : '';
                $pagesss .= generatePagination($page, 1, $pagesCount, 10, $paginationParams, $navigations);
                $pagesss .= ($page < $pagesCount) ? str_replace('%page%', $this->lang['next_page'], str_replace('%link%', generatePageLink($paginationParams, $page + 1), $navigations['nextlink'])) : '';
            }
        }

        $tPath = locatePluginTemplates(['page_gallery'], 'gallery', 0, $gallery['skin']);
        $tVars = [
            'plugin_title' => $this->pluginTitle,
            'url_tpl' => $tPath['url:page_gallery'],
            'url_main' => generatePluginLink('gallery', null),
            'images' => $images,
            'gallery' => [
                'url' => generatePluginLink('gallery', 'gallery', ['id' => $gallery['id'], 'name' => $gallery['name']]),
                'title' => $gallery['title'],
                'description' => $gallery['description'],
                'keywords' => $gallery['keywords'],
            ],
            'pagesss' => $pagesss,
        ];
        $template['vars']['mainblock'] .= $output = $twig->render($tPath['page_gallery'].'page_gallery.tpl', $tVars);

        if ($this->params['cache']) {
            cacheStoreFile($cacheFileName, $output, 'gallery');
        }
    }

    public function imagePageAction($params)
    {
        global $userROW, $template, $tpl, $twig, $lang, $mysql, $TemplateCache, $SYSTEM_FLAGS;
        global $galleries;

        $imageName = ! empty($params['name']) ? secure_html($params['name']) : false;
        $gallery['name'] = ! empty($params['gallery']) ? secure_html($params['gallery']) : false;

        if (! $imageName or ! $gallery['name']) {
            error404();
            return false;
        }

        if (! is_array($gallery = $this->galleries[$gallery['name']])) {
            error404();
            return false;
        }

        $gallery['id'] = (int) $gallery['id'];
        $gallery['name'] = secure_html($gallery['name']); // Reload name of gallery
        $gallery['title'] = secure_html($gallery['title']);
        $gallery['description'] = secure_html($gallery['description']);
        $gallery['keywords'] = secure_html($gallery['keywords']);

        $SYSTEM_FLAGS['info']['title']['group'] = $this->pluginTitle.' '.$gallery['title'];
        $SYSTEM_FLAGS['info']['title']['item'] = $imageName;
        if ($this->params['if_keywords']) {
            $SYSTEM_FLAGS['meta']['keywords'] = $gallery['keywords'];
        }
        if ($this->params['if_description']) {
            $SYSTEM_FLAGS['meta']['description'] = $gallery['description'];
        }
        $SYSTEM_FLAGS['info']['breadcrumbs'] = [
            ['link' => generatePluginLink('gallery', null), 'text' => $this->pluginTitle],
            ['link' => generatePluginLink('gallery', 'gallery', ['id' => $gallery['id'], 'name' => $gallery['name']]), 'text' => $gallery['title']],
            ['link' => generatePluginLink('gallery', 'image', ['gallery' => $gallery['name'], 'name' => $imageName]), 'text' => $imageName],
        ];

        // Need to update count views
        $mysql->query('UPDATE '.prefix.'_images SET views=views+1 WHERE name='.db_squote($imageName));

        // Temporaly disabled cached
        if ($this->params['cache']) {
            //$havePerm = (is_array($userROW) and (($userROW['status'] == 1) or ($userROW['status'] == 2) or ($row['author_id'] == $userROW['id'])));
            $cacheFileName = md5('gallery'.$gallery['id'].$gallery['name'].$imageName).'.txt';
            $cacheData = cacheRetrieveFile($cacheFileName, (int) $this->params['cache_expire'], 'gallery');
            if ($cacheData != false) {
                //return $template['vars']['mainblock'] .= $cacheData;
            }
        }

        $row = $mysql->record('SELECT * FROM '.prefix.'_images WHERE folder='.db_squote($gallery['name']).' and name='.db_squote($imageName).' ORDER BY date LIMIT 1');

        $row['id'] = (int) $row['id'];
        $row['com'] = (int) $row['com'];
        $row['views'] = (int) $row['views'];
        $row['width'] = (int) $row['width'];
        $row['height'] = (int) $row['height'];
        $row['name'] = secure_html($row['name']);
        $row['description'] = secure_html($row['description']);

        // Reload meta-description of page
        if ($this->params['if_description']) {
            $SYSTEM_FLAGS['meta']['description'] = $row['description'];
        }

        // Prepare date to generate output Prev and Next
        templateLoadVariables(true);
        $nav = $TemplateCache['site']['#variables']['navigation'];

        // Prev image, if isset
        $pimage = $mysql->select('SELECT name FROM '.prefix.'_images WHERE folder='.db_squote($gallery['name']).' AND id<'.db_squote($row['id']).' ORDER BY `id` desc limit 1');
        if (isset($pimage[0])) {
            $imageName = secure_html($pimage[0]['name']);
            $paginationParams = [
                'pluginName' => 'gallery', 'pluginHandler' => 'image',
                'params' => ['id' => $gallery['id'], 'gallery' => $gallery['name'], 'name' => $imageName], 'xparams' => [],
                'paginator' => ['page', 0, false], ];
            $prevlink = str_replace('%page%', $this->lang['gallery:prevlink'], str_replace('%link%', generatePageLink($paginationParams, 0), $nav['prevlink']));
        } else {
            $prevlink = '';
        }

        // Next image, if isset
        $nimage = $mysql->select('select name from '.prefix.'_images where folder='.db_squote($gallery['name']).' and id>'.db_squote($row['id']).' ORDER BY `id` asc limit 1');
        if (isset($nimage[0])) {
            $imageName = secure_html($nimage[0]['name']);
            $paginationParams = [
                'pluginName' => 'gallery', 'pluginHandler' => 'image',
                'params' => ['galleryID' => $gallery['id'], 'gallery' => $gallery['name'], 'name' => $imageName], 'xparams' => [],
                'paginator' => ['page', 0, false], ];
            $nextlink = str_replace('%page%', $this->lang['gallery:nextlink'], str_replace('%link%', generatePageLink($paginationParams, 0), $nav['nextlink']));
        } else {
            $nextlink = '';
        }

        // Комментарии не тронуты
        // Вернуться и доделать. Эй, куда пошел
        if (getPluginStatusActive('comments')) {
            // Prepare params for call
            // module - DB table images
            $callingCommentsParams = ['outprint' => true, 'total' => $row['com'], 'module' => 'images'];

            include_once root.'/plugins/comments/inc/comments.show.php';

            $tcvars = [];
            // Show comments [ if not skipped ]
            $tcvars['vars']['entries'] = comments_show($row['id'], 0, 0, $callingCommentsParams);
            $tcvars['vars']['comnum'] = $row['com'];

            $tcvars['vars']['more_comments'] = '';
            $tcvars['regx']['#\[more_comments\](.*?)\[\/more_comments\]#is'] = '';

            // Show form for adding comments
            if (! pluginGetVariable('comments', 'regonly') or is_array($userROW)) {
                $tcvars['vars']['form'] = comments_showform($row['id'], $callingCommentsParams);
                $tcvars['regx']['#\[regonly\](.*?)\[\/regonly\]#is'] = '';
                $tcvars['regx']['#\[commforbidden\](.*?)\[\/commforbidden\]#is'] = '';
            } else {
                $tcvars['vars']['form'] = '';
                $tcvars['regx']['#\[regonly\](.*?)\[\/regonly\]#is'] = '$1';
                $tcvars['regx']['#\[commforbidden\](.*?)\[\/commforbidden\]#is'] = '';
            }
            $tcvars['regx']['#\[comheader\](.*)\[/comheader\]#is'] = ($row['com']) ? '$1' : '';

            $tPath = locatePluginTemplates(['comments.internal'], 'comments');

            $tpl->template('comments.internal', $tPath['comments.internal']);
            $tpl->vars('comments.internal', $tcvars);
            //$tvars['vars']['plugin_comments'] = $tpl->show('comments.internal');
        }

        $tPath = locatePluginTemplates(['page_image'], 'gallery', 0, $gallery['skin']);
        $tVars = [
            'plugin_title' => $this->pluginTitle,
            'url_tpl' => $tPath['url:page_image'],
            'url_main' => generatePluginLink('gallery', null),
            'gallery' => [
                'url' => generatePluginLink('gallery', 'gallery', ['id' => $gallery['id'], 'name' => $gallery['name']]),
                'title' => $gallery['title'],
                'description' => $gallery['description'],
                'keywords' => $gallery['keywords'],
            ],
            'image' => [
                'name' => $row['name'],
                'src' => images_url.'/'.$gallery['name'].'/'.$row['name'],
                'src_thumb' => images_url . '/' . $gallery['name'] . '/thumb/' . $row['name'],
                'description' => $row['description'],
                'date' => LangDate('j.m.Y - H:i', $row['date']),
                'dateStamp' => $row['date'],
                'com' => $row['com'],
                'views' => $row['views'],
                'width' => $row['width'],
                'height' => $row['height'],
                'size' => is_readable($fname = images_dir.'/'.$gallery['name'].'/'.$row['name']) ? formatSize(filesize($fname)) : '-',
            ],
            'nextlink' => $nextlink,
            'gallerylink' => str_replace('%page%', $gallery['title'], str_replace('%link%', generatePluginLink('gallery', 'gallery', ['name' => $gallery['name']]), $nav['link_page'])),
            'prevlink' => $prevlink,
            'plugin_comments' => $tpl->show('comments.internal'),
        ];

        $template['vars']['mainblock'] .= $output = $twig->render($tPath['page_image'].'page_image.tpl', $tVars);
        if ($this->params['cache']) {
            cacheStoreFile($cacheFileName, $output, 'gallery');
        }
    }
}
