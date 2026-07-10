<?php
/**
 * Card frontend per la tipologia "Incarico dirigenziale".
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once get_template_directory() . '/template-parts/amministrazione-trasparente/custom-section-card-helpers.php';

$post_id = get_the_ID();
$prefix = '_dci_incarico_dirigenziale_';
$empty_value = '-';

$name = trim(
    (string) get_post_meta($post_id, $prefix . 'nome_titolare', true)
    . ' '
    . (string) get_post_meta($post_id, $prefix . 'cognome_titolare', true)
);
$position = trim((string) get_post_meta($post_id, $prefix . 'mansione_titolare', true));
$structure = trim((string) get_post_meta($post_id, $prefix . 'struttura', true));
$status = (string) get_post_meta($post_id, $prefix . 'tipo_stato_incarico_dirigenziale', true);
$free = 'si' === (string) get_post_meta($post_id, $prefix . 'gratuito', true);
$compensation = trim((string) get_post_meta($post_id, $prefix . 'compenso', true));
$duration = trim((string) get_post_meta($post_id, $prefix . 'durata', true));
$start_date = get_post_meta($post_id, $prefix . 'data_conferimento', true);
$end_date = get_post_meta($post_id, $prefix . 'data_scadenza', true);
$curriculum = get_post_meta($post_id, $prefix . 'curriculum', true);
$attachments = get_post_meta($post_id, $prefix . 'allegati', true);
$additional_attachments = get_post_meta($post_id, $prefix . 'allegati_aggiuntivi', true);
$more_info = trim(wp_strip_all_tags((string) get_post_meta($post_id, $prefix . 'more_info', true)));
$published_date = get_the_date('j F Y', $post_id);
$updated_date = get_the_modified_date('j F Y', $post_id);

$status_labels = array(
    'in_corso' => __('In corso', 'design_comuni_italia'),
    'cessato'  => __('Cessato', 'design_comuni_italia'),
    'revocato' => __('Revocato', 'design_comuni_italia'),
    'concluso' => __('Concluso', 'design_comuni_italia'),
);

$format_date = static function ($value) use ($empty_value) {
    if ($value === '' || $value === null) {
        return $empty_value;
    }
    if (is_numeric($value)) {
        return date_i18n('j F Y', (int) $value);
    }
    $timestamp = strtotime((string) $value);
    return $timestamp ? date_i18n('j F Y', $timestamp) : (string) $value;
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
            $file_title = sprintf(
                '%s %d',
                $default_label,
                $file_number
            );
        }

        $items[] = array(
            'url'   => $file_url,
            'title' => $file_title,
        );
    }

    return $items;
};

$curriculum_id = (int) get_post_meta($post_id, $prefix . 'curriculum_id', true);
$curriculum_url = $get_file_url($curriculum, $curriculum_id);
$assignment_documents = $normalize_file_list($attachments, __('Documento', 'design_comuni_italia'), $get_file_url);
$extra_documents = $normalize_file_list($additional_attachments, __('Allegato', 'design_comuni_italia'), $get_file_url);
?>

<article class="dci-dirig-card t-primary">
    <header class="dci-dirig-card__header">
        <div class="dci-dirig-card__identity">
            <h3 class="dci-dirig-card__name card-title">
                <a href="<?php the_permalink(); ?>">
                    <?php echo esc_html(dci_custom_section_card_text($name !== '' ? $name : get_the_title(), 95)); ?>
                </a>
            </h3>
            <p class="dci-dirig-card__position" aria-label="Mansione">
                <?php echo esc_html(dci_custom_section_card_text($position, 85)); ?>
            </p>
            <p class="dci-dirig-card__structure" aria-label="Struttura">
                <?php echo esc_html(dci_custom_section_card_text($structure, 85)); ?>
            </p>
        </div>

        <span class="dci-dirig-card__status dci-dirig-card__status--<?php echo esc_attr($status); ?>">
            <?php echo esc_html($status_labels[$status] ?? $empty_value); ?>
        </span>
    </header>

    <dl class="dci-dirig-card__data">
        <div>
            <dt><?php esc_html_e('Data pubblicazione', 'design_comuni_italia'); ?></dt>
            <dd><?php echo esc_html(dci_custom_section_card_text($published_date, 35)); ?></dd>
        </div>
        <!-- <div>
            <dt><?php // esc_html_e('Data aggiornamento', 'design_comuni_italia'); ?></dt>
            <dd><?php // echo esc_html(dci_custom_section_card_text($updated_date, 35)); ?></dd>
        </div> -->
        <div>
            <dt><?php esc_html_e('Conferimento', 'design_comuni_italia'); ?></dt>
            <dd><?php echo esc_html($format_date($start_date)); ?></dd>
        </div>
        <div>
            <dt><?php esc_html_e('Scadenza', 'design_comuni_italia'); ?></dt>
            <dd><?php echo esc_html($format_date($end_date)); ?></dd>
        </div>
        <div>
            <dt><?php esc_html_e('Durata', 'design_comuni_italia'); ?></dt>
            <dd><?php echo esc_html(dci_custom_section_card_text($duration, 45)); ?></dd>
        </div>
        <div>
            <dt><?php esc_html_e('Compenso', 'design_comuni_italia'); ?></dt>
            <dd>
                <?php
                echo $free
                    ? esc_html__('Incarico gratuito', 'design_comuni_italia')
                    : esc_html(dci_custom_section_card_text($compensation, 45));
                ?>
            </dd>
        </div>
    </dl>

    <?php if ($more_info !== '') : ?>
        <p class="dci-dirig-card__summary">
            <?php echo esc_html(dci_custom_section_card_text($more_info, 150)); ?>
        </p>
    <?php endif; ?>

    <section class="dci-dirig-card__document-section" aria-label="<?php esc_attr_e('Documenti dell incarico', 'design_comuni_italia'); ?>">
        <h4 class="dci-dirig-card__document-title t-primary">
            <?php esc_html_e('Documenti', 'design_comuni_italia'); ?>
        </h4>

        <div class="dci-dirig-card__document-grid">
            <div class="dci-dirig-card__document-group">
                <h5><?php esc_html_e('Curriculum', 'design_comuni_italia'); ?></h5>
                <?php if ($curriculum_url !== '') : ?>
                    <a href="<?php echo esc_url($curriculum_url); ?>" target="_blank" rel="noopener">
                        <svg class="icon icon-sm icon-primary" aria-hidden="true"><use href="#it-file"></use></svg>
                        <?php esc_html_e('Curriculum', 'design_comuni_italia'); ?>
                    </a>
                <?php else : ?>
                    <p><?php echo esc_html($empty_value); ?></p>
                <?php endif; ?>
            </div>

            <div class="dci-dirig-card__document-group">
                <h5><?php esc_html_e('Atti e documenti relativi all incarico', 'design_comuni_italia'); ?></h5>
                <?php if (!empty($assignment_documents)) : ?>
                    <?php foreach ($assignment_documents as $document) : ?>
                        <a href="<?php echo esc_url($document['url']); ?>" target="_blank" rel="noopener">
                            <svg class="icon icon-sm icon-primary" aria-hidden="true"><use href="#it-file"></use></svg>
                            <?php echo esc_html(dci_custom_section_card_text($document['title'], 65)); ?>
                        </a>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p><?php echo esc_html($empty_value); ?></p>
                <?php endif; ?>
            </div>

            <div class="dci-dirig-card__document-group">
                <h5><?php esc_html_e('Allegati aggiuntivi', 'design_comuni_italia'); ?></h5>
                <?php if (!empty($extra_documents)) : ?>
                    <?php foreach ($extra_documents as $document) : ?>
                        <a href="<?php echo esc_url($document['url']); ?>" target="_blank" rel="noopener">
                            <svg class="icon icon-sm icon-primary" aria-hidden="true"><use href="#it-file"></use></svg>
                            <?php echo esc_html(dci_custom_section_card_text($document['title'], 65)); ?>
                        </a>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p><?php echo esc_html($empty_value); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer class="dci-dirig-card__footer">
        <a class="dci-dirig-card__detail btn btn-primary" href="<?php the_permalink(); ?>">
            <?php esc_html_e('Consulta la scheda', 'design_comuni_italia'); ?>
        </a>
    </footer>
</article>

<?php
global $dci_incarico_dirigenziale_card_style_printed;
if (empty($dci_incarico_dirigenziale_card_style_printed)) :
    $dci_incarico_dirigenziale_card_style_printed = true;
    ?>
    <style>
        .dci-dirig-card {
            --dci-dirig-primary: var(--tema-primary, var(--bs-primary, #06c));
            --dci-dirig-primary-dark: var(--tema-primary-dark, var(--bs-primary, #004080));
            --dci-dirig-border: #d7e2ec;
            --dci-dirig-muted: #5c6f82;
            --dci-dirig-soft: #f7f9fb;
            margin-bottom: 1.25rem;
            padding: 1.35rem;
            border: 1px solid var(--dci-dirig-border);
            border-radius: 4px;
            background: #fff;
            box-shadow: 0 8px 22px rgba(23, 50, 77, .07);
            color: #17324d;
        }
        .dci-dirig-card__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
        }
        .dci-dirig-card__identity { min-width: 0; }
        .dci-dirig-card__name {
            margin: 0;
            font-size: 1.3rem;
            line-height: 1.35;
        }
        .dci-dirig-card__name a {
            color: var(--dci-dirig-primary-dark);
            text-decoration: none;
        }
        .dci-dirig-card__name a:hover { text-decoration: underline; }
        .dci-dirig-card__position {
            margin: .35rem 0 0;
            font-weight: 700;
        }
        .dci-dirig-card__structure {
            margin: .2rem 0 0;
            color: #455a64;
        }
        .dci-dirig-card__status {
            flex: 0 0 auto;
            padding: .25rem .55rem;
            border: 1px solid var(--dci-dirig-border);
            background: #f1f3f5;
            color: #33485c;
            font-size: .82rem;
            font-weight: 700;
        }
        .dci-dirig-card__status--in_corso {
            border-color: #008758;
            background: #e8f7f0;
            color: #006b47;
        }
        .dci-dirig-card__status--revocato {
            border-color: #d9364f;
            background: #fff1f2;
            color: #a61b31;
        }
        .dci-dirig-card__data {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
            margin: 1.2rem 0 0;
            padding-top: 1rem;
            border-top: 1px solid #e4ebf2;
        }
        .dci-dirig-card__data dt {
            margin-bottom: .2rem;
            color: var(--dci-dirig-muted);
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .dci-dirig-card__data dd {
            margin: 0;
            font-weight: 600;
        }
        .dci-dirig-card__summary {
            margin: 1rem 0 0;
        }
        .dci-dirig-card__document-section {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e4ebf2;
        }
        .dci-dirig-card__document-title {
            margin: 0 0 .75rem;
            color: var(--dci-dirig-primary-dark);
            font-size: 1.05rem;
            font-weight: 700;
        }
        .dci-dirig-card__document-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
        }
        .dci-dirig-card__document-group {
            min-width: 0;
            padding: .85rem;
            border: 1px solid #e4ebf2;
            border-radius: 4px;
            background: var(--dci-dirig-soft);
        }
        .dci-dirig-card__document-group h5 {
            margin: 0 0 .5rem;
            color: #17324d;
            font-size: .88rem;
            font-weight: 700;
        }
        .dci-dirig-card__document-group a {
            display: flex;
            align-items: flex-start;
            gap: .35rem;
            min-width: 0;
            margin-top: .35rem;
            color: var(--dci-dirig-primary);
            font-weight: 700;
            line-height: 1.35;
            text-decoration: none;
            overflow-wrap: anywhere;
        }
        .dci-dirig-card__document-group a:first-of-type {
            margin-top: 0;
        }
        .dci-dirig-card__document-group a:hover {
            text-decoration: underline;
        }
        .dci-dirig-card__document-group p {
            margin: 0;
            font-weight: 600;
        }
        .dci-dirig-card__document-group .icon {
            flex: 0 0 auto;
            fill: var(--dci-dirig-primary);
            margin-top: .1rem;
        }
        .dci-dirig-card__footer {
            display: flex;
            justify-content: flex-end;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e4ebf2;
        }
        .dci-dirig-card__detail {
            flex: 0 0 auto;
            border-radius: 4px;
            font-weight: 700;
            text-decoration: none;
        }
        @media (max-width: 991.98px) {
            .dci-dirig-card__data,
            .dci-dirig-card__document-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 575.98px) {
            .dci-dirig-card__header {
                display: block;
            }
            .dci-dirig-card__status {
                display: inline-block;
                margin-top: .75rem;
            }
            .dci-dirig-card__data,
            .dci-dirig-card__document-grid {
                grid-template-columns: 1fr;
            }
            .dci-dirig-card__footer {
                display: block;
            }
            .dci-dirig-card__detail {
                display: inline-block;
                width: 100%;
                text-align: center;
            }
        }
    </style>
<?php endif; ?>
