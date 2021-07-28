<h2 class="section-title">{{ lang['gallery:title'] }}</h2>

<section class="section">
    <div class="card-columns">
        {% for gallery in galleries %}
        <div class="card card-inverse">
            <a href="{{ gallery.url }}" title="{{ gallery.title }}">
                <img src="{{ gallery.src }}" alt="{{ gallery.title }}" class="card-img-top img-fluid" />
                <div class="card-img-overlay">
                    <h4 class="card-title">{{ gallery.title }}</h4>
                    <p class="card-text"><i class="fa fa-files-o"></i> {{ gallery.count }}</p>
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