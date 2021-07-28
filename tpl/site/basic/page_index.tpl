<h2 class="section-title">{{ plugin_title }}</h2>

<section class="section">
    <p>{{ plugin_description }}</p>
    <div class="card-columns">
        {% for gallery in galleries %}
        <div class="card bg-dark text-white">
            <a href="{{ gallery.url }}" title="{{ gallery.title }}" class=" text-white">
                <img src="{{ gallery.icon }}" alt="{{ gallery.title|e }}" class="card-img-top img-fluid" />
                <div class="card-img-overlay">
                    <h4 class="card-title">{{ gallery.title }}</h4>
                    <p class="card-text"><i class="fa fa-files-o"></i> {{ gallery.count }}</p>
                </div>
            </a>
        </div>
        {% endfor %}
    </div>
</section>

<nav class="section">
    <ul class="pagination justify-content-center">
        {{ pagesss }}
    </ul>
</nav>