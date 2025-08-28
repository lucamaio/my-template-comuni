<?php
global $post, $inline, $hide_arguments;

$argomenti = get_the_terms($post, 'argomenti');
$tipi_notizia = get_the_terms($post, 'tipi_notizia');
$categorie_servizio = get_the_terms($post, 'categorie_servizio');
$tipi_unita_organizzativa = get_the_terms($post, 'tipi_unita_organizzativa');

$post_url = esc_url(get_permalink());
$tipi_luogo = get_the_terms($post->ID, 'tipi_luogo');
$tipo_evento = get_the_terms($post->ID, 'tipi_evento');
$tipi_commissario = get_the_terms($post->ID, 'tipi_commissario'); 

if ($hide_arguments) $argomenti = array();
?>

<!-- Condividi sui social -->
<div class="dropdown <?php echo $inline ? 'd-inline' : ''; ?>">
    <button class="btn btn-dropdown dropdown-toggle text-decoration-underline d-inline-flex align-items-center fs-0" type="button" id="shareActions" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="condividi sui social">
        <svg class="icon" aria-hidden="true">
            <use xlink:href="#it-share"></use>
        </svg>
        <small>Condividi</small>
    </button>
    <div class="dropdown-menu shadow-lg" aria-labelledby="shareActions">
        <div class="link-list-wrapper">
            <ul class="link-list" role="menu">
                <li role="none">
                    <a class="list-item" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $post_url; ?>" target="_blank" role="menuitem">
                        <svg class="icon" aria-hidden="true">
                            <use xlink:href="#it-facebook"></use>
                        </svg>
                        <span>Facebook</span>
                    </a>
                </li>
                <li role="none">
                    <a class="list-item" href="https://twitter.com/intent/tweet?text=<?php echo $post_url; ?>" target="_blank" role="menuitem">
                        <svg class="icon" aria-hidden="true">
                            <use xlink:href="#it-twitter"></use>
                        </svg>
                        <span>Twitter</span>
                    </a>
                </li>
                <li role="none">
                    <a class="list-item" href="https://www.linkedin.com/shareArticle?url=<?php echo $post_url; ?>" target="_blank" role="menuitem">
                        <svg class="icon" aria-hidden="true">
                            <use xlink:href="#it-linkedin"></use>
                        </svg>
                        <span>Linkedin</span>
                    </a>
                </li>
                <li role="none">
                    <a class="list-item" href="https://api.whatsapp.com/send?text=<?php echo $post_url; ?>" target="_blank" role="menuitem">
                        <svg class="icon" aria-hidden="true">
                            <use xlink:href="#it-whatsapp"></use>
                        </svg>
                        <span>Whatsapp</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Azioni -->
<div class="dropdown <?php echo $inline ? 'd-inline' : ''; ?>">
    <button class="btn btn-dropdown dropdown-toggle text-decoration-underline d-inline-flex align-items-center fs-0" type="button" id="viewActions" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <svg class="icon" aria-hidden="true">
            <use xlink:href="#it-more-items"></use>
        </svg>
        <small>Vedi azioni</small>
    </button>
    <div class="dropdown-menu shadow-lg" aria-labelledby="viewActions">
        <div class="link-list-wrapper">
            <ul class="link-list" role="menu">
                <li role="none">
                    <a class="list-item" href="#" onclick="window.print()" role="menuitem">
                        <svg class="icon" aria-hidden="true">
                            <use xlink:href="#it-print"></use>
                        </svg>
                        <span>Stampa</span>
                    </a>
                </li>
                <li role="none">
                    <a class="list-item" href="#" role="menuitem" onclick="window.listenElements(this, '[data-audio]')">
                        <svg class="icon" aria-hidden="true">
                            <use xlink:href="#it-hearing"></use>
                        </svg>
                        <span>Ascolta</span>
                    </a>
                </li>
                <li role="none">
                    <a class="list-item" href="mailto:?subject=<?php echo esc_html(get_the_title()); ?>&body=<?php echo esc_url(get_permalink()); ?>" role="menuitem">
                        <svg class="icon" aria-hidden="true">
                            <use xlink:href="#it-mail"></use>
                        </svg>
                        <span>Invia</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Tipi di notizia -->
