<?php
/**
 * Template Name: Bilanci
 * @package Design_Comuni_Italia
 */

// Difesa anche in caso di inclusione diretta del template da altro codice.
if (!defined('ABSPATH')) {
    exit;
}
if ('true' !== dci_get_option('ck_abilita_trasparenza')) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    nocache_headers();
    get_template_part('404');
    return;
}

// Ritorno alla sezione interna della Trasparenza, indipendente dal provider.
$bilanci_return_term = get_term_by('slug', 'bilanci', 'tipi_cat_amm_trasp');
$bilanci_return_url = $bilanci_return_term ? get_term_link($bilanci_return_term) : '';
$bilanci_has_return_section = !is_wp_error($bilanci_return_url) && !empty($bilanci_return_url);
if (!$bilanci_has_return_section) {
    $bilanci_return_url = dci_bilanci_find_page('page-templates/amministrazione-trasparente.php');
}


$bilanci_view = dci_bilanci_view();
if ($bilanci_view && $bilanci_view['state']['year'] === '') {
    usort($bilanci_view['data']['rows'], function ($a, $b) {
        return (int) $b['year'] <=> (int) $a['year'];
    });
}
// Il ritorno segue la gerarchia anche quando i dati non sono disponibili.
$bilanci_back_url = $bilanci_return_url;
$bilanci_back_label = __('Torna alla sezione Bilanci', 'design_comuni_italia');
$bilanci_back_source = dci_bilanci_source(dci_get_option('url_bilanci', 'trasparenza'));
$bilanci_back_state = $bilanci_back_source ? dci_bilanci_request_state($bilanci_back_source) : false;
if ($bilanci_back_state && $bilanci_back_state['year'] !== '') {
    $bilanci_back_url = $bilanci_back_state['type'] !== ''
        ? dci_bilanci_local_url($bilanci_back_source, $bilanci_back_state['year'])
        : get_permalink(get_queried_object_id());
    $bilanci_back_label = __('Indietro', 'design_comuni_italia');
} elseif (!$bilanci_has_return_section) {
    $bilanci_back_label = __('Torna all’Amministrazione Trasparente', 'design_comuni_italia');
}
$bilanci_has_documents = $bilanci_view && in_array('document', array_column($bilanci_view['data']['rows'], 'kind'), true);
get_header();
?>
<main id="main-container" class="dci-bilanci dci-at-wrap">
    <div class="container py-4 py-lg-4">
        <div id="bilanci-breadcrumb"><?php get_template_part('template-parts/common/breadcrumb'); ?></div>
        <h1 class="title-xxxlarge mb-3" data-element="page-name"><?php esc_html_e('Bilanci', 'design_comuni_italia'); ?></h1>
        <div class="dci-bilanci__intro text-paragraph">
            <p><?php esc_html_e('Consulta i bilanci di previsione e i rendiconti di ciascun anno, con i relativi documenti e allegati.', 'design_comuni_italia'); ?></p>
            <p><?php esc_html_e('I dati sono pubblicati in forma sintetica, aggregata e semplificata per aiutare i cittadini a comprendere come vengono programmate e utilizzate le risorse pubbliche.', 'design_comuni_italia'); ?></p>
            <p class="mb-2"><strong><?php esc_html_e('Riferimento normativo:', 'design_comuni_italia'); ?></strong> <?php esc_html_e('articolo 29, comma 1, del decreto legislativo 14 marzo 2013, n. 33.', 'design_comuni_italia'); ?></p>
            <p class="mb-0"><a class="text-decoration-underline" href="https://www.gazzettaufficiale.it/atto/serie_generale/caricaDettaglioAtto/originario?atto.dataPubblicazioneGazzetta=2013-04-05&amp;atto.codiceRedazionale=13G00076&amp;elenco30giorni=true"><?php esc_html_e('Consulta il decreto nella Gazzetta Ufficiale', 'design_comuni_italia'); ?></a></p>
        </div>

    </div>
    <div class="bg-grey-card dci-at-layout">
        <div class="container py-4 py-lg-5">
        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <section id="bilanci-content" data-bilanci-ready="<?php echo $bilanci_view ? 'true' : 'false'; ?>" class="dci-bilanci__service p-3 p-lg-4" aria-labelledby="bilanci-service-title">
                    <h2 id="bilanci-service-title" class="h4" tabindex="-1"><?php echo esc_html($bilanci_view ? $bilanci_view['data']['title'] : __('Consultazione dei bilanci', 'design_comuni_italia')); ?></h2>
                    <?php if ($bilanci_view) : ?>
                        <?php if ($bilanci_view['stale']) : ?>
                            <div class="dci-bilanci__notice" role="note">
                            <svg class="icon dci-bilanci__notice-icon" aria-hidden="true" focusable="false"><use href="#it-clock"></use></svg>
                            <div class="dci-bilanci__notice-body">
                                <h3 class="dci-bilanci__notice-title"><?php esc_html_e('Aggiornamento temporaneamente non disponibile', 'design_comuni_italia'); ?></h3>
                                <p><?php esc_html_e('Puoi continuare a consultare gli ultimi dati disponibili. Per verificare eventuali aggiornamenti, ti invitiamo a tornare più tardi.', 'design_comuni_italia'); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($bilanci_view['data']['rows']) : ?>
                            <div class="table-responsive dci-bilanci__table" role="region" aria-label="<?php esc_attr_e('Elenco bilanci e documenti', 'design_comuni_italia'); ?>" tabindex="0">
                                <table class="table<?php echo $bilanci_has_documents ? ' dci-bilanci__documents' : ''; ?>">
                                    <caption class="visually-hidden"><?php echo esc_html($bilanci_view['data']['title']); ?></caption>
                                    <thead><tr>
                                        <th scope="col"><?php esc_html_e('Voce', 'design_comuni_italia'); ?></th>
                                        <?php if ($bilanci_has_documents) : ?>
                                            <th scope="col"><?php esc_html_e('Formato', 'design_comuni_italia'); ?></th>
                                        <?php endif; ?>
                                    </tr></thead>
                                    <tbody>
                                    <?php foreach ($bilanci_view['data']['rows'] as $bilanci_row) : ?>
                                        <tr>
                                            <th scope="row">
                                                <div class="dci-bilanci__entry">
                                                    <svg class="icon dci-bilanci__entry-icon" aria-hidden="true" focusable="false">
                                                        <use href="<?php echo esc_attr($bilanci_row['kind'] === 'document' ? '#it-file' : ($bilanci_row['type'] === '' ? '#it-calendar' : '#it-folder')); ?>"></use>
                                                    </svg>
                                                    <span class="dci-bilanci__entry-label">
                                                <?php if ($bilanci_row['kind'] === 'section') : ?>
                                                    <a data-bilanci-nav href="<?php echo esc_url(dci_bilanci_local_url($bilanci_view['source'], $bilanci_row['year'], $bilanci_row['type'])); ?>"><?php echo esc_html($bilanci_row['label']); ?></a>
                                                <?php else : ?>
                                                    <a href="<?php echo esc_url($bilanci_row['url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($bilanci_row['label']); ?><span class="visually-hidden"><?php esc_html_e(' (documento originale, si apre in una nuova scheda)', 'design_comuni_italia'); ?></span></a>
                                                <?php endif; ?>
                                                    </span>
                                                </div>
                                            </th>
                                            <?php if ($bilanci_has_documents) : ?>
                                                <td><?php if ($bilanci_row['kind'] === 'document') : ?><span class="dci-bilanci__format"><?php echo esc_html(strtolower($bilanci_row['format'])); ?></span><?php endif; ?></td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else : ?>
                            <div class="dci-bilanci__notice" role="note">
                            <svg class="icon dci-bilanci__notice-icon" aria-hidden="true" focusable="false"><use href="#it-info-circle"></use></svg>
                            <div class="dci-bilanci__notice-body">
                                <h3 class="dci-bilanci__notice-title"><?php esc_html_e('Nessun documento disponibile', 'design_comuni_italia'); ?></h3>
                                <p><?php esc_html_e('Al momento non sono presenti documenti da consultare in questa voce. Puoi tornare al livello precedente e scegliere un’altra voce.', 'design_comuni_italia'); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php else : ?>
                        <div class="dci-bilanci__notice" role="note">
                            <svg class="icon dci-bilanci__notice-icon" aria-hidden="true" focusable="false"><use href="#it-info-circle"></use></svg>
                            <div class="dci-bilanci__notice-body">
                                <h3 class="dci-bilanci__notice-title"><?php esc_html_e('Consultazione temporaneamente non disponibile', 'design_comuni_italia'); ?></h3>
                                <p><?php esc_html_e('Al momento non è possibile visualizzare i bilanci. Ti invitiamo a riprovare più tardi. Puoi continuare a consultare le altre sezioni dell’Amministrazione Trasparente.', 'design_comuni_italia'); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($bilanci_back_url) : ?>
                        <a data-bilanci-nav class="btn btn-outline-primary dci-bilanci__back mt-4" href="<?php echo esc_url($bilanci_back_url); ?>">
                            <svg class="icon icon-sm" aria-hidden="true" focusable="false"><use href="#it-arrow-left"></use></svg>
                            <span><?php echo esc_html($bilanci_back_label); ?></span>
                        </a>
                    <?php endif; ?>
                </section>
                <p id="bilanci-navigation-status" class="dci-bilanci__status" role="status" aria-live="polite" aria-atomic="true"
                    data-loading="<?php esc_attr_e('Caricamento…', 'design_comuni_italia'); ?>"
                    data-loaded="<?php esc_attr_e('Contenuti aggiornati.', 'design_comuni_italia'); ?>"></p>
                
            </div>
            <?php get_template_part('template-parts/amministrazione-trasparente/side-bar', null, array(
                'section_term' => $bilanci_return_term,
                'siti_tematici' => dci_get_option('siti_tematici', 'trasparenza') ?: array(),
                'column_classes' => '',
            )); ?>
        </div>
        </div>
    </div>
</main>
<?php get_footer(); ?>
