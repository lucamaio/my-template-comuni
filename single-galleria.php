<?php
/**
 * Template single – Galleria moderna con loader
 *
 * @package Design_Comuni_Italia
 */

get_header();

while ( have_posts() ) :
    the_post();

    $prefix          = '_dci_galleria_';
    $id              = get_the_ID();
    $descrizione     = get_post_meta( $id, $prefix . 'descrizione_breve', true );
    $foto_array      = get_post_meta( $id, $prefix . 'foto_gallery', true );
    $url_video_group = get_post_meta( $id, $prefix . 'url_video_group', true );
    $video_array     = get_post_meta( $id, $prefix . 'video', true );

    /* Unione contenuti */
    $all_items = [];

    if ( !empty($foto_array) ) {
        foreach ( $foto_array as $foto ) {
            $all_items[] = ['type' => 'foto', 'data' => $foto];
        }
    }

    if ( !empty($url_video_group) ) {
        foreach ( $url_video_group as $video ) {
            $all_items[] = ['type' => 'video_embed', 'data' => $video];
        }
    }

    if ( !empty($video_array) ) {
        foreach ( $video_array as $video ) {
            $all_items[] = ['type' => 'video_file', 'data' => $video];
        }
    }
?>
<div class="container" id="main-container">
    <div class="row">
        <div class="col px-lg-4">
            <?php get_template_part("template-parts/common/breadcrumb"); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 px-lg-4 py-lg-2">
            <h1><?php the_title(); ?></h1>
            <h3 class="visually-hidden">Dettagli galleria</h3>
            <p><?= esc_html($descrizione); ?></p>
        </div>
        <div class="col-lg-3 offset-lg-1">
            <?php
            $inline = true;
            get_template_part('template-parts/single/actions');
            ?>
        </div>
    </div>
</div>

<main class="gallery-page bg-grey-dsk py-5">
    <div class="container mb-5">

        <!-- LOADER -->
        <div id="gallery-loader" class="gallery-loader">
            <div class="spinner"></div>
            <p>Caricamento galleria…</p>
        </div>

        <!-- GALLERIA -->
        <div class="gallery-grid">
            <?php 
                $foto_counter = 1;
                foreach ( $all_items as $item ): ?>

                <?php if ( $item['type'] === 'foto' ):
                    $foto = $item['data'];
                    $attachment_id = attachment_url_to_postid($foto);
                    $image_title   = $attachment_id ? get_the_title($attachment_id) : 'Foto ' . $foto_counter;
                    $image_alt     = $attachment_id ? get_post_meta($attachment_id, '_wp_attachment_image_alt', true) : 'Immagine della galleria';
                ?>
                    <div class="gallery-item image">
                        <a href="<?= esc_url($foto); ?>" class="glightbox" data-gallery="galleria" data-title="<?= esc_attr($image_title); ?>">
                            <div class="gallery-info">
                                <i class="fas fa-search-plus"></i>
                                <p class="gallery-title"><?= esc_html($image_title); ?></p>
                            </div>
                            <img src="<?= esc_url($foto); ?>" alt="<?= esc_attr($image_alt); ?>">
                            <span class="badge">Foto</span>
                        </a>
                    </div>

                <?php elseif ( $item['type'] === 'video_embed' ):
                    $video = $item['data']; ?>
                    <div class="gallery-item video">
                        <div class="video-container">
                            <iframe src="<?= esc_url($video['url_video']); ?>" title="<?= esc_attr($video['titolo']); ?>" loading="lazy" allowfullscreen></iframe>
                        </div>
                        <span class="badge badge-video">Video</span>
                    </div>

                <?php elseif ( $item['type'] === 'video_file' ):
                    $video = $item['data']; ?>
                    <div class="gallery-item video">
                        <div class="video-container">
                            <video controls preload="metadata">
                                <source src="<?= esc_url($video); ?>" type="video/mp4">
                            </video>
                        </div>
                        <span class="badge badge-video">Video</span>
                    </div>
                <?php endif; ?>
                    <?php $foto_counter++; ?>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<?php get_template_part("template-parts/common/valuta-servizio"); ?>
<?php get_template_part("template-parts/common/assistenza-contatti"); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    GLightbox({
        selector: '.glightbox',
        touchNavigation: true,
        zoomable: true,
        loop: true
    });

    const gallery = document.querySelector('.gallery-grid');
    const loader = document.getElementById('gallery-loader');
    const media  = gallery.querySelectorAll('img, video, iframe');
    let loaded = 0;

    if (media.length === 0) finalize();

    function finalize() {
        loader.style.display = 'none';
        gallery.classList.add('is-loaded');
    }

    function check() {
        loaded++;
        if (loaded === media.length) finalize();
    }

    media.forEach(el => {
        if (el.tagName === 'IMG') {
            el.complete ? check() : el.addEventListener('load', check);
        } else {
            el.addEventListener('loadeddata', check, { once: true });
            el.addEventListener('load', check, { once: true });
            setTimeout(check, 4000);
        }
    });
});
</script>

<style>
/* Loader */
.gallery-loader {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 300px;
    font-weight: 500;
}

.spinner {
    width: 48px;
    height: 48px;
    border: 4px solid #ddd;
    border-top: 4px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    opacity: 0;
    visibility: hidden;
    transition: opacity .4s ease;
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

.video-container {
    position: relative;
    padding-top: 56.25%;
}

.video-container iframe,
.video-container video {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}

.badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: #007bff;
    color: #fff;
    padding: 5px 10px;
    border-radius: 10px;
    font-size: .8rem;
    z-index: 5;
}

.badge-video {
    background: #e63946;
}
</style>

<?php endwhile; get_footer(); ?>
