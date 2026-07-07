<?php
/**
 * Template single - Incarico dirigenziale.
 *
 * @package Design_Comuni_Italia
 */

global $inline;

get_header();
?>

<main>
<?php
while (have_posts()) :
    the_post();

    $id = get_the_ID();
    $prefix = '_dci_incarico_dirigenziale_';
    $empty_value = '-';

    $name = trim(
        (string) get_post_meta($id, $prefix . 'nome_titolare', true)
        . ' '
        . (string) get_post_meta($id, $prefix . 'cognome_titolare', true)
    );
    $position = trim((string) get_post_meta($id, $prefix . 'mansione_titolare', true));
    $structure = trim((string) get_post_meta($id, $prefix . 'struttura', true));
    $section = (string) get_post_meta($id, $prefix . 'sezione_pubblicazione', true);
    $status = (string) get_post_meta($id, $prefix . 'tipo_stato_incarico_dirigenziale', true);
    $free = 'si' === (string) get_post_meta($id, $prefix . 'gratuito', true);
    $compensation = trim((string) get_post_meta($id, $prefix . 'compenso', true));
    $duration = trim((string) get_post_meta($id, $prefix . 'durata', true));
    $start_date = get_post_meta($id, $prefix . 'data_conferimento', true);
    $end_date = get_post_meta($id, $prefix . 'data_scadenza', true);
    $curriculum = get_post_meta($id, $prefix . 'curriculum', true);
    $attachments = get_post_meta($id, $prefix . 'allegati', true);
    $additional_attachments = get_post_meta($id, $prefix . 'allegati_aggiuntivi', true);
    $more_info = get_post_meta($id, $prefix . 'more_info', true);

    $published_date = get_the_date('j F Y', $id);
    $updated_date = get_the_modified_date('j F Y', $id);

    $status_labels = array(
        'in_corso' => __('In corso', 'design_comuni_italia'),
        'cessato'  => __('Cessato', 'design_comuni_italia'),
        'revocato' => __('Revocato', 'design_comuni_italia'),
        'concluso' => __('Concluso', 'design_comuni_italia'),
    );
    $section_labels = function_exists('dci_incarico_dirigenziale_sections')
        ? dci_incarico_dirigenziale_sections()
        : array();

    $display_value = static function ($value) use ($empty_value) {
        if (is_array($value) || is_object($value)) {
            return $empty_value;
        }

        $value = trim(wp_strip_all_tags((string) $value));

        if (
            $value === ''
            || preg_match('/^null+$/i', $value)
            || strcasecmp($value, 'Non specificato') === 0
        ) {
            return $empty_value;
        }

        return $value;
    };

    $format_date = static function ($value) use ($empty_value) {
        if ($value === '' || $value === null) {
            return $empty_value;
        }
        if (is_numeric($value)) {
            return date_i18n('d/m/Y', (int) $value);
        }
        $timestamp = strtotime((string) $value);
        return $timestamp ? date_i18n('d/m/Y', $timestamp) : (string) $value;
    };

    $get_file_url = static function ($value, $fallback_id = 0) {
        if (is_array($value)) {
            if (!empty($value['id'])) {
                return (string) wp_get_attachment_url((int) $value['id']);
            }
            if (!empty($value['url'])) {
                return (string) $value['url'];
            }
        } elseif (is_numeric($value)) {
            return (string) wp_get_attachment_url((int) $value);
        } elseif (is_string($value) && $value !== '') {
            return $value;
        }

        return $fallback_id > 0 ? (string) wp_get_attachment_url($fallback_id) : '';
    };

    $normalize_file_list = static function ($files, $default_label, $get_file_url) {
        $items = array();

        if (empty($files)) {
            return $items;
        }

        if (!is_array($files)) {
            $files = array($files);
        }

        $file_number = 0;
        foreach ($files as $key => $file) {
            $file_url = '';
            $attachment_id = 0;

            if (is_array($file)) {
                $attachment_id = !empty($file['id']) ? (int) $file['id'] : 0;
                $file_url = $get_file_url($file);
            } elseif (is_numeric($file)) {
                $attachment_id = (int) $file;
                $file_url = $get_file_url($file);
            } elseif (filter_var($key, FILTER_VALIDATE_URL)) {
                $file_url = (string) $key;
                $attachment_id = is_numeric($file) ? (int) $file : 0;
            } elseif (is_string($file)) {
                $file_url = $file;
            }

            if ($file_url === '') {
                continue;
            }

            $file_number++;
            $file_title = $attachment_id > 0 ? get_the_title($attachment_id) : '';
            if ($file_title === '') {
                $file_title = sprintf('%s %d', $default_label, $file_number);
            }

            $items[] = array(
                'url'   => $file_url,
                'title' => $file_title,
            );
        }

        return $items;
    };

    $curriculum_id = (int) get_post_meta($id, $prefix . 'curriculum_id', true);
    $curriculum_url = $get_file_url($curriculum, $curriculum_id);
    $curriculum_documents = $curriculum_url !== ''
        ? array(array('url' => $curriculum_url, 'title' => __('Curriculum', 'design_comuni_italia')))
        : array();
    $assignment_documents = $normalize_file_list($attachments, __('Documento', 'design_comuni_italia'), $get_file_url);
    $extra_documents = $normalize_file_list($additional_attachments, __('Allegato', 'design_comuni_italia'), $get_file_url);
    $has_documents = !empty($curriculum_documents) || !empty($assignment_documents) || !empty($extra_documents);

    $summary_items = array(
        array('label' => __('Sezione di pubblicazione', 'design_comuni_italia'), 'value' => $section_labels[$section] ?? $section),
        array('label' => __('Stato incarico', 'design_comuni_italia'), 'value' => $status_labels[$status] ?? ''),
        array('label' => __('Nome titolare', 'design_comuni_italia'), 'value' => get_post_meta($id, $prefix . 'nome_titolare', true)),
        array('label' => __('Cognome titolare', 'design_comuni_italia'), 'value' => get_post_meta($id, $prefix . 'cognome_titolare', true)),
        array('label' => __('Denominazione incarico', 'design_comuni_italia'), 'value' => $position, 'full' => true),
        array('label' => __('Struttura organizzativa o ufficio', 'design_comuni_italia'), 'value' => $structure, 'full' => true),
        array('label' => __('Data conferimento', 'design_comuni_italia'), 'value' => $format_date($start_date)),
        array('label' => __('Data scadenza', 'design_comuni_italia'), 'value' => $format_date($end_date)),
        array('label' => __('Durata', 'design_comuni_italia'), 'value' => $duration),
        array('label' => __('Compenso lordo annuo', 'design_comuni_italia'), 'value' => $free ? __('Incarico gratuito', 'design_comuni_italia') : $compensation),
        array('label' => __('Data pubblicazione', 'design_comuni_italia'), 'value' => $published_date),
        array('label' => __('Data aggiornamento', 'design_comuni_italia'), 'value' => $updated_date),
    );
    ?>

    <style>
        .dci-dirig-single {
            --dci-dirig-primary: currentColor;
            --dci-dirig-hover: currentColor;
            --dci-dirig-soft: #f7f9fb;
            --dci-dirig-border: #edf0f3;
            --dci-dirig-muted: #5b6f82;
        }
        .dci-dirig-summary {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .dci-dirig-summary__title,
        .dci-dirig-resources__heading,
        .dci-dirig-notes__title {
            margin-bottom: 1rem;
            color: var(--dci-dirig-primary);
            font-size: 1.25rem;
            font-weight: 700;
        }
        .dci-dirig-summary__grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem;
        }
        .dci-dirig-summary__item {
            padding: .85rem 1rem;
            background: var(--dci-dirig-soft);
            border: 1px solid var(--dci-dirig-border);
            border-radius: 10px;
        }
        .dci-dirig-summary__item--full {
            grid-column: 1 / -1;
        }
        .dci-dirig-summary__label {
            display: block;
            margin-bottom: .25rem;
            color: var(--dci-dirig-muted);
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .dci-dirig-summary__value {
            display: block;
            color: var(--dci-dirig-primary);
            font-size: .95rem;
            font-weight: 600;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }
        .dci-dirig-resources {
            margin-bottom: 2rem;
        }
        .dci-dirig-resources__section-title {
            margin: 1.25rem 0 .75rem;
            color: var(--dci-dirig-primary);
            font-size: 1rem;
            font-weight: 700;
        }
        .dci-dirig-resources__grid {
            row-gap: 1rem;
        }
        .dci-dirig-resources__item {
            display: flex;
        }
        .dci-dirig-resources__card {
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            width: 100%;
            min-height: 112px;
            padding: 1.15rem 3.25rem 1.15rem 1.15rem;
            overflow: hidden;
            color: var(--dci-dirig-primary);
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 22px rgba(13, 43, 69, .08);
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .dci-dirig-resources__card::before {
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: currentColor;
            content: "";
        }
        .dci-dirig-resources__card:hover {
            color: var(--dci-dirig-hover);
            background-color: #fbfcfd;
            box-shadow: 0 12px 28px rgba(13, 43, 69, .14);
            text-decoration: none;
            transform: translateY(-2px);
        }
        .dci-dirig-resources__icon {
            flex: 0 0 auto;
            width: 2.4rem;
            height: 2.4rem;
            border: 1px solid #d9e4ee;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f7f9fb;
        }
        .dci-dirig-resources__icon .icon {
            fill: currentColor;
        }
        .dci-dirig-resources__title {
            display: block;
            margin-bottom: .25rem;
            color: inherit;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }
        .dci-dirig-resources__meta {
            display: block;
            color: #5b6f82;
            font-size: .86rem;
            line-height: 1.4;
        }
        .dci-dirig-resources__arrow {
            position: absolute;
            right: 1rem;
            top: 50%;
            width: 1.4rem;
            height: 1.4rem;
            transform: translateY(-50%);
            fill: currentColor;
        }
        .dci-dirig-notes {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        @media (max-width: 767.98px) {
            .dci-dirig-summary__grid {
                grid-template-columns: 1fr;
            }
            .dci-dirig-summary__item--full {
                grid-column: auto;
            }
            .dci-dirig-resources__card {
                padding-right: 1.15rem;
            }
            .dci-dirig-resources__arrow {
                display: none;
            }
        }
    </style>

    <div class="container" id="main-container">
        <div class="row">
            <div class="col px-lg-4">
                <?php get_template_part('template-parts/common/breadcrumb'); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 px-lg-4 py-lg-2">
                <h1 data-audio><?php echo esc_html($name !== '' ? $name : get_the_title()); ?></h1>
                <h2 class="visually-hidden" data-audio><?php esc_html_e('Dettagli incarico dirigenziale', 'design_comuni_italia'); ?></h2>
                <?php if ($position !== '') : ?>
                    <p data-audio><?php echo esc_html($position); ?></p>
                <?php endif; ?>
            </div>

            <div class="col-lg-3 offset-lg-1">
                <?php
                $inline = true;
                get_template_part('template-parts/single/actions');
                ?>
            </div>
        </div>

        <div class="row mt-5 mb-4">
            <div class="col-6">
                <small><?php esc_html_e('Data pubblicazione:', 'design_comuni_italia'); ?></small>
                <p class="fw-semibold font-monospace"><?php echo esc_html($display_value($published_date)); ?></p>
            </div>
            <div class="col-6">
                <small><?php esc_html_e('Data aggiornamento:', 'design_comuni_italia'); ?></small>
                <p class="fw-semibold font-monospace"><?php echo esc_html($display_value($updated_date)); ?></p>
            </div>
        </div>
    </div>

    <div class="container dci-dirig-single t-primary">
        <div class="row border-top border-light row-column-border row-column-menu-left">
            <aside class="col-lg-4">
                <div class="cmp-navscroll sticky-top" aria-labelledby="accordion-title-dirig">
                    <nav class="navbar it-navscroll-wrapper navbar-expand-lg" aria-label="<?php esc_attr_e('Indice della pagina', 'design_comuni_italia'); ?>" data-bs-navscroll>
                        <div class="navbar-custom" id="navbarNavProgress">
                            <div class="menu-wrapper">
                                <div class="link-list-wrapper">
                                    <div class="accordion">
                                        <div class="accordion-item">
                                            <span class="accordion-header" id="accordion-title-dirig">
                                                <button
                                                    class="accordion-button pb-10 px-3 text-uppercase"
                                                    type="button"
                                                    aria-controls="collapse-dirig"
                                                    aria-expanded="true"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#collapse-dirig"
                                                >
                                                    <?php esc_html_e('Indice della pagina', 'design_comuni_italia'); ?>
                                                    <svg class="icon icon-sm icon-primary align-top">
                                                        <use href="#it-expand"></use>
                                                    </svg>
                                                </button>
                                            </span>
                                            <div class="progress">
                                                <div class="progress-bar it-navscroll-progressbar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div id="collapse-dirig" class="accordion-collapse collapse show" role="region" aria-labelledby="accordion-title-dirig">
                                                <div class="accordion-body">
                                                    <ul class="link-list" data-element="page-index">
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="#dati">
                                                                <span class="title-medium"><?php esc_html_e('Dati dell incarico', 'design_comuni_italia'); ?></span>
                                                            </a>
                                                        </li>
                                                        <?php if ($has_documents) : ?>
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#documenti">
                                                                    <span class="title-medium"><?php esc_html_e('Documenti', 'design_comuni_italia'); ?></span>
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <?php if ($display_value($more_info) !== $empty_value) : ?>
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#note">
                                                                    <span class="title-medium"><?php esc_html_e('Note', 'design_comuni_italia'); ?></span>
                                                                </a>
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
                    </nav>
                </div>
            </aside>

            <section class="col-lg-8 it-page-sections-container border-light mb-5">
                <article class="it-page-section anchor-offset mt-5 dci-dirig-summary" aria-labelledby="dati">
                    <h4 class="dci-dirig-summary__title t-primary" id="dati">
                        <?php esc_html_e('Dati dell incarico', 'design_comuni_italia'); ?>
                    </h4>
                    <div class="dci-dirig-summary__grid">
                        <?php foreach ($summary_items as $item) : ?>
                            <div class="dci-dirig-summary__item <?php echo !empty($item['full']) ? 'dci-dirig-summary__item--full' : ''; ?>">
                                <span class="dci-dirig-summary__label"><?php echo esc_html($item['label']); ?></span>
                                <span class="dci-dirig-summary__value">
                                    <?php echo nl2br(esc_html($display_value($item['value']))); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>

                <?php if ($has_documents) : ?>
                    <article class="it-page-section anchor-offset dci-dirig-resources" aria-labelledby="documenti">
                        <h4 class="dci-dirig-resources__heading t-primary" id="documenti">
                            <?php esc_html_e('Documenti', 'design_comuni_italia'); ?>
                        </h4>

                        <?php
                        $document_groups = array(
                            __('Curriculum', 'design_comuni_italia') => $curriculum_documents,
                            __('Atti e documenti relativi all incarico', 'design_comuni_italia') => $assignment_documents,
                            __('Allegati aggiuntivi', 'design_comuni_italia') => $extra_documents,
                        );
                        foreach ($document_groups as $group_title => $documents) :
                            if (empty($documents)) {
                                continue;
                            }
                            ?>
                            <h5 class="dci-dirig-resources__section-title t-primary"><?php echo esc_html($group_title); ?></h5>
                            <div class="row dci-dirig-resources__grid">
                                <?php foreach ($documents as $document) : ?>
                                    <div class="col-12 col-md-6 dci-dirig-resources__item">
                                        <a class="dci-dirig-resources__card t-primary" href="<?php echo esc_url($document['url']); ?>" target="_blank" rel="noopener">
                                            <span class="dci-dirig-resources__icon" aria-hidden="true">
                                                <svg class="icon icon-sm icon-primary"><use href="#it-file"></use></svg>
                                            </span>
                                            <span>
                                                <span class="dci-dirig-resources__title card-title"><?php echo esc_html($document['title']); ?></span>
                                                <span class="dci-dirig-resources__meta"><?php esc_html_e('Apri documento', 'design_comuni_italia'); ?></span>
                                            </span>
                                            <svg class="icon icon-primary dci-dirig-resources__arrow" aria-hidden="true">
                                                <use href="#it-external-link"></use>
                                            </svg>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </article>
                <?php endif; ?>

                <?php if ($display_value($more_info) !== $empty_value) : ?>
                    <article class="it-page-section anchor-offset dci-dirig-notes" aria-labelledby="note">
                        <h4 class="dci-dirig-notes__title t-primary" id="note">
                            <?php esc_html_e('Note e informazioni aggiuntive', 'design_comuni_italia'); ?>
                        </h4>
                        <div class="richtext-wrapper lora">
                            <?php echo wp_kses_post(wpautop($more_info)); ?>
                        </div>
                    </article>
                <?php endif; ?>
            </section>
        </div>
    </div>

<?php
endwhile;
?>
</main>

<?php
get_footer();
