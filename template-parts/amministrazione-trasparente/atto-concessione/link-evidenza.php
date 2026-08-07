<?php
$link_evidenza = dci_get_option('atti_concessione_link_evidenza', 'trasparenza', array());

if (!is_array($link_evidenza) || empty($link_evidenza)) {
    return;
}

$link_evidenza = array_filter($link_evidenza, static function ($link) {
    return is_array($link)
        && !empty(trim((string) ($link['titolo'] ?? '')))
        && !empty(trim((string) ($link['url'] ?? '')));
});

if (empty($link_evidenza)) {
    return;
}
?>

<section class="dci-atti-link-evidenza mb-4" aria-labelledby="dci-atti-link-evidenza-title">
    <h3 id="dci-atti-link-evidenza-title" class="h4 mb-3">
        <?php esc_html_e('Collegamenti in evidenza', 'design_comuni_italia'); ?>
    </h3>

    <div class="dci-atti-link-evidenza__list">
        <?php foreach ($link_evidenza as $link) :
            $titolo = isset($link['titolo']) ? trim((string) $link['titolo']) : '';
            $descrizione = isset($link['descrizione']) ? trim((string) $link['descrizione']) : '';
            $url = isset($link['url']) ? trim((string) $link['url']) : '';

            $is_external = !dci_is_internal_portal_url($url);
            ?>
            <article class="card dci-atti-link-evidenza__card t-primary">
                <div class="card-body position-relative">
                    <div class="dci-atti-link-evidenza__icon" aria-hidden="true">
                        <svg class="icon icon-sm">
                            <use href="<?php echo $is_external ? '#it-external-link' : '#it-link'; ?>"></use>
                        </svg>
                    </div>

                    <h4 class="h5 fw-bold mb-2 pe-5"><?php echo esc_html($titolo); ?></h4>

                    <?php if ('' !== $descrizione) : ?>
                        <p class="card-text mb-3 pe-md-5"><?php echo esc_html($descrizione); ?></p>
                    <?php endif; ?>

                    <a class="fw-semibold"
                       href="<?php echo esc_url($url); ?>"
                       <?php if ($is_external) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>
                        <?php esc_html_e('Vai al collegamento', 'design_comuni_italia'); ?>
                        <?php if ($is_external) : ?>
                            <span class="visually-hidden"><?php esc_html_e('(si apre in una nuova scheda)', 'design_comuni_italia'); ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<style>
    .dci-atti-link-evidenza__list {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .dci-atti-link-evidenza__card {
        width: 100%;
        border: 1px solid #d7e2ec !important;
        border-radius: 4px !important;
        background: #fff !important;
        box-shadow: 0 8px 22px rgba(23, 50, 77, .07) !important;
        overflow: hidden;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .dci-atti-link-evidenza__card:hover {
        border-color: #c9d7e5 !important;
        box-shadow: 0 12px 28px rgba(23, 50, 77, .11) !important;
        transform: translateY(-1px);
    }
    .dci-atti-link-evidenza__card .card-body { padding: 1.35rem; }
    .dci-atti-link-evidenza__card a { color: currentColor; }
    .dci-atti-link-evidenza__icon {
        position: absolute;
        top: 1.1rem;
        right: 1.1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        background: rgba(var(--bs-primary-rgb), .1);
    }
    .dci-atti-link-evidenza__icon .icon { fill: currentColor; }
</style>
