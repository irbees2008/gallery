{% if dependencies %}
	<div class="alert alert-warning m-3">
		Для полноценной работы плагина требуется активация плагина:
		<ul class="m-0">
		{% for item in dependencies %}
			<li>{{ item }}</li>
		{% endfor %}
		</ul>
	</div>
{% endif %}

<ul class="nav nav-pills m-3 d-md-flex d-block" role="tablist">
	<li class="nav-item">
		<a href="?mod=extra-config&plugin=gallery" class="nav-link {{ section ? '' : 'active' }}">
		{{ lang['gallery:config'] }}
	</a>
	</li>
	<li class="nav-item">
		<a href="?mod=extra-config&plugin=gallery&section=list" class="nav-link {{ 'list' == section ? 'active' : '' }}">
		{{ lang['gallery:button_list'] }}</a>
	</li>
	<li class="nav-item">
		<a href="?mod=extra-config&plugin=gallery&section=widget_list" class="nav-link {{ 'widget_list' == section ? 'active' : '' }}">
			{{ lang['gallery:widgetList'] }}
		</a>
	</li>
</ul>
