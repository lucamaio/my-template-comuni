<?php
/**
 * Elenco dei componenti degli organi politici per Amministrazione Trasparente.
 *
 * @package Design_Comuni_Italia
 */

$section_name = isset($args['section_name']) ? (string) $args['section_name'] : '';
$section_config = array(
    'Il Sindaco' => array(
        'option' => 'link_sindaco',
        'office_names' => array('Sindaco', 'Ufficio del Sindaco'),
        'office_slugs' => array('sindaco', 'ufficio-del-sindaco'),
        'roles' => array('sindaco'),
    ),
    'Giunta Comunale' => array(
        'option' => 'link_giunta_comunale',
        'office_names' => array('Giunta Comunale', 'Giunta'),
        'office_slugs' => array('giunta-comunale', 'giunta'),
        'roles' => array('assessore', 'vicesindaco', 'vice-sindaco'),
    ),
    'Consiglio Comunale' => array(
        'option' => 'link_consiglio_comunale',
        'office_names' => array('Consiglio Comunale', 'Consiglio'),
        'office_slugs' => array('consiglio-comunale', 'consiglio'),
        'roles' => array(
            'consigliere',
            'consigliere-comunale',
            'presidente-del-consiglio',
            'presidente-del-consiglio-comunale',
            'vicepresidente-del-consiglio',
            'vicepresidente-del-consiglio-comunale',
        ),
    ),
);

if (!isset($section_config[$section_name])) {
    return;
}

$config = $section_config[$section_name];
$office_ids = array();
$configured_office_url = trim((string) dci_get_option($config['option'], 'trasparenza'));

if ($configured_office_url !== '') {
    $configured_office_id = url_to_postid($configured_office_url);
    if ($configured_office_id > 0 && get_post_type($configured_office_id) === 'unita_organizzativa') {
        $office_ids[] = $configured_office_id;
    }
}

if (empty($office_ids)) {
    $fallback_offices = get_posts(array(
        'post_type' => 'unita_organizzativa',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'post_name__in' => $config['office_slugs'],
        'no_found_rows' => true,
    ));
    $office_ids = array_merge($office_ids, $fallback_offices);
}

if (empty($office_ids)) {
    $all_political_offices = get_posts(array(
        'post_type' => 'unita_organizzativa',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'no_found_rows' => true,
    ));

    foreach ($all_political_offices as $office) {
        foreach ($config['office_names'] as $office_name) {
            if (strcasecmp(trim($office->post_title), $office_name) === 0) {
                $office_ids[] = (int) $office->ID;
                break;
            }
        }
    }
}

$office_ids = array_values(array_unique(array_filter(array_map('intval', $office_ids))));
$member_ids = array();

foreach ($office_ids as $office_id) {
    $office_people = array_merge(
        dci_normalize_meta_ids(dci_get_meta('responsabile', '_dci_unita_organizzativa_', $office_id)),
        dci_normalize_meta_ids(dci_get_meta('persone_struttura', '_dci_unita_organizzativa_', $office_id))
    );

    foreach ($office_people as $person_id) {
        $member_ids[$person_id] = true;
    }
}

$published_people = get_posts(array(
    'post_type' => 'persona_pubblica',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'fields' => 'ids',
    'no_found_rows' => true,
));
$assignment_person_map = array();

foreach ($published_people as $person_id) {
    $person_organizations = dci_normalize_meta_ids(
        dci_get_meta('organizzazioni', '_dci_persona_pubblica_', $person_id)
    );
    if (array_intersect($office_ids, $person_organizations)) {
        $member_ids[(int) $person_id] = true;
    }

    foreach (dci_normalize_meta_ids(dci_get_meta('incarichi', '_dci_persona_pubblica_', $person_id)) as $assignment_id) {
        if (!isset($assignment_person_map[$assignment_id])) {
            $assignment_person_map[$assignment_id] = array();
        }
        $assignment_person_map[$assignment_id][] = (int) $person_id;
    }
}

