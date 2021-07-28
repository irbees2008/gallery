<h2 class="section-title">{{ lang['gallery:title'] }} {{ gallery.title }}</h2>

<section class="section">
    <p>{{ gallery_description }}</p>
    <div class="card-columns">
        {% for img in images %}
        <div class="card card-inverse">
            <a href="{{ img.url }}" title="{{ img.name }}">
                <img src="{{ img.src_thumb }}" alt="{{ img.name }}" class="card-img img-fluid" />
                <div class="card-img-overlay">
                    <h4 class="card-title">{{ img.name }}</h4>
                    <p class="card-text"><i class="fa fa-comments"></i> {{ img.com }} • <i class="fa fa-eye"></i> {{ img.views }}</p>
                    <p class="card-text">{{ img.description }}</p>
                </div>
            </a>
        </div>
        {% endfor %}
    </div>
</section>

<nav class="section justify-content-center">
    <ul class="pagination">
        {{ pagesss }}
    </ul>
</nav>