<?php
/**
 * Template single – Incarichi conferiti ai dipendenti
 *
 * @package Design_Comuni_Italia
 */

get_header();
?>

<main>
    <?php
while ( have_posts() ) :
    the_post();

    $prefix = '_dci_galleria_';                          
    $id     = get_the_ID();
    $descrizione = get_post_meta( $id, $prefix . 'descrizione_breve', true );
    $foto_array = get_post_meta($id, $prefix.'foto_gallery',true);
    $url_video_group = get_post_meta($id, $prefix . 'url_video_group', true);
    $tipo = get_post_meta($id, $prefix.'tipo_galleria',true);
    $video_array = get_post_meta($id, $prefix . 'video', true);
    ?>
    <div class="container" id="main-container">
        <div class="row">
            <div class="col px-lg-4">
                <?php get_template_part("template-parts/common/breadcrumb"); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 px-lg-4 py-lg-2">
                <h1 data-audio><?php the_title(); ?></h1>
                <h2 class="visually-hidden" data-audio>Dettagli galleria</h2>
                <span>
                    <?= $descrizione;?>
                </span>
            </div>
        </div>
    </div>

    <div class="bg-grey-dsk">
        <div class="gallery-container mb-3">
            <br>

            <div class="gallery-grid">
                <?php if(!empty($foto_array) && is_array($foto_array)){ ?>
                    <?php foreach($foto_array as $foto){ 
                        // Recupera ID attachment dall’URL
                        $attachment_id = attachment_url_to_postid($foto);
                        $image_title   = $attachment_id ? get_the_title($attachment_id) : "Immagine della galleria";
                        $image_alt     = $attachment_id ? get_post_meta($attachment_id, '_wp_attachment_image_alt', true) : "Immagine della galleria";
                    ?>
                        <div class="gallery-item image">
                            <a href="<?= esc_url($foto); ?>" target="_blank">
                                <img src="<?php echo esc_url($foto); ?>"
                                    alt="<?php echo esc_attr($image_alt); ?>"
                                    onerror="this.style.backgroundColor='#667eea'; this.alt='Immagine non disponibile'">
                                <div class="overlay">
                                    <h3 class="gallery-title"><?php echo esc_html($image_title); ?></h3>
                                    <?php if($image_alt): ?>
                                        <p class="gallery-description"><?php echo esc_html($image_alt); ?></p>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                    <?php } ?>
                <?php } ?>

                <?php if(!empty($url_video_group) && is_array($url_video_group)){ ?>
                    <?php foreach ($url_video_group as $video){ ?>
                        <div class="gallery-item video">
                            <iframe 
                                title="<?php echo esc_attr($video['titolo']); ?>" 
                                src="<?php echo esc_url($video['url_video']); ?>" 
                                frameborder="0" 
                                allowfullscreen>
                            </iframe>
                            <div class="overlay">
                                <h3 class="gallery-title"><?= $video['titolo']; ?></h3>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>

                <?php if(!empty($video_array) && is_array($video_array)){ ?>
                    <?php foreach ($video_array as $video){ ?>
                        <div class="gallery-item video">
                            <video controls>
                                <source src="<?= esc_url($video); ?>" type="video/mp4">
                                Il tuo browser non supporta il formato video.
                            </video>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>

    <?php 
    get_template_part( 'template-parts/common/valuta-servizio' ); 
    get_template_part( 'template-parts/common/assistenza-contatti' ); 
    ?>

    <?php
endwhile;
?>
</main>

<?php get_footer(); ?>
<style>
.gallery-container {
    max-width: 1200px;
    margin: 0 auto;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    justify-content: center;
}

.gallery-item {
    position: relative;
    overflow: hidden;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    background: #000;
}

.gallery-item:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
}

/* ---------------- IMMAGINI ---------------- */
.gallery-item.image {
    aspect-ratio: 4/3;
}

.gallery-item.image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 20px;
    transition: transform 0.4s ease;
}

.gallery-item.image:hover img {
    transform: scale(1.1);
}

/* Overlay immagini */
.gallery-item.image .overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.8) 100%);
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 30px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gallery-item.image:hover .overlay {
    opacity: 1;
}

.gallery-title {
    color: white;
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 10px;
    transform: translateY(20px);
    transition: transform 0.3s ease;
}

.gallery-description {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1rem;
    line-height: 1.5;
    transform: translateY(20px);
    transition: transform 0.3s ease;
}

.gallery-item.image:hover .gallery-title,
.gallery-item.image:hover .gallery-description {
    transform: translateY(0);
}

.gallery-title {
    transition-delay: 0.1s;
}

.gallery-description {
    transition-delay: 0.2s;
}

/* ---------------- VIDEO ---------------- */
.gallery-item.video {
    aspect-ratio: 16/9;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.gallery-item.video iframe,
.gallery-item.video video {
    width: 100%;
    height: 100%;
    border-radius: 20px;
    border: none;
    object-fit: cover;
}

/* Didascalia video */
.video-caption {
    color: #fff;
    font-size: 1rem;
    margin-top: 8px;
    text-align: center;
}

/* ---------------- RESPONSIVITÀ ---------------- */
@media (max-width: 768px) {
    .gallery-grid {
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
    }
}

@media (max-width: 480px) {
    .gallery-grid {
        grid-template-columns: 1fr;
    }
}
</style>