$role_matches = static function ($role_title) use ($section_name, $config) {
    $normalized_role = sanitize_title((string) $role_title);

    if ($section_name === 'Il Sindaco') {
        return in_array($normalized_role, array('sindaco', 'il-sindaco'), true);
    }

    if ($section_name === 'Giunta Comunale') {
        return strpos($normalized_role, 'assessore') !== false
            || in_array($normalized_role, array('vicesindaco', 'vice-sindaco'), true);
    }

    if ($section_name === 'Consiglio Comunale') {
        return strpos($normalized_role, 'consigliere') !== false
            || (
                strpos($normalized_role, 'presidente') !== false
                && strpos($normalized_role, 'consiglio') !== false
            );
    }

    foreach ($config['roles'] as $allowed_role) {
        if ($normalized_role === $allowed_role || strpos($normalized_role, $allowed_role) !== false) {
            return true;
        }
    }

    return false;
};

$assignments = get_posts(array(
    'post_type' => 'incarico',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'no_found_rows' => true,
));
$people_assignments = array();
$all_person_role_slugs = array();

foreach ($assignments as $assignment) {
    $assignment_role_names = array($assignment->post_title);
    $assignment_role_terms = get_the_terms($assignment->ID, 'tipi_incarico');
    if (is_array($assignment_role_terms)) {
        foreach ($assignment_role_terms as $assignment_role_term) {
            $assignment_role_names[] = $assignment_role_term->name;
        }
    }

    $assignment_people = dci_normalize_meta_ids(
        dci_get_meta('persona', '_dci_incarico_', $assignment->ID)
    );
    if (!empty($assignment_person_map[$assignment->ID])) {
        $assignment_people = array_values(array_unique(array_merge(
            $assignment_people,
            $assignment_person_map[$assignment->ID]
        )));
    }

    foreach ($assignment_people as $assignment_person_id) {
        if (!isset($all_person_role_slugs[$assignment_person_id])) {
            $all_person_role_slugs[$assignment_person_id] = array();
        }
        foreach ($assignment_role_names as $assignment_role_name) {
            $all_person_role_slugs[$assignment_person_id][] = sanitize_title((string) $assignment_role_name);
        }
        $all_person_role_slugs[$assignment_person_id] = array_values(array_unique($all_person_role_slugs[$assignment_person_id]));
    }

    $assignment_role_is_valid = false;
    foreach ($assignment_role_names as $assignment_role_name) {
        if ($role_matches($assignment_role_name)) {
            $assignment_role_is_valid = true;
            break;
        }
    }

    if (!$assignment_role_is_valid) {
        continue;
    }

    $assignment_offices = array_merge(
        dci_normalize_meta_ids(dci_get_meta('unita_organizzativa', '_dci_incarico_', $assignment->ID)),
        dci_normalize_meta_ids(dci_get_meta('incarico_unita_organizzative', '_dci_incarico_', $assignment->ID))
    );
    $assignment_matches_office = !empty(array_intersect($office_ids, $assignment_offices));

    foreach ($assignment_people as $person_id) {
        if (!$assignment_matches_office && empty($member_ids[$person_id])) {
            continue;
        }

        $member_ids[$person_id] = true;
        if (!isset($people_assignments[$person_id])) {
            $people_assignments[$person_id] = array();
        }
        $people_assignments[$person_id][] = $assignment->ID;
    }
}

$format_date = static function ($value) {
    if (empty($value)) {
        return '';
    }

    $timestamp = is_numeric($value) ? (int) $value : strtotime((string) $value);
    return $timestamp ? date_i18n('j F Y', $timestamp) : (string) $value;
};

$normalize_documents = static function ($value) {
    $urls = array();
    $stack = is_array($value) ? $value : array($value);

    while (!empty($stack)) {
        $item = array_pop($stack);
        if (is_array($item)) {
            foreach ($item as $nested_item) {
                $stack[] = $nested_item;
            }
            continue;
        }

        if (is_numeric($item)) {
            $item = wp_get_attachment_url((int) $item);
        }
        if (is_string($item) && filter_var($item, FILTER_VALIDATE_URL)) {
            $urls[$item] = $item;
        }
    }

    return array_values($urls);
};

$document_fields = array(
    'curriculum_vitae' => __('Curriculum vitae', 'design_comuni_italia'),
    'variazione_situazione_patrimoniale' => __('Variazioni patrimoniali', 'design_comuni_italia'),
    'dichiarazione_redditi' => __('Dichiarazione dei redditi', 'design_comuni_italia'),
    'spese_elettorali' => __('Spese elettorali', 'design_comuni_italia'),
    'altre_cariche' => __('Altre cariche e compensi', 'design_comuni_italia'),
    'relazione_inizio_mandato' => __('Relazione di inizio mandato', 'design_comuni_italia'),
    'relazione_fine_mandato' => __('Relazione di fine mandato', 'design_comuni_italia'),
    'altri_documenti' => __('Altri documenti', 'design_comuni_italia'),
);

