<?php
global $luogo, $title;

$prefix = '_dci_luogo_';
$img = dci_get_meta('immagine', $prefix, $luogo->ID);
$tipi = get_the_terms($luogo->ID, 'tipi_luogo');
$indirizzo = dci_get_meta('indirizzo', $prefix, $luogo->ID);
$posizione_gps = dci_get_meta('posizione_gps', $prefix, $luogo->ID);
$cap = dci_get_meta('cap', $prefix, $luogo->ID);
// $mail = dci_get_meta('mail', $prefix, $luogo->ID);
// $telefono = dci_get_meta('telefono', $prefix, $luogo->ID);

if ($luogo->post_status == "publish") {
    // Controlla se ci sono tipi e se il titolo corrisponde
    if (is_array($tipi) && count($tipi) > 0) {
        // Crea un array con i nomi dei tipi
        $tipo_nomi = array_map(function($tipo) {
            return $tipo->name;
        }, $tipi);

        if (in_array($title, $tipo_nomi)) {
            ?>
            <div class="container">
    <div class="row">
        <div class="col-lg-12">
            <div class="card modern-card mb-5">
                <div class="card-header">
                    <a href="<?php echo get_permalink($post->ID); ?>" style="text-decoration: none;" ><h3 class="card-title t-primary title-xlarge text-center"><?php echo $luogo->post_title; ?></h3></a>
                </div>
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-5">
                            <div class="full-image">
                                <?php dci_get_img($img, 'rounded-top img-fluid'); ?>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="py-4">
                                <ul class="location-list mt-2">
                                    <?php if (isset($indirizzo) && $indirizzo != ""): ?>
                                        <li>
                                            <strong><?php _e("Indirizzo", "design_comuni_italia"); ?></strong>
                                            <p><?php echo wpautop($indirizzo); ?></p>
                                        </li>
                                    <?php endif; ?>
                                    <?php if (isset($cap) && $cap != ""): ?>
                                        <li>
                                            <strong><?php _e("CAP", "design_comuni_italia"); ?></strong>
                                            <p><?php echo $cap; ?></p>
                                        </li>
                                    <?php endif; ?>
                                    <!-- <?php if (isset($mail) && $mail != ""): ?>
                                        <li>
                                            <strong><?php _e("Email", "design_comuni_italia"); ?></strong>
                                            <p><a href="mailto:<?php echo $mail; ?>"><?php echo $mail; ?></a></p>
                                        </li>
                                    <?php endif; ?>
                                    <?php if (isset($telefono) && $telefono != ""): ?>
                                        <li>
                                            <strong><?php _e("Telefono", "design_comuni_italia"); ?></strong>
                                            <p><?php echo $telefono; ?></p>
                                        </li>
                                    <?php endif; ?> -->
                                    <?php if (isset($posizione_gps["lat"]) && isset($posizione_gps["lng"])): ?>
                                        <li>
                                            <strong><?php _e("Naviga su Google Map", "design_comuni_italia"); ?></strong>
                                            <p>
                                            <button class="my_btn my_btn-primary my_btn-sm">
                                                <a href="https://www.google.com/maps/dir/<?php echo $posizione_gps["lat"]; ?>,<?php echo $posizione_gps["lng"]; ?>" target="_blank" style="color: white; text-decoration: none;">
                                                     <?php _e("Clicca qui per navigare", "design_comuni_italia"); ?>
                                                </a>
                                            </button>
                                            </p>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
            <?php
        }
    }
}
?>

<style>
    .modern-card {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        overflow: hidden;
        background-color: #f7f7f7;
        padding: 20px;
    }

    .full-image {
        width: 100%;
        height: auto;
        overflow: hidden;
        border-radius: 10px 10px 0 0;
        margin-top: 20px;
    }

    .full-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .location-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .location-list li {
        margin-bottom: 10px;
    }

    .location-list strong {
        font-weight: bold;
        margin-right: 10px;
    }

    .my_btn {
        border-radius: 10px;
        padding: 10px 10px;
        font-size: 12px;
        cursor: pointer;
        border: none;
        outline: none;
    }

    .my_btn-primary {
        background-color: #337ab7;
        border-color: #337ab7;
        color: #fff;
    }

    .my_btn-secondary {
        background-color: #666;
        border-color: #666;
        color: #fff;
    }

    .my_btn-lg {
        padding: 12px 30    px;
        font-size: 12px;
    }
    .text-left {
    text-align: left;
}   
</style>
