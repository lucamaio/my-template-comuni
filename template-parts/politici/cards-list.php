<?php 
global $post;

$description = dci_get_meta('descrizione_breve');
$incarichi = dci_get_meta('incarichi') ?? [];
$img = dci_get_meta('foto');

/* =========================
   NORMALIZZAZIONE TESTI
========================= */

// Titolo
if (preg_match('/[A-Z]{5,}/', $post->post_title)) {
    $titolo = ucfirst(strtolower($post->post_title));
} else {
    $titolo = $post->post_title;
}

// Descrizione
if (preg_match('/[A-Z]{5,}/', $description)) {
    $descrizione = ucfirst(strtolower($description));
} else {
    $descrizione = $description;
}
?>

<div class="col-md-6 col-xl-4">
    <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
        <div class="card no-after rounded">

            <div class="row g-2 g-md-0 flex-column flex-md-row">

                <!-- CONTENUTO (SINISTRA) -->
                <div class="<?php echo $img ? 'col-8 col-md-8' : 'col-12'; ?>">
                    <div class="card-body">

                        <!-- TITOLO -->
                        <a class="text-decoration-none" href="<?php echo get_permalink(); ?>">
                            <h3 class="h5 card-title"><?php echo esc_html($titolo); ?></h3>
                        </a>

                        <!-- INCARICHI -->
                        <?php if (!empty($incarichi)) { ?>
                            <div class="mb-2">
                                <?php foreach ($incarichi as $incarico_id) { ?>
                                    <span class="badge bg-primary badge-sm me-1">
                                        <?php echo esc_html(get_the_title($incarico_id)); ?>
                                    </span>
                                <?php } ?>
                            </div>
                        <?php } ?>

                        <!-- DESCRIZIONE -->
                        <?php if (!empty($descrizione)) { ?>
                            <p class="card-text d-none d-md-block">
                                <?php echo esc_html($descrizione); ?>
                            </p>
                        <?php } ?>

                    </div>
                </div>

                <!-- IMMAGINE (DESTRA) -->
                <?php if ($img) { ?>
                    <div class="col-4 col-md-4 d-flex align-items-center justify-content-center p-2">
                        <div class="img-wrapper">
                            <?php dci_get_img($img, 'img-fluid img-responsive foto-fixed'); ?>
                        </div>
                    </div>
                <?php } ?>

            </div>

        </div>
    </div>
</div>
<style>
.foto-soft-style {
    border: 1px solid #ddd;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    padding: 2px;
    background-color: #fff;
    border-radius: 4px;
}
.img-wrapper {
    width: 100%;
    max-width: 110px;   /* dimensione massima controllata */
    height: 110px;      /* altezza fissa */
    overflow: hidden;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #fff;
}

.foto-fixed {
    width: 100%;
    height: 100%;
    object-fit: cover;  /* evita deformazioni */
    border-radius: 4px;
}
</style>