$people = array();
$eligible_person_ids = array_values(array_unique(array_merge(
    array_map('intval', array_keys($member_ids)),
    array_map('intval', array_keys($people_assignments))
)));

foreach ($eligible_person_ids as $person_id) {
    $person = get_post($person_id);
    if (!$person instanceof WP_Post || $person->post_status !== 'publish') {
        continue;
    }

    if ($section_name === 'Consiglio Comunale' && empty($people_assignments[$person_id])) {
        $person_role_slugs = $all_person_role_slugs[$person_id] ?? array();
        $has_excluded_executive_role = false;
        foreach ($person_role_slugs as $person_role_slug) {
            if ($person_role_slug === 'sindaco'
                || $person_role_slug === 'il-sindaco'
                || strpos($person_role_slug, 'assessore') !== false
                || in_array($person_role_slug, array('vicesindaco', 'vice-sindaco'), true)
            ) {
                $has_excluded_executive_role = true;
                break;
            }
        }

        if ($has_excluded_executive_role) {
            continue;
        }
    }

    $person_data = array(
        'id' => $person_id,
        'name' => get_the_title($person_id),
        'url' => get_permalink($person_id),
        'description' => dci_get_meta('descrizione_breve', '_dci_persona_pubblica_', $person_id),
        'biography' => dci_get_meta('biografia', '_dci_persona_pubblica_', $person_id),
        'delegations' => dci_get_meta('deleghe', '_dci_persona_pubblica_', $person_id),
        'image' => get_the_post_thumbnail_url($person_id, 'medium_large'),
        'image_alt' => '',
        'roles' => array(),
        'documents' => array(),
        'contacts' => dci_get_contatti_da_punti_ids(dci_normalize_meta_ids(
            dci_get_meta('punti_contatto', '_dci_persona_pubblica_', $person_id)
        )),
    );

    $person_photo = dci_get_meta('foto', '_dci_persona_pubblica_', $person_id);
    if (empty($person_data['image'])) {
        if (is_numeric($person_photo)) {
            $person_data['image'] = wp_get_attachment_url((int) $person_photo);
        } elseif (is_array($person_photo) && !empty($person_photo['url'])) {
            $person_data['image'] = (string) $person_photo['url'];
        } elseif (is_string($person_photo)) {
            $person_data['image'] = $person_photo;
        }
    }

    $person_image_id = !empty($person_data['image']) ? attachment_url_to_postid($person_data['image']) : 0;
    $person_data['image_alt'] = $person_image_id
        ? trim((string) get_post_meta($person_image_id, '_wp_attachment_image_alt', true))
        : '';
    if ($person_data['image_alt'] === '') {
        $person_data['image_alt'] = $person_data['name'];
    }

    $person_start_date = dci_get_meta('data_inizio_incarico', '_dci_persona_pubblica_', $person_id);
    $person_end_date = dci_get_meta('data_conclusione_incarico', '_dci_persona_pubblica_', $person_id);

    foreach ($people_assignments[$person_id] ?? array() as $assignment_id) {
        $start_date = dci_get_meta('data_inizio_incarico', '_dci_incarico_', $assignment_id);
        $end_date = dci_get_meta('data_conclusione_incarico', '_dci_incarico_', $assignment_id);
        $person_data['roles'][] = array(
            'title' => get_the_title($assignment_id),
            'start' => $format_date(!empty($start_date) ? $start_date : $person_start_date),
            'end' => $format_date(!empty($end_date) ? $end_date : $person_end_date),
        );
    }

    foreach ($document_fields as $field_name => $field_label) {
        foreach ($normalize_documents(dci_get_meta($field_name, '_dci_persona_pubblica_', $person_id)) as $document_url) {
            $document_path = (string) wp_parse_url($document_url, PHP_URL_PATH);
            $document_name = urldecode((string) basename($document_path));
            $person_data['documents'][] = array(
                'label' => $field_label,
                'name' => $document_name !== '' ? $document_name : $field_label,
                'url' => $document_url,
            );
        }
    }

    $people[] = $person_data;
}

usort($people, static function ($first, $second) {
    return strcasecmp((string) $first['name'], (string) $second['name']);
});

