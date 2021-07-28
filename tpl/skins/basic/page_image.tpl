<h2 class="section-title">{{ img.name }}</h2>

<section class="section">
    <div class="card">
        <img src="{{ img.src }}" alt="{{ img.name }}" class="card-img-top img-fluid" />
        <div class="card-body">
        <p class="card-text">{{ img.description }}</p>
        <p class="card-text">
            <small class="text-muted pull-left">Загружено: {{ img.dateStamp | cdate }}</small>
            <small class="text-muted pull-right"><i class="fa fa-comments"></i> {{ img.com }} • <i class="fa fa-eye"></i> {{ img.views }}</small>
        </p>
        </div>
    </div>
</section>

<nav class="section">
    <ul class="pagination justify-content-center">
        {{ prevlink }}
        {{ gallerylink }}
        {{ nextlink }}
    </ul>
</nav>

{{ plugin_comments }}