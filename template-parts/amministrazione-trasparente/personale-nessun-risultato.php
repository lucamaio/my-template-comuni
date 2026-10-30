<?php
/** Avviso per l'elenco vuoto: distingue la ricerca dalla sezione senza filtri. */
$empty_has_filters = !empty($args['has_filters']);
$empty_page_out_of_range = (int) ($args['paged'] ?? 1) > 1;
?>
<div class="dci-at-empty text-decoration-none" role="status" aria-live="polite">
    <span class="dci-at-empty__icon" aria-hidden="true">
        <svg class="icon icon-sm"><use href="#it-info-circle"></use></svg>
    </span>
    <div class="dci-at-empty__content">
        <?php if ($empty_has_filters || $empty_page_out_of_range) { ?>
            <p class="dci-at-empty__text text-decoration-none mb-0">Nessun elemento trovato con i criteri di ricerca selezionati.</p>
        <?php } else { ?>
            <?php get_template_part('template-parts/amministrazione-trasparente/informativa-durata', null, ['post_type' => $args['post_type'] ?? '']); ?>
        <?php } ?>
    </div>
</div>