$section_offices = array();
foreach ($office_ids as $office_id) {
    $office = get_post($office_id);
    if (!$office instanceof WP_Post || $office->post_status !== 'publish') {
        continue;
    }

    $section_offices[] = array(
        'name' => get_the_title($office_id),
        'url' => get_permalink($office_id),
        'competences' => dci_get_meta('competenze', '_dci_unita_organizzativa_', $office_id),
    );
}
?>

<section aria-labelledby="dci-political-office-title">
    <div style="margin-bottom:1.5rem;padding:1rem 1.15rem;background:#fff;border:1px solid #dfe7f0;border-radius:.25rem;">
        <h2 id="dci-political-office-title" class="h4 mb-3"><?php echo esc_html($section_name); ?></h2>
        <?php foreach ($section_offices as $office_index => $section_office) { ?>
            <div style="<?php echo $office_index > 0 ? 'margin-top:1rem;padding-top:1rem;border-top:1px solid #dfe7f0;' : ''; ?>">
                <strong style="display:block;margin-bottom:.35rem;color:#17324d;font-size:.95rem;">
                    <?php echo esc_html($section_office['name']); ?>
                </strong>
                <?php if (!empty($section_office['competences'])) { ?>
                    <div class="richtext-wrapper" style="margin-bottom:.85rem;color:#334e68;font-size:.92rem;line-height:1.55;">
                        <?php echo wp_kses_post(wpautop($section_office['competences'])); ?>
                    </div>
                <?php } ?>
                <a href="<?php echo esc_url($section_office['url']); ?>" class="text-decoration-none" style="display:inline-flex;align-items:center;gap:.45rem;padding:.5rem .75rem;color:#17324d;background:#f2f5f7;border:1px solid #d5e0e8;border-radius:.25rem;font-size:.86rem;font-weight:700;">
                    <span><?php esc_html_e('Vai alla scheda dell’ufficio', 'design_comuni_italia'); ?></span>
                    <svg class="icon icon-sm" aria-hidden="true" style="width:.95rem;height:.95rem;fill:currentColor;"><use href="#it-arrow-right"></use></svg>
                </a>
            </div>
        <?php } ?>
    </div>

    <?php if (empty($office_ids)) { ?>
        <div class="alert alert-warning" role="status">
            <?php esc_html_e('Non è stato possibile individuare l’ufficio configurato per questa sezione.', 'design_comuni_italia'); ?>
        </div>
    <?php } elseif (empty($people)) { ?>
        <div class="alert alert-info" role="status">
            <?php esc_html_e('Nessuna persona con un incarico compatibile è attualmente associata a questo ufficio.', 'design_comuni_italia'); ?>
        </div>
    <?php } else { ?>
        <div class="row g-4">
            <?php foreach ($people as $person) {
                $visible_documents = array_slice($person['documents'], 0, 10);
                $remaining_documents = max(0, count($person['documents']) - count($visible_documents));
                $grouped_documents = array();
                foreach ($visible_documents as $visible_document) {
                    $grouped_documents[$visible_document['label']][] = $visible_document;
                }
                $image_url = is_string($person['image']) ? $person['image'] : '';
                ?>
                <div class="col-12">
                    <article class="card no-after rounded border border-light h-100" style="overflow:hidden;box-shadow:none;">
                        <div class="h-100">
                            <?php if ($image_url !== '') { ?>
                                <div style="display:flex;align-items:center;justify-content:center;min-height:13rem;padding:1.25rem;background:#f7f9fb;border-bottom:1px solid #dfe7f0;">
                                    <img
                                        src="<?php echo esc_url($image_url); ?>"
                                        alt="<?php echo esc_attr($person['image_alt']); ?>"
                                        loading="lazy"
                                        decoding="async"
                                        style="display:block;width:auto;max-width:100%;height:auto;max-height:16rem;object-fit:contain;border-radius:.25rem;"
                                    >
                                </div>
                            <?php } ?>
                            <div>
                                <div class="card-body h-100" style="padding:1.25rem;">
                                    <span style="display:block;margin-bottom:.25rem;color:#455a64;font-size:.78rem;font-weight:700;text-transform:uppercase;">
                                        <?php echo esc_html($section_name); ?>
                                    </span>
                                    <a href="<?php echo esc_url($person['url']); ?>" class="text-decoration-none">
                                        <h3 class="h4 card-title mb-2" style="color:#17324d;"><?php echo esc_html($person['name']); ?></h3>
                                    </a>

                                    <?php if (!empty($person['description'])) { ?>
                                        <p class="mb-3"><?php echo esc_html(wp_strip_all_tags($person['description'])); ?></p>
                                    <?php } ?>

                                    <?php if (!empty($person['biography'])) { ?>
                                        <section aria-label="<?php esc_attr_e('Biografia', 'design_comuni_italia'); ?>" style="margin-bottom:1rem;padding:.9rem 1rem;background:#f7f9fb;border:1px solid #dfe7f0;border-radius:.25rem;">
                                            <strong style="display:block;margin-bottom:.4rem;color:#17324d;font-size:.92rem;">
                                                <?php esc_html_e('Biografia', 'design_comuni_italia'); ?>
                                            </strong>
                                            <div class="richtext-wrapper" style="color:#334e68;font-size:.9rem;line-height:1.55;">
                                                <?php echo wp_kses_post(wpautop($person['biography'])); ?>
                                            </div>
                                        </section>
                                    <?php } ?>

                                    <?php if (empty($person['roles'])) { ?>
                                        <div style="display:flex;align-items:center;gap:.45rem;margin-bottom:1rem;padding:.8rem;background:#fff;border:1px solid #dfe7f0;border-radius:.25rem;color:#334e68;font-size:.9rem;font-weight:600;">
                                            <svg class="icon icon-sm" aria-hidden="true" style="width:1rem;height:1rem;fill:#455a64;"><use href="#it-user"></use></svg>
                                            <?php esc_html_e('Componente dell’ufficio', 'design_comuni_italia'); ?>
                                        </div>
                                    <?php } ?>

                                    <?php foreach ($person['roles'] as $role) { ?>
                                        <div style="margin-bottom:1rem;padding:.8rem;background:#fff;border:1px solid #dfe7f0;border-radius:.25rem;">
                                            <span style="display:block;margin-bottom:.3rem;color:#5b6f82;font-size:.72rem;font-weight:700;text-transform:uppercase;">
                                                <?php esc_html_e('Incarico della persona', 'design_comuni_italia'); ?>
                                            </span>
                                            <strong style="display:flex;align-items:center;gap:.4rem;color:#17324d;">
                                                <svg class="icon icon-sm" aria-hidden="true" style="width:1rem;height:1rem;fill:#0066cc;"><use href="#it-user"></use></svg>
                                                <?php echo esc_html($role['title']); ?>
                                            </strong>
                                            <?php if ($role['start'] !== '' || $role['end'] !== '') { ?>
                                                <dl style="display:grid;grid-template-columns:repeat(auto-fit,minmax(9rem,1fr));gap:.5rem;margin:.65rem 0 0;">
                                                    <?php if ($role['start'] !== '') { ?>
                                                        <div style="padding:.5rem .6rem;background:#fff;border-radius:.2rem;">
                                                            <dt style="display:flex;align-items:center;gap:.3rem;color:#455a64;font-size:.75rem;font-weight:700;">
                                                                <svg class="icon icon-sm" aria-hidden="true" style="width:.9rem;height:.9rem;"><use href="#it-calendar"></use></svg>
                                                                <?php esc_html_e('Inizio incarico', 'design_comuni_italia'); ?>
                                                            </dt>
                                                            <dd style="margin:.2rem 0 0;color:#17324d;font-size:.88rem;font-weight:600;"><?php echo esc_html($role['start']); ?></dd>
                                                        </div>
                                                    <?php } ?>
                                                    <?php if ($role['end'] !== '') { ?>
                                                        <div style="padding:.5rem .6rem;background:#fff;border-radius:.2rem;">
                                                            <dt style="display:flex;align-items:center;gap:.3rem;color:#455a64;font-size:.75rem;font-weight:700;">
                                                                <svg class="icon icon-sm" aria-hidden="true" style="width:.9rem;height:.9rem;"><use href="#it-calendar"></use></svg>
                                                                <?php esc_html_e('Fine incarico', 'design_comuni_italia'); ?>
                                                            </dt>
                                                            <dd style="margin:.2rem 0 0;color:#17324d;font-size:.88rem;font-weight:600;"><?php echo esc_html($role['end']); ?></dd>
                                                        </div>
                                                    <?php } ?>
                                                </dl>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>

                                    <?php if (!empty($person['delegations'])) { ?>
                                        <section aria-label="<?php esc_attr_e('Deleghe', 'design_comuni_italia'); ?>" style="margin-top:1rem;padding:.9rem 1rem;background:#f7f9fb;border:1px solid #dfe7f0;border-radius:.25rem;">
                                            <strong style="display:flex;align-items:center;gap:.45rem;margin-bottom:.45rem;color:#17324d;font-size:.92rem;">
                                                <svg class="icon icon-sm" aria-hidden="true" style="width:1rem;height:1rem;fill:currentColor;"><use href="#it-list"></use></svg>
                                                <?php esc_html_e('Deleghe', 'design_comuni_italia'); ?>
                                            </strong>
                                            <div class="richtext-wrapper" style="color:#334e68;font-size:.9rem;line-height:1.55;">
                                                <?php echo wp_kses_post(wpautop($person['delegations'])); ?>
                                            </div>
                                        </section>
                                    <?php } ?>

                                    <?php if (!empty($grouped_documents)) {
                                        ?>
                                        <section aria-label="<?php esc_attr_e('Documenti di trasparenza', 'design_comuni_italia'); ?>" style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid #dfe7f0;">
                                            <strong style="display:block;margin-bottom:.75rem;color:#17324d;font-size:1rem;">
                                                <?php esc_html_e('Documenti di trasparenza', 'design_comuni_italia'); ?>
                                            </strong>
                                            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,18rem),1fr));gap:.75rem;align-items:start;">
                                        <?php foreach ($grouped_documents as $document_group_label => $document_group) { ?>
                                            <div style="min-width:0;padding:.8rem;background:#fff;border:1px solid #d5e0e8;border-radius:.25rem;">
                                                <strong style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin-bottom:.5rem;color:#455a64;font-size:.82rem;">
                                                    <span><?php echo esc_html($document_group_label); ?></span>
                                                    <span style="padding:.12rem .42rem;color:#334e68;background:#e9f0f5;border-radius:.2rem;font-size:.7rem;">
                                                        <?php echo esc_html(number_format_i18n(count($document_group))); ?>
                                                    </span>
                                                </strong>
                                                <ul style="display:grid;gap:.5rem;margin:0;padding:0;list-style:none;">
                                                    <?php foreach ($document_group as $document) {
                                                        $document_path = (string) wp_parse_url($document['url'], PHP_URL_PATH);
                                                        $document_extension = strtolower((string) pathinfo($document_path, PATHINFO_EXTENSION));
                                                        $document_format = $document_extension !== '' ? strtoupper($document_extension) : __('FILE', 'design_comuni_italia');
                                                        $document_icon = '#it-file';
                                                        $document_color = '#455a64';

                                                        if ($document_extension === 'pdf') {
                                                            $document_color = '#b42318';
                                                        } elseif (in_array($document_extension, array('zip', 'rar', '7z', 'tar', 'gz'), true)) {
                                                            $document_icon = '#it-folder';
                                                            $document_color = '#9a6700';
                                                        } elseif (in_array($document_extension, array('doc', 'docx', 'odt', 'rtf'), true)) {
                                                            $document_color = '#175cd3';
                                                        } elseif (in_array($document_extension, array('xls', 'xlsx', 'ods', 'csv'), true)) {
                                                            $document_color = '#18794e';
                                                        } elseif (in_array($document_extension, array('ppt', 'pptx', 'odp'), true)) {
                                                            $document_color = '#c2410c';
                                                        } elseif (in_array($document_extension, array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif'), true)) {
                                                            $document_color = '#7e22ce';
                                                        }
                                                        ?>
                                                        <li style="min-width:0;">
                                                            <a
                                                                href="<?php echo esc_url($document['url']); ?>"
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                title="<?php echo esc_attr($document['name']); ?>"
                                                                style="display:flex;align-items:center;gap:.5rem;width:100%;min-height:2.75rem;padding:.5rem;background:#f7f9fb;border:1px solid #e3e9ef;border-radius:.25rem;color:#17324d;font-size:.86rem;font-weight:600;text-decoration:none;"
                                                            >
                                                                <svg class="icon icon-sm" aria-hidden="true" style="flex:0 0 auto;width:1.15rem;height:1.15rem;fill:<?php echo esc_attr($document_color); ?>;"><use href="<?php echo esc_attr($document_icon); ?>"></use></svg>
                                                                <span aria-hidden="true" style="display:inline-flex;flex:0 0 auto;align-items:center;justify-content:center;min-width:2.5rem;height:1.35rem;padding:0 .3rem;color:#fff;background:<?php echo esc_attr($document_color); ?>;border-radius:.2rem;font-size:.66rem;line-height:1;font-weight:700;"><?php echo esc_html($document_format); ?></span>
                                                                <span style="min-width:0;overflow-wrap:anywhere;line-height:1.3;"><?php echo esc_html($document['name']); ?></span>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        <?php } ?>
                                            </div>
                                        <?php if ($remaining_documents > 0) { ?>
                                            <a href="<?php echo esc_url($person['url']); ?>" style="display:inline-block;margin-top:.6rem;font-size:.85rem;font-weight:600;">
                                                <?php echo esc_html(sprintf(_n('+ %s altro documento', '+ %s altri documenti', $remaining_documents, 'design_comuni_italia'), number_format_i18n($remaining_documents))); ?>
                                            </a>
                                        <?php } ?>
                                        </section>
                                    <?php } ?>

                                    <?php
                                    $contacts = $person['contacts'];
                                    $has_contacts = !empty($contacts['telefono'])
                                        || !empty($contacts['email'])
                                        || !empty($contacts['pec'])
                                        || !empty($contacts['indirizzo'])
                                        || !empty($contacts['url']);
                                    if ($has_contacts) { ?>
                                        <section aria-label="<?php esc_attr_e('Recapiti della persona', 'design_comuni_italia'); ?>" style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid #dfe7f0;">
                                            <strong style="display:flex;align-items:center;gap:.45rem;margin-bottom:.75rem;color:#17324d;font-size:1rem;">
                                                <svg class="icon icon-sm" aria-hidden="true" style="width:1.05rem;height:1.05rem;fill:currentColor;"><use href="#it-card"></use></svg>
                                                <?php esc_html_e('Recapiti della persona', 'design_comuni_italia'); ?>
                                            </strong>
                                            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,15rem),1fr));gap:.65rem;">
                                                <?php foreach ((array) $contacts['telefono'] as $phone) { ?>
                                                    <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>" style="display:flex;align-items:center;gap:.65rem;min-width:0;min-height:4rem;padding:.7rem .8rem;background:#f7f9fb;border:1px solid #dfe7f0;border-radius:.25rem;color:#17324d;text-decoration:none;">
                                                        <span aria-hidden="true" style="display:flex;flex:0 0 2rem;width:2rem;height:2rem;align-items:center;justify-content:center;background:#e7f2ec;border-radius:50%;color:#18794e;"><svg class="icon icon-sm" style="width:1rem;height:1rem;fill:currentColor;"><use href="#it-telephone"></use></svg></span>
                                                        <span style="min-width:0;"><small style="display:block;margin-bottom:.1rem;color:#455a64;font-size:.72rem;font-weight:700;text-transform:uppercase;"><?php esc_html_e('Telefono', 'design_comuni_italia'); ?></small><span style="font-size:.9rem;font-weight:600;overflow-wrap:anywhere;"><?php echo esc_html($phone); ?></span></span>
                                                    </a>
                                                <?php } ?>
                                                <?php foreach ((array) $contacts['email'] as $email) { ?>
                                                    <a href="mailto:<?php echo esc_attr(sanitize_email($email)); ?>" style="display:flex;align-items:center;gap:.65rem;min-width:0;min-height:4rem;padding:.7rem .8rem;background:#f7f9fb;border:1px solid #dfe7f0;border-radius:.25rem;color:#17324d;text-decoration:none;">
                                                        <span aria-hidden="true" style="display:flex;flex:0 0 2rem;width:2rem;height:2rem;align-items:center;justify-content:center;background:#e8f0fb;border-radius:50%;color:#175cd3;"><svg class="icon icon-sm" style="width:1rem;height:1rem;fill:currentColor;"><use href="#it-mail"></use></svg></span>
                                                        <span style="min-width:0;"><small style="display:block;margin-bottom:.1rem;color:#455a64;font-size:.72rem;font-weight:700;text-transform:uppercase;"><?php esc_html_e('Email', 'design_comuni_italia'); ?></small><span style="font-size:.9rem;font-weight:600;overflow-wrap:anywhere;"><?php echo esc_html($email); ?></span></span>
                                                    </a>
                                                <?php } ?>
                                                <?php foreach ((array) $contacts['pec'] as $email) { ?>
                                                    <a href="mailto:<?php echo esc_attr(sanitize_email($email)); ?>" style="display:flex;align-items:center;gap:.65rem;min-width:0;min-height:4rem;padding:.7rem .8rem;background:#f7f9fb;border:1px solid #dfe7f0;border-radius:.25rem;color:#17324d;text-decoration:none;">
                                                        <span aria-hidden="true" style="display:flex;flex:0 0 2rem;width:2rem;height:2rem;align-items:center;justify-content:center;background:#f3eafa;border-radius:50%;color:#7e22ce;"><svg class="icon icon-sm" style="width:1rem;height:1rem;fill:currentColor;"><use href="#it-mail"></use></svg></span>
                                                        <span style="min-width:0;"><small style="display:block;margin-bottom:.1rem;color:#455a64;font-size:.72rem;font-weight:700;text-transform:uppercase;"><?php esc_html_e('PEC', 'design_comuni_italia'); ?></small><span style="font-size:.9rem;font-weight:600;overflow-wrap:anywhere;"><?php echo esc_html($email); ?></span></span>
                                                    </a>
                                                <?php } ?>
                                                <?php foreach ((array) $contacts['indirizzo'] as $address) { ?>
                                                    <div style="display:flex;align-items:center;gap:.65rem;min-width:0;min-height:4rem;padding:.7rem .8rem;background:#f7f9fb;border:1px solid #dfe7f0;border-radius:.25rem;color:#17324d;">
                                                        <span aria-hidden="true" style="display:flex;flex:0 0 2rem;width:2rem;height:2rem;align-items:center;justify-content:center;background:#fff2df;border-radius:50%;color:#9a6700;"><svg class="icon icon-sm" style="width:1rem;height:1rem;fill:currentColor;"><use href="#it-map-marker-circle"></use></svg></span>
                                                        <span style="min-width:0;"><small style="display:block;margin-bottom:.1rem;color:#455a64;font-size:.72rem;font-weight:700;text-transform:uppercase;"><?php esc_html_e('Indirizzo', 'design_comuni_italia'); ?></small><span style="font-size:.9rem;font-weight:600;overflow-wrap:anywhere;"><?php echo esc_html($address); ?></span></span>
                                                    </div>
                                                <?php } ?>
                                                <?php foreach ((array) $contacts['url'] as $contact_url) { ?>
                                                    <?php $contact_host = (string) wp_parse_url($contact_url, PHP_URL_HOST); ?>
                                                    <a href="<?php echo esc_url($contact_url); ?>" target="_blank" rel="noopener noreferrer" style="display:flex;align-items:center;gap:.65rem;min-width:0;min-height:4rem;padding:.7rem .8rem;background:#f7f9fb;border:1px solid #dfe7f0;border-radius:.25rem;color:#17324d;text-decoration:none;">
                                                        <span aria-hidden="true" style="display:flex;flex:0 0 2rem;width:2rem;height:2rem;align-items:center;justify-content:center;background:#e9eef2;border-radius:50%;color:#455a64;"><svg class="icon icon-sm" style="width:1rem;height:1rem;fill:currentColor;"><use href="#it-link"></use></svg></span>
                                                        <span style="min-width:0;"><small style="display:block;margin-bottom:.1rem;color:#455a64;font-size:.72rem;font-weight:700;text-transform:uppercase;"><?php esc_html_e('Sito web', 'design_comuni_italia'); ?></small><span style="font-size:.9rem;font-weight:600;overflow-wrap:anywhere;"><?php echo esc_html($contact_host !== '' ? $contact_host : $contact_url); ?></span></span>
                                                    </a>
                                                <?php } ?>
                                            </div>
                                        </section>
                                    <?php } ?>

                                    <a href="<?php echo esc_url($person['url']); ?>" class="text-decoration-none" style="display:inline-flex;align-items:center;gap:.5rem;margin-top:1.5rem;padding:.55rem .8rem;color:#17324d;background:#eef3f7;border:1px solid #d5e0e8;border-radius:.25rem;font-size:.9rem;font-weight:700;">
                                        <span><?php esc_html_e('Apri dettaglio', 'design_comuni_italia'); ?></span>
                                        <svg class="icon icon-sm" aria-hidden="true" style="width:1rem;height:1rem;fill:currentColor;"><use href="#it-arrow-right"></use></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</section>
