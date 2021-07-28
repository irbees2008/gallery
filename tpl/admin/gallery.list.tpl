<table class="table table-condensed">
    <thead>
        <tr>
            <th>#</th>
            <th></th>
            <th>{{ lang['gallery:label_name'] }}</th>
            <th>{{ lang['gallery:label_title'] }}</th>
            <th>{{ lang['gallery:label_skin'] }}</th>
            <th class="text-right">{{ lang['gallery:label_action'] }}</th>
        </tr>
    </thead>
    <tbody>
    {% for item in items %}
        <tr>
            <td>{{ item.id }}</td>
            <td>{% if item.isActive %}<i class="fa fa-check text-success"></i>{% else %}<i class="fa fa-times text-danger"></i>{% endif %}</td>
            <td>{{ item.name }}</td>
            <td>{{ item.title }}</td>
            <td>{{ item.skin }}</td>
            <td class="text-right">
                <div class="btn-group">
                    <a href="admin.php?mod=extra-config&plugin=gallery&action=edit&id={{ item.id }}" class="btn btn-default"><i class="fa fa-pencil"></i></a>
                    {% if item.isActive %}<a href="{{ item.url }}" class="btn btn-default" target="_blank"><i class="fa fa-external-link"></i></a>{% endif %}
                    <a href="admin.php?mod=extra-config&plugin=gallery&action=move_up&id={{ item.id }}" class="btn btn-default"><i class="fa fa-arrow-up"></i></a>
                    <a href="admin.php?mod=extra-config&plugin=gallery&action=move_down&id={{ item.id }}" class="btn btn-default"><i class="fa fa-arrow-down"></i></a>
                    <a href="#" onclick="confirmIt('admin.php?mod=extra-config&plugin=gallery&action=dell&id={{ item.id }}','{{ lang['sure_del'] }}');return false;" class="btn btn-danger"><i class="fa fa-trash-o"></i></a>
                </div>
            </td>
        </tr>
    {% else %}
        <tr><td colspan="8">{{ lang['not_found'] }}</td></tr>
    {% endfor %}
    </tbody>
</table>