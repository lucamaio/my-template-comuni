<?php
/** Date dei risultati della ricerca globale della trasparenza. */
$metadata_post_id = (int) $args['post_id'];
$metadata_updated = (int) get_post_modified_time('U', true, $metadata_post_id) > (int) get_post_time('U', true, $metadata_post_id)
    && get_the_modified_date('Y-m-d', $metadata_post_id) !== get_the_date('Y-m-d', $metadata_post_id);
global $dci_at_search_metadata_style_printed;
if (empty($dci_at_search_metadata_style_printed)) {
    $dci_at_search_metadata_style_printed = true;
    ?>
    <style>
        .dci-at-search-dates { display:flex; flex-wrap:wrap; gap:.75rem 1.5rem; margin:0 0 1rem; padding:.75rem 0; border-top:1px solid #e4ebf2; border-bottom:1px solid #e4ebf2; }
        .dci-at-search-dates__item { display:flex; align-items:flex-start; gap:.5rem; min-width:0; max-width:100%; }
        .dci-at-search-dates__item > div { min-width:0; overflow-wrap:anywhere; white-space:normal; }
        .dci-at-search-dates__icon { flex:0 0 auto; width:1rem; height:1rem; margin-top:.15rem; fill:currentColor; color:#5c6f82; }
        .dci-at-search-dates__label { display:block; color:#5c6f82; font-size:.78rem; font-weight:400; line-height:1.4; margin-bottom:.15rem; }
        .dci-at-search-dates__value { display:block; color:#334e68; font-size:.95rem; font-weight:600; line-height:1.4; }
        @media (max-width:575.98px) { .dci-at-search-dates { gap:.75rem 1rem; } .dci-at-search-dates__item { flex-basis:100%; } }
    </style>
<?php } ?>
<div class="dci-at-search-dates">
    <div class="dci-at-search-dates__item">
        <svg class="icon dci-at-search-dates__icon" aria-hidden="true"><use href="#it-calendar"></use></svg>
        <div>
            <span class="dci-at-search-dates__label">Pubblicazione</span>
            <time class="dci-at-search-dates__value" datetime="<?php echo esc_attr(get_the_date('Y-m-d', $metadata_post_id)); ?>"><?php echo esc_html(get_the_date('j F Y', $metadata_post_id)); ?></time>
        </div>
    </div>
    <?php if ($metadata_updated) { ?>
        <div class="dci-at-search-dates__item">
            <svg class="icon dci-at-search-dates__icon" aria-hidden="true"><use href="#it-clock"></use></svg>
            <div>
                <span class="dci-at-search-dates__label">Ultimo aggiornamento</span>
                <time class="dci-at-search-dates__value" datetime="<?php echo esc_attr(get_the_modified_date('Y-m-d', $metadata_post_id)); ?>"><?php echo esc_html(get_the_modified_date('j F Y', $metadata_post_id)); ?></time>
            </div>
        </div>
    <?php } ?>
</div>
