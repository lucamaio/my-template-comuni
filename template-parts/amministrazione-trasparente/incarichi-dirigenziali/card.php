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
$show_updated_date = get_post_modified_time('U', true, $post_id) > get_post_time('U', true, $post_id) && get_the_modified_date('Y-m-d', $post_id) !== get_the_date('Y-m-d', $post_id);

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
        $file_title = dci_custom_section_attachment_title(
            $attachment_id,
            $file_url,
            sprintf('%s %d', $default_label, $file_number)
        );

        $items[] = array(
            'url'   => $file_url,
            'title' => $file_title,
        );
    }

    return $items;
};

$curriculum_id = (int) get_post_meta($post_id, $prefix . 'curriculum_id', true);
$curriculum_url = $get_file_url($curriculum, $curriculum_id);
$curriculum_title = dci_custom_section_attachment_title(
    $curriculum_id,
    $curriculum_url,
    __('Curriculum', 'design_comuni_italia')
);
$assignment_documents = $normalize_file_list($attachments, __('Documento', 'design_comuni_italia'), $get_file_url);
$extra_documents = $normalize_file_list($additional_attachments, __('Allegato', 'design_comuni_italia'), $get_file_url);
?>

<article class="dci-dirig-card t-primary"><p class="dci-dirig-card__meta"><svg class="icon" aria-hidden="true"><use href="#it-calendar"></use></svg>
Pubblicato il <time datetime="<?php echo esc_attr(get_the_date('Y-m-d', $post_id)); ?>"><?php echo esc_html($published_date); ?></time>
<?php if ($show_updated_date) { ?><span aria-hidden="true">&ndash;</span> Aggiornato il <time datetime="<?php echo esc_attr(get_the_modified_date('Y-m-d', $post_id)); ?>"><?php echo esc_html($updated_date); ?></time><?php } ?>
</p>
    <header class="dci-dirig-card__header">
        <div class="dci-dirig-card__identity">
            <span class="dci-dirig-card__label">Titolare dell'incarico dirigenziale</span><h3 class="dci-dirig-card__name card-title">
                <a href="<?php the_permalink(); ?>">
                    <?php echo esc_html(dci_custom_section_card_text($name !== '' ? $name : get_the_title(), 95)); ?>
                </a>
            </h3>
        </div>
        <div class="dci-dirig-card__identity-detail">
            <span class="dci-dirig-card__label">Mansione</span>
            <p class="dci-dirig-card__position">
                <?php echo esc_html($position !== '' ? dci_custom_section_card_text($position, 85) : 'Non specificata'); ?>
            </p>
        </div>
        <div class="dci-dirig-card__identity-detail">
            <span class="dci-dirig-card__label">Struttura di appartenenza</span>
            <p class="dci-dirig-card__structure">
                <?php echo esc_html($structure !== '' ? dci_custom_section_card_text($structure, 85) : 'Non specificata'); ?>
            </p>
        </div>
    </header>
    <div class="dci-dirig-card__status-row">
        <span class="dci-dirig-card__label">Stato dell'incarico</span>
        <span class="dci-dirig-card__status dci-dirig-card__status--<?php echo esc_attr($status); ?>">
            <span class="dci-dirig-card__status-dot" aria-hidden="true"></span>
            <?php echo esc_html($status_labels[$status] ?? 'Non specificato'); ?>
        </span>
    </div>

    <dl class="dci-dirig-card__data">
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

    <section class="dci-dirig-card__document-section" aria-label="<?php esc_attr_e("Documenti dell'incarico", 'design_comuni_italia'); ?>">
        <h4 class="visually-hidden">
            <?php esc_html_e('Documenti', 'design_comuni_italia'); ?>
        </h4>

        <div class="dci-dirig-card__document-grid">
            <div class="dci-dirig-card__document-group">
                <h5><?php esc_html_e('Curriculum', 'design_comuni_italia'); ?></h5>
                <?php if ($curriculum_url !== '') : ?>
                    <a href="<?php echo esc_url($curriculum_url); ?>" target="_blank" rel="noopener">
                        <svg class="icon icon-sm" aria-hidden="true"><use href="#it-file"></use></svg>
                        <?php echo esc_html(dci_custom_section_card_text($curriculum_title, 65)); ?>
                    </a>
                <?php else : ?>
                    <p><?php esc_html_e('Nessun curriculum', 'design_comuni_italia'); ?></p>
                <?php endif; ?>
            </div>

            <div class="dci-dirig-card__document-group">
                <h5><?php esc_html_e("Atti e documenti dell'incarico", 'design_comuni_italia'); ?></h5>
                <?php if (!empty($assignment_documents)) : ?>
                    <?php foreach ($assignment_documents as $document) : ?>
                        <a href="<?php echo esc_url($document['url']); ?>" target="_blank" rel="noopener">
                            <svg class="icon icon-sm" aria-hidden="true"><use href="#it-file"></use></svg>
                            <?php echo esc_html(dci_custom_section_card_text($document['title'], 65)); ?>
                        </a>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p><?php esc_html_e('Nessun documento', 'design_comuni_italia'); ?></p>
                <?php endif; ?>
            </div>

            <div class="dci-dirig-card__document-group dci-dirig-card__document-group--wide">
                <h5><?php esc_html_e('Allegati aggiuntivi', 'design_comuni_italia'); ?></h5>
                <?php if (!empty($extra_documents)) : ?>
                    <?php foreach ($extra_documents as $document) : ?>
                        <a href="<?php echo esc_url($document['url']); ?>" target="_blank" rel="noopener">
                            <svg class="icon icon-sm" aria-hidden="true"><use href="#it-file"></use></svg>
                            <?php echo esc_html(dci_custom_section_card_text($document['title'], 65)); ?>
                        </a>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p><?php esc_html_e('Nessun allegato aggiuntivo', 'design_comuni_italia'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer class="dci-dirig-card__footer dci-at-card-actions">
        <a class="dci-dirig-card__detail dci-at-card-detail-action btn btn-primary btn-sm" href="<?php the_permalink(); ?>">
            <span><?php esc_html_e('Apri dettaglio', 'design_comuni_italia'); ?></span>
            <svg class="icon icon-sm ms-1" aria-hidden="true" focusable="false"><use href="#it-arrow-right"></use></svg>
        </a>
        <?php
        if (function_exists('dci_render_trasparenza_edit_link')) {
            dci_render_trasparenza_edit_link($post_id);
        }
        ?>
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
            margin-bottom: 1.5rem;
            padding: 1.5rem;
            border: 1px solid var(--dci-dirig-border);
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(23, 50, 77, .08);
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
            font-size: 1.25rem;
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
            border-radius: 8px;
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
            border-radius: 8px;
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
    .dci-dirig-card { border-radius:12px; border-color:#d9e2ec; }
.dci-dirig-card__meta { display:flex; flex-wrap:wrap; align-items:center; gap:.35rem; margin:0 0 1.25rem; padding-bottom:1rem; border-bottom:1px solid #455a64; color:#455a64; font-weight:600; }
.dci-dirig-card__meta .icon { width:1rem; height:1rem; fill:currentColor; }
.dci-dirig-card__label, .dci-dirig-card__data dt, .dci-dirig-card__document-group h5 { display:block; margin-bottom:.3rem; color:#4f6173; font-size:.75rem; font-weight:700; letter-spacing:.035em; text-transform:uppercase; }
.dci-dirig-card__identity { padding:1.15rem; border:1px solid #e1e7ed; border-radius:8px; background:#f3f5f7; flex:1; overflow-wrap:anywhere; }
.dci-dirig-card__data { grid-template-columns:repeat(4,minmax(0,1fr)); gap:0; padding:0; border:1px solid #e1e8ef; border-radius:8px; overflow:hidden; }
.dci-dirig-card__data > div { padding:1rem; min-width:0; overflow-wrap:anywhere; }
.dci-dirig-card__data > div + div { border-left:1px solid #e1e8ef; }
.dci-dirig-card__data dd { font-weight:400; color:#263b4d; line-height:1.55; }
.dci-dirig-card__document-group { background:#fff; padding:1rem; }
.dci-dirig-card__summary { padding:1.15rem; background:#f3f5f7; border:1px solid #e1e7ed; border-radius:8px; overflow-wrap:anywhere; }
.dci-dirig-card__footer { flex-wrap:wrap; gap:.65rem; }
@media (min-width:768px) and (max-width:991.98px) {
.dci-dirig-card__data { grid-template-columns:repeat(2,minmax(0,1fr)); }
.dci-dirig-card__data > div:nth-child(n+3) { border-top:1px solid #e1e8ef; }
.dci-dirig-card__data > div:nth-child(3) { border-left:0; }
}
@media (max-width:767.98px) {
.dci-dirig-card__data, .dci-dirig-card__document-grid { grid-template-columns:1fr; }
.dci-dirig-card__data > div + div { border-left:0; border-top:1px solid #e1e8ef; }
}
.dci-dirig-card__header { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.75rem; }
.dci-dirig-card__identity { grid-column:1 / -1; }
.dci-dirig-card__identity-detail { min-width:0; padding:1.15rem; border:1px solid #e1e7ed; border-radius:8px; background:#f3f5f7; overflow-wrap:anywhere; }
.dci-dirig-card__position, .dci-dirig-card__structure { margin:0; color:#263b4d; font-weight:400; line-height:1.55; }
.dci-dirig-card__status-row { display:flex; flex-wrap:wrap; align-items:center; gap:.75rem; margin-top:1.25rem; }
.dci-dirig-card__status-row .dci-dirig-card__label { margin:0; }
.dci-dirig-card__status { display:inline-flex; align-items:center; gap:.45rem; margin:0; padding:.4rem .8rem; border-radius:999px; line-height:1.4; }
.dci-dirig-card__status-dot { width:.5rem; height:.5rem; border-radius:50%; background:currentColor; }
.dci-dirig-card__status--cessato, .dci-dirig-card__status--concluso { color:#455a64; border-color:#c7d2da; background:#eef2f5; }
@media (max-width:575.98px) { .dci-dirig-card__header { grid-template-columns:1fr; } }
.dci-dirig-card__document-section { margin-top:1.25rem; padding-top:1.25rem; }
.dci-dirig-card__document-grid { grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem; }
.dci-dirig-card__document-group { padding:1rem; border:1px solid #e4ebf2; border-radius:6px; background:#fff; }
.dci-dirig-card__document-group--wide { grid-column:1 / -1; }
.dci-dirig-card__document-group a { gap:.4rem; margin:.4rem 0 0; color:currentColor; font-weight:400; line-height:1.55; }
.dci-dirig-card__document-group a:first-of-type { margin-top:.4rem; }
.dci-dirig-card__document-group a:hover, .dci-dirig-card__document-group a:focus-visible { text-decoration:underline; }
.dci-dirig-card__document-group p { color:#263b4d; font-weight:400; line-height:1.55; }
.dci-dirig-card__document-group .icon { fill:currentColor; }
@media (max-width:767.98px) { .dci-dirig-card__document-grid { grid-template-columns:1fr; } }
</style>
<?php endif; ?>
