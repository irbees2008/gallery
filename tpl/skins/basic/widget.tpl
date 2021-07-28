<div class="widget widget-gallery">
    <div class="widget-header">
        <h4 class="widget-title">{{ widget_title }}</h4>
    </div>
    <div class="widget-body">
        <div class="row">
        {% for img in images %}
            <p class="col-sm-12">
                <a href="{{ img.url }}" title="{{ img.title }}"><img src="{{ img.src_thumb }}" alt="{{ img.title }}" class="card-img img-fluid" /></a>
            </p>
        {% endfor %}
        </div>
    </div>
    <div class="widget-footer">
        <p><a href="{{ url_main }}" title="Страница плагина" class="text-muted pull-right">Страница плагина &raquo;</a></p>
    </div>
</div>