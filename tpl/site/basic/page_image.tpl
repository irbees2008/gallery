<h2 class="section-title">{{ img.name }}</h2>

<section class="section">
    <div class="card">
        <img src="{{ img.src }}" alt="{{ img.name|e }}" class="card-img-top img-fluid" />
        <div class="card-body">
        <p class="card-text">{{ img.description }}</p>
        <p class="card-text">
            <small class="text-muted pull-left"><i class="fa fa-calendar"></i>&nbsp;{{ img.dateStamp | cdate }}</small>
            <small class="text-muted pull-right"><i class="fa fa-file-archive-o"></i>&nbsp;{{ img.size }} • <i class="fa fa-comments"></i>&nbsp;{{ img.com }} • <i class="fa fa-eye"></i>&nbsp;{{ img.views }}</small>
        </p>
        </div>
    </div>
</section>

<nav class="section">
    <ul class="pagination justify-content-center">
        {{ prevlink }}{{ gallerylink }}{{ nextlink }}
    </ul>
</nav>

{{ plugin_comments }}