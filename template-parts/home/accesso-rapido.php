<?php
global $boxes;
$box_accesso_rapido = $boxes;
?>

<?php if (!empty($boxes)) { ?>
<div class="container py-5">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <h2 class="title-xxlarge mb-4">Accesso rapido</h2>

    <div class="row g-4 custom-styles">

        <?php foreach ($boxes as $box) {

            $colore_sfondo = $box['colore'] ?? false;
            $sfondo_scuro = $colore_sfondo ? is_this_dark_hex($colore_sfondo) : true;
        ?>

        <div class="col-md-6 col-xl-4">

            <!-- LINK -->
            <a target="_blank" href="<?php echo esc_url($box['link_message']); ?>"
               style="<?= ($colore_sfondo) ? 'background-color:' . $colore_sfondo : '' ?>"
               class="card card-teaser <?= $colore_sfondo ? '' : 'bg-neutral' ?> rounded mt-0 p-3">

                <div class="cmp-card-simple card-wrapper pb-0 rounded">

                    <div style="border: none;">

                        <div class="card-body d-flex align-items-center">

                            <?php if (
                                isset($box['icona_message']) &&
                                $box['icona_message'] &&
                                array_key_exists('icon', $box) &&
                                !empty($box['icon'])
                            ) { ?>

                                <div class="avatar size-lg me-3"
                                     style="min-width:50px;width:50px;height:50px;
                                            display:flex;justify-content:center;
                                            align-items:center;
                                            background-color:#f0f0f0;
                                            border-radius:50%;">

                                    <i class="fas fa-<?php echo htmlspecialchars($box['icon']); ?>"
                                       style="color:#555;font-size:24px;"></i>

                                </div>

                            <?php } ?>

                            <div class="flex-grow-1">

                                <h3 class="card-title t-primary title-xlarge text-dark"
                                    style="font-size:1.5rem;line-height:1.2;">

                                    <?php echo $box['titolo_message']; ?>

                                    <!-- ICONA -->
                                    <svg class="icon icon-white"
                                         style="width:20px;height:20px;margin-left:8px;">
                                        <use href="#it-external-link"></use>
                                    </svg>

                                </h3>

                                <?php if (!empty($box['desc_message'])) { ?>

                                    <p class="card-text text-sans-serif mb-0 description text-muted"
                                       style="font-size:1rem;line-height:1.5;">

                                        <?php echo $box['desc_message']; ?>

                                    </p>

                                <?php } ?>

                            </div>

                        </div>

                    </div>

                </div>

            </a>

        </div>

        <?php } ?>

    </div>

</div>
<?php } ?>


<style>

/* ===============================
   CARD BASE
================================ */

.custom-styles .card {

    background-color: #f9f9f9;
    border: 1px solid #e0e0e0;

    box-shadow: 0 2px 6px rgba(0,0,0,0.1);

    transition: transform 0.3s ease,
                box-shadow 0.3s ease;

    display: flex;
    flex-direction: column;

    height: 100%;
    min-height: 120px;

    position: relative;

    /* FIX TAP ANDROID */
    touch-action: manipulation;
}


/* Hover */
.custom-styles .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}


/* ===============================
   TESTI
================================ */

.custom-styles .card-title {

    margin-bottom: 6px;
    font-size: 1.2rem;

    color: #333;

    flex-grow: 1;

    margin-right: 40px;
}

.custom-styles .description {

    font-size: 0.9rem;
    line-height: 1.4;

    color: #777;

    margin-top: 8px;
}


/* ===============================
   AVATAR
================================ */

.custom-styles .avatar {

    background-color: #f0f0f0;
    border-radius: 50%;

    padding: 2px;
}

.custom-styles .avatar i {
    color: #555;
    font-size: 24px;
}


/* ===============================
   BODY
================================ */

.custom-styles .card-body {

    display: flex;
    align-items: center;

    justify-content: flex-start;

    padding: 15px;

    flex-grow: 1;
}


/* ===============================
   ICONA SVG (FIX CLICK)
================================ */

.custom-styles .card-title svg.icon-white {

    fill: #000 !important;

    position: absolute;

    top: 10px;
    right: 10px;

    width: 20px;
    height: 20px;

    z-index: 2;

    transition: transform 0.3s ease,
                fill 0.3s ease;

    /* 🔴 FIX PRINCIPALE */
    pointer-events: none;
}


/* Hover icona */
.custom-styles .card-title svg.icon-white:hover {

    transform: scale(1.1);
    fill: #f0f0f0 !important;
}


/* ===============================
   GRID
================================ */

.custom-styles .row {

    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));

    gap: 20px;
}


/* ===============================
   MOBILE FIX
================================ */

@media (hover: none) {

    .custom-styles .card:hover {

        transform: none;
        box-shadow: none;
        background-color: inherit;
    }
}


/* ===============================
   RESPONSIVE
================================ */

@media (max-width: 768px) {

    .custom-styles .card-body {
        flex-direction: row;
        align-items: center;
        text-align: left;

        padding: 8px 12px;
    }

    .custom-styles .flex-grow-1 {

        display: flex;
        flex-direction: column;

        justify-content: center;
    }

    .custom-styles .avatar {

        margin-right: 12px;
        flex-shrink: 0;
    }

    .custom-styles .card-title {
        margin-bottom: 4px;
        margin-right: 0;
    }

    .custom-styles .description {
        margin: 0;
    }
}

</style>
