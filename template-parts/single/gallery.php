<?php
global $gallery;
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>

<div class="gallery-grid mb-2">
<?php 
$foto_counter = 1;
foreach ($gallery as $photo): 
    $attachment_id = attachment_url_to_postid($photo);
    $image_title   = $attachment_id ? get_the_title($attachment_id) : 'Foto ' . $foto_counter;
    $image_alt     = $attachment_id ? get_post_meta($attachment_id, '_wp_attachment_image_alt', true) : 'Immagine della galleria';
?>
    <div class="gallery-item image">
        <a 
            href="<?= esc_url($photo); ?>"
            class="glightbox"
            data-gallery="galleria"
            data-title="<?= esc_attr($image_title); ?>"
        >
            <div class="gallery-info">
                <i class="fas fa-search-plus"></i>
                <p class="gallery-title"><?= esc_html($image_title); ?></p>
            </div>
            <img 
                src="<?= esc_url($photo); ?>" 
                alt="<?= esc_attr($image_alt); ?>"
                width="100%"
                loading="lazy"
            />
        </a>
    </div>
<?php 
$foto_counter++;
endforeach; 
?>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    GLightbox({
        selector: '.glightbox',
        touchNavigation: true,
        zoomable: true,
        loop: true
    });

    const gallery = document.querySelector('.gallery-grid');
    const loader  = document.getElementById('gallery-loader');
    const media   = gallery.querySelectorAll('img');
    let loaded = 0;

    if (media.length === 0) finalize();

    function finalize() {
        if (loader) loader.style.display = 'none';
        gallery.classList.add('is-loaded');
    }

    function check() {
        loaded++;
        if (loaded === media.length) finalize();
    }

    media.forEach(img => {
        img.complete ? check() : img.addEventListener('load', check);
    });
});
</script>


<style>
  .gallery-grid {
    display: grid;
    gap: 25px; 
    transition: opacity .4s ease; 
}

/* Desktop */
@media (min-width: 1200px) {
    .gallery-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* Tablet */
@media (min-width: 768px) and (max-width: 1199px) {
    .gallery-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Mobile */
@media (max-width: 767px) {
    .gallery-grid {
        grid-template-columns: 1fr;
    }
}


.gallery-grid.is-loaded {
    opacity: 1;
    visibility: visible;
}

.gallery-item {
    position: relative;
    overflow: hidden;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 6px 18px rgba(0,0,0,.1);
    aspect-ratio: 4 / 3;
}

.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .4s ease, filter .4s ease;
}

.gallery-item:hover img {
    transform: scale(1.08);
    filter: blur(4px);
}

.gallery-info {
    position: absolute;
    inset: 0;
    z-index: 10;
    background: rgba(0,0,0,.6);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity .4s ease;
}

.gallery-item:hover .gallery-info {
    opacity: 1;
}

.gallery-info i,
.gallery-info p {
    font-size: 1.5rem;
    margin-bottom: 5px;
    color: #fff;
    transform: translateY(20px);
    transition: transform .4s ease;
}

.gallery-item:hover .gallery-info i,
.gallery-item:hover .gallery-info p {
    transform: translateY(0);
}
</style>
