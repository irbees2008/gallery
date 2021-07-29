<table class="table table-condensed">
    <thead>
        <tr>
            <th>#</th>
            <th></th>
            <th>{{ lang['gallery:label_widget_name'] }}</th>
            <th>{{ lang['gallery:label_widget_title'] }}</th>
            <th>{{ lang['gallery:label_gallery'] }}</th>
            <th>{{ lang['gallery:label_rand'] }}</th>
            <th>{{ lang['gallery:label_skin'] }}</th>
            <th class="text-right">{{ lang['gallery:label_action'] }}</th>
        </tr>
    </thead>
    <tbody>
    {% for item in items %}
        <tr>
            <td>{{ item.id }}</td>
            <td>{% if item.isActive %}<i class="fa fa-check text-success"></i>{% else %}<i class="fa fa-times text-danger"></i>{% endif %}</td>
            <td><code>&#123;&#123; plugin_gallery_{{ item.name }} }}</code></td>
            <td>{{ item.title }}</td>
            <td>{{ item.gallery }}</td>
            <td>{{ item.rand }}</td>
            <td>{{ item.skin }}</td>
            <td class="text-right">
                <div class="btn-group btn-group-sm">
                    <a href="admin.php?mod=extra-config&plugin=gallery&section=widget_add&id={{ item.id }}" class="btn btn-outline-primary">
                        <i class="fa fa-pencil"></i>
                    </a>
                </div>
                <div class="btn-group btn-group-sm">
                    <a href="#" onclick="confirmit('admin.php?mod=extra-config&plugin=gallery&section=widget_dell&id={{ item.id }}','{{ lang['gallery:sure_del'] }}');return false;" class="btn btn-outline-danger">
                        <i class="fa fa-trash-o"></i>
                    </a>
                </div>
            </td>
        </tr>
    {% else %}
        <tr><td colspan="9">{{ lang['gallery:not_found'] }}</td></tr>
    {% endfor %}
    </tbody>
</table>