<?php if (!empty($tipi_notizia) && is_array($tipi_notizia)) { ?>
<div class="mt-4 mb-4">
    <span class="subtitle-small">Tipo Notizia</span>
    <ul class="d-flex flex-wrap gap-1">
        <?php foreach ($tipi_notizia as $tip_not) { ?>
        <li>
            <a class="chip chip-simple" href="<?php echo esc_url(get_term_link($tip_not->term_id)); ?>">
                <span class="chip-label"><?php echo esc_html($tip_not->name); ?></span>
            </a>
        </li>
        <?php } ?>
    </ul>
</div>
<?php } ?>

<!-- Argomenti -->
<?php if (!empty($argomenti) && is_array($argomenti)) { ?>
<div class="mt-4 mb-4">
    <span class="subtitle-small">Argomenti</span>
    <ul class="d-flex flex-wrap gap-1">
        <?php foreach ($argomenti as $argomento) { ?>
        <li>
            <a class="chip chip-simple" href="<?php echo esc_url(get_term_link($argomento->term_id)); ?>">
                <span class="chip-label"><?php echo esc_html($argomento->name); ?></span>
            </a>
        </li>
        <?php } ?>
    </ul>
</div>
<?php } ?>

<!-- Categorie di servizio -->
<?php if (!empty($categorie_servizio) && is_array($categorie_servizio)) { ?>
<div class="mt-4 mb-4">
    <span class="subtitle-small">Categorie Servizio</span>
    <ul class="d-flex flex-wrap gap-1">
        <?php foreach ($categorie_servizio as $cat_serv) { ?>
        <li>
            <a class="chip chip-simple" href="<?php echo esc_url(get_term_link($cat_serv->term_id)); ?>">
                <span class="chip-label"><?php echo esc_html($cat_serv->name); ?></span>
            </a>
        </li>
        <?php } ?>
    </ul>
</div>
<?php } ?>

<!-- Tipi di unità organizzativa -->
<?php if (!empty($tipi_unita_organizzativa) && is_array($tipi_unita_organizzativa)) { ?>
<div class="mt-4 mb-4">
    <span class="subtitle-small">Tipo</span>
    <ul class="d-flex flex-wrap gap-1">
        <?php foreach ($tipi_unita_organizzativa as $tipo) { ?>
        <li>
            <div class="chip chip-simple">
                <span class="chip-label"><?php echo esc_html($tipo->name); ?></span>
            </div>
        </li>
        <?php } ?>
    </ul>
</div>
<?php } ?>

<!-- Tipi di evento -->
<?php if (!empty($tipo_evento) && is_array($tipo_evento)) { ?>
<div class="mt-4 mb-4">
    <span class="subtitle-small">Tipi evento</span>
    <ul class="d-flex flex-wrap gap-1">
        <?php foreach ($tipo_evento as $evento) { ?>
        <li>
            <a class="chip chip-simple" href="<?php echo esc_url(get_term_link($evento->term_id)); ?>">
                <span class="chip-label"><?php echo esc_html($evento->name); ?></span>
            </a>
        </li>
        <?php } ?>
    </ul>
</div>
<?php } ?>

<!-- Tipi di luogo -->
<?php if (!empty($tipi_luogo) && is_array($tipi_luogo)) { ?>
<div class="mt-4 mb-4">
    <span class="subtitle-small">Tipi luogo</span>
    <ul class="d-flex flex-wrap gap-1">
        <?php foreach ($tipi_luogo as $tipo_luogo) { ?>
        <li>
            <a class="chip chip-simple" href="<?php echo esc_url(get_term_link($tipo_luogo->term_id)); ?>">
                <span class="chip-label"><?php echo esc_html($tipo_luogo->name); ?></span>
            </a>
        </li>
        <?php } ?>
    </ul>
</div>
<?php } ?>

<!-- Tipi di commissario -->
<?php if (!empty($tipi_commissario) && is_array($tipi_commissario)) { ?>
<div class="mt-4 mb-4">
    <span class="subtitle-small">Commissari</span>
    <ul class="d-flex flex-wrap gap-1">
        <?php foreach ($tipi_commissario as $commissario) { ?>
        <li>
            <a class="chip chip-simple" href="<?php echo esc_url(get_term_link($commissario->term_id)); ?>">
                <span class="chip-label"><?php echo esc_html($commissario->name); ?></span>
            </a>
        </li>
        <?php } ?>
    </ul>
</div>
<?php } ?>
