<?php
/**
 * Esportazione e sostituzione controllata dei soli dati di Amministrazione Trasparente.
 */

if (!defined('ABSPATH')) {
    exit;
}

function dci_trasparenza_transfer_allowed() {
    return 1 === (int) get_current_user_id() && current_user_can('manage_options');
}

function dci_trasparenza_transfer_post_types() {
    return array(
        'elemento_trasparenza',
        'bando',
        'atto_concessione',
        'titolare_incarico',
        'incarichi_dip',
        'incarico_dirig',
    );
}

function dci_trasparenza_transfer_post_type_labels() {
    return array(
        'elemento_trasparenza' => __('Elementi generici', 'design_comuni_italia'),
        'bando'                => __('Atti, documenti e link a BDNCP', 'design_comuni_italia'),
        'atto_concessione'     => __('Atti di concessione', 'design_comuni_italia'),
        'titolare_incarico'    => __('Titolari di incarichi', 'design_comuni_italia'),
        'incarichi_dip'        => __('Incarichi conferiti ai dipendenti', 'design_comuni_italia'),
        'incarico_dirig'       => __('Incarichi dirigenziali', 'design_comuni_italia'),
    );
}

function dci_trasparenza_transfer_order_terms_hierarchically($terms) {
    $terms_by_parent = array();
    $known_ids = array();
    foreach ((array) $terms as $term) {
        $known_ids[(int) $term->term_id] = true;
    }
    foreach ((array) $terms as $term) {
        $parent_id = (int) $term->parent;
        if ($parent_id && !isset($known_ids[$parent_id])) {
            $parent_id = 0;
        }
        $terms_by_parent[$parent_id][] = $term;
    }
    foreach ($terms_by_parent as &$siblings) {
        usort($siblings, static function ($first, $second) {
            return strcasecmp($first->name, $second->name);
        });
    }
    unset($siblings);

    $ordered = array();
    $append_branch = static function ($parent_id, $depth) use (&$append_branch, &$ordered, $terms_by_parent) {
        foreach ($terms_by_parent[$parent_id] ?? array() as $term) {
            $ordered[] = array('term' => $term, 'depth' => $depth);
            $append_branch((int) $term->term_id, $depth + 1);
        }
    };
    $append_branch(0, 0);

    return $ordered;
}

function dci_trasparenza_transfer_dir($child = '') {
    $uploads = wp_upload_dir();
    $base = trailingslashit($uploads['basedir']) . 'dci-trasparenza-transfer';
    wp_mkdir_p($base);
    if (!is_file(trailingslashit($base) . 'index.php')) {
        file_put_contents(trailingslashit($base) . 'index.php', "<?php\n// Silence is golden.\n");
    }
    if (!is_file(trailingslashit($base) . '.htaccess')) {
        file_put_contents(trailingslashit($base) . '.htaccess', "Require all denied\nDeny from all\n");
    }

    return $child === '' ? $base : trailingslashit($base) . ltrim($child, '/');
}

function dci_trasparenza_transfer_collect_attachment_ids($value, &$ids) {
    if (is_array($value)) {
        foreach ($value as $item) {
            dci_trasparenza_transfer_collect_attachment_ids($item, $ids);
        }
        return;
    }

    if (!is_numeric($value)) {
        return;
    }

    $id = absint($value);
    if ($id && 'attachment' === get_post_type($id)) {
        $ids[$id] = $id;
    }
}

function dci_trasparenza_transfer_export_data($requested_post_types = null, $requested_term_ids = null) {
    $allowed_post_types = dci_trasparenza_transfer_post_types();
    $post_types = null === $requested_post_types
        ? $allowed_post_types
        : array_values(array_intersect($allowed_post_types, array_map('sanitize_key', (array) $requested_post_types)));

    $all_term_ids = get_terms(array('taxonomy' => 'tipi_cat_amm_trasp', 'hide_empty' => false, 'fields' => 'ids'));
    $all_term_ids = is_wp_error($all_term_ids) ? array() : array_map('intval', $all_term_ids);
    $selected_term_ids = null === $requested_term_ids
        ? $all_term_ids
        : array_values(array_unique(array_filter(array_map('absint', (array) $requested_term_ids))));
    if (!in_array('elemento_trasparenza', $post_types, true)) {
        $selected_term_ids = array();
    }
    $posts = get_posts(array(
        'post_type'      => $post_types,
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'orderby'        => 'ID',
        'order'          => 'ASC',
    ));
    $attachment_ids = array();
    $export_posts = array();

    foreach ($posts as $post) {
        $post_term_ids = wp_get_object_terms($post->ID, 'tipi_cat_amm_trasp', array('fields' => 'ids'));
        $post_term_ids = is_wp_error($post_term_ids) ? array() : array_map('intval', $post_term_ids);
        if ('elemento_trasparenza' === $post->post_type && empty(array_intersect($post_term_ids, $selected_term_ids))) {
            continue;
        }
        $meta = get_post_meta($post->ID);
        foreach ($meta as $values) {
            foreach ($values as $value) {
                dci_trasparenza_transfer_collect_attachment_ids(maybe_unserialize($value), $attachment_ids);
            }
        }
        foreach (get_attached_media('', $post->ID) as $attached_media) {
            $attachment_ids[(int) $attached_media->ID] = (int) $attached_media->ID;
        }
        if (preg_match_all('/(?:wp-image-|attachment[_-])([0-9]+)/', $post->post_content, $matches)) {
            foreach ($matches[1] as $content_attachment_id) {
                if ('attachment' === get_post_type((int) $content_attachment_id)) {
                    $attachment_ids[(int) $content_attachment_id] = (int) $content_attachment_id;
                }
            }
        }

        $export_posts[] = array(
            'source_id' => (int) $post->ID,
            'data'      => array_intersect_key((array) $post, array_flip(array(
                'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title',
                'post_excerpt', 'post_status', 'comment_status', 'ping_status', 'post_password',
                'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt',
                'post_content_filtered', 'post_parent', 'menu_order', 'post_type', 'post_mime_type',
            ))),
            'meta'      => array_map(static function ($values) {
                return array_map('maybe_unserialize', $values);
            }, $meta),
            'terms'     => $post_term_ids,
        );
    }

    $terms = get_terms(array(
        'taxonomy'   => 'tipi_cat_amm_trasp',
        'hide_empty' => false,
        'orderby'    => 'term_id',
        'order'      => 'ASC',
    ));
    $export_terms = array();
    if (!is_wp_error($terms)) {
        $structural_term_ids = $selected_term_ids;
        foreach ($selected_term_ids as $selected_term_id) {
            $structural_term_ids = array_merge($structural_term_ids, get_ancestors($selected_term_id, 'tipi_cat_amm_trasp', 'taxonomy'));
        }
        $structural_term_ids = array_unique(array_map('intval', $structural_term_ids));
        foreach ($terms as $term) {
            if (!in_array((int) $term->term_id, $structural_term_ids, true)) {
                continue;
            }
            $export_terms[] = array(
                'source_id'   => (int) $term->term_id,
                'name'        => $term->name,
                'slug'        => $term->slug,
                'description' => $term->description,
                'parent'      => (int) $term->parent,
                'meta'        => get_term_meta($term->term_id),
            );
        }
    }

    $attachment_urls = array();
    foreach ($attachment_ids as $attachment_id) {
        $attachment_urls[$attachment_id] = wp_get_attachment_url($attachment_id);
    }

    return array(
        'format'         => 'dci-trasparenza-content',
        'version'        => 1,
        'transfer_id'    => substr(hash('sha256', dci_trasparenza_transfer_json(array($export_posts, $export_terms, get_option('trasparenza', array()), array_values($attachment_ids)))), 0, 32),
        'created_at'     => gmdate('c'),
        'source_url'     => home_url('/'),
        'post_types'     => $post_types,
        'scope'          => array(
            'post_types' => $post_types,
            'term_ids'   => $selected_term_ids,
        ),
        'posts'          => $export_posts,
        'terms'          => $export_terms,
        'options'        => array('trasparenza' => get_option('trasparenza', array())),
        'attachment_ids' => array_values($attachment_ids),
        'attachment_urls' => $attachment_urls,
    );
}

function dci_trasparenza_transfer_json($data) {
    return wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

function dci_trasparenza_transfer_create_content_file($data, $path) {
    $json = dci_trasparenza_transfer_json($data);
    return false !== $json && false !== file_put_contents($path, $json);
}

function dci_trasparenza_transfer_attachment_files($attachment_id) {
    $files = array();
    $main = get_attached_file($attachment_id);
    if ($main && is_file($main)) {
        $files[basename($main)] = $main;
        $metadata = wp_get_attachment_metadata($attachment_id);
        if (is_array($metadata) && !empty($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size) {
                if (!empty($size['file'])) {
                    $candidate = trailingslashit(dirname($main)) . $size['file'];
                    if (is_file($candidate)) {
                        $files[basename($candidate)] = $candidate;
                    }
                }
            }
        }
    }
    return $files;
}

function dci_trasparenza_transfer_media_parts($attachment_ids, $max_part_bytes = 104857600) {
    $parts = array();
    $current_part = array();
    $current_size = 0;

    foreach ((array) $attachment_ids as $attachment_id) {
        $attachment_size = 0;
        foreach (dci_trasparenza_transfer_attachment_files($attachment_id) as $file) {
            $file_size = filesize($file);
            if (false !== $file_size) {
                $attachment_size += (int) $file_size;
            }
        }

        if ($current_part && $current_size + $attachment_size > $max_part_bytes) {
            $parts[] = $current_part;
            $current_part = array();
            $current_size = 0;
        }

        $current_part[] = (int) $attachment_id;
        $current_size += $attachment_size;
    }

    if ($current_part || empty($parts)) {
        $parts[] = $current_part;
    }

    return $parts;
}

function dci_trasparenza_transfer_create_media_zip($content_data, $path) {
    if (!class_exists('ZipArchive')) {
        return new WP_Error('zip_missing', __('Estensione PHP ZipArchive non disponibile.', 'design_comuni_italia'));
    }

    $zip = new ZipArchive();
    if (true !== $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
        return new WP_Error('zip_open', __('Impossibile creare il pacchetto dei file.', 'design_comuni_italia'));
    }

    $manifest = array(
        'format'      => 'dci-trasparenza-media',
        'version'     => 1,
        'transfer_id' => $content_data['transfer_id'],
        'created_at'  => $content_data['created_at'],
        'part'        => (int) ($content_data['media_part'] ?? 1),
        'total_parts' => (int) ($content_data['media_total_parts'] ?? 1),
        'attachments' => array(),
    );

    foreach ($content_data['attachment_ids'] as $attachment_id) {
        $post = get_post($attachment_id);
        if (!$post || 'attachment' !== $post->post_type) {
            continue;
        }
        $entry = array(
            'source_id' => (int) $attachment_id,
            'post'      => array_intersect_key((array) $post, array_flip(array(
                'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_excerpt',
                'post_status', 'comment_status', 'ping_status', 'post_name', 'post_mime_type',
            ))),
            'metadata'  => wp_get_attachment_metadata($attachment_id),
            'meta'      => array_map(static function ($values) {
                return array_map('maybe_unserialize', $values);
            }, get_post_meta($attachment_id)),
            'files'     => array(),
        );
        foreach (dci_trasparenza_transfer_attachment_files($attachment_id) as $name => $file) {
            $zip_path = 'media/' . (int) $attachment_id . '/' . sanitize_file_name($name);
            if (!$zip->addFile($file, $zip_path)) {
                $zip->close();
                return new WP_Error('zip_add', __('Impossibile aggiungere un file al pacchetto.', 'design_comuni_italia'));
            }
            // I file multimediali sono normalmente già compressi. CM_STORE evita
            // lavoro CPU inutile e riduce sensibilmente il rischio di timeout.
            if (method_exists($zip, 'setCompressionName')) {
                $zip->setCompressionName($zip_path, ZipArchive::CM_STORE);
            }
            $entry['files'][] = array(
                'path'   => $zip_path,
                'name'   => sanitize_file_name($name),
                'sha256' => hash_file('sha256', $file),
                'main'   => get_attached_file($attachment_id) === $file,
            );
        }
        if (!empty($entry['files'])) {
            $manifest['attachments'][] = $entry;
        }
    }

    if (!$zip->addFromString('manifest.json', dci_trasparenza_transfer_json($manifest)) || !$zip->close()) {
        return new WP_Error('zip_close', __('Impossibile completare il pacchetto dei file.', 'design_comuni_italia'));
    }
    return true;
}

function dci_trasparenza_transfer_download($path, $filename, $content_type, $headers = array()) {
    nocache_headers();
    header('Content-Type: ' . $content_type);
    header('Content-Disposition: attachment; filename="' . sanitize_file_name($filename) . '"');
    header('Content-Length: ' . filesize($path));
    foreach ((array) $headers as $name => $value) {
        header(sanitize_key($name) . ': ' . sanitize_text_field($value));
    }
    readfile($path);
    @unlink($path);
    exit;
}

function dci_trasparenza_transfer_notice($type, $message) {
    set_transient('dci_trasparenza_transfer_notice_' . get_current_user_id(), array($type, $message), MINUTE_IN_SECONDS);
    wp_safe_redirect(admin_url('tools.php?page=dci-trasparenza-transfer'));
    exit;
}

function dci_trasparenza_transfer_export_content() {
    if (!dci_trasparenza_transfer_allowed()) {
        wp_die(esc_html__('Operazione non consentita.', 'design_comuni_italia'), '', array('response' => 403));
    }
    check_admin_referer('dci_trasparenza_export');
    $requested_post_types = (array) wp_unslash($_POST['export_post_types'] ?? array());
    $requested_term_ids = (array) wp_unslash($_POST['export_term_ids'] ?? array());
    if (empty($requested_post_types) || (in_array('elemento_trasparenza', $requested_post_types, true) && empty($requested_term_ids))) {
        dci_trasparenza_transfer_notice('error', __('Seleziona almeno una tipologia e, per gli elementi generici, almeno una categoria.', 'design_comuni_italia'));
    }
    $data = dci_trasparenza_transfer_export_data(
        $requested_post_types,
        $requested_term_ids
    );
    $path = wp_tempnam('trasparenza-contenuti.json');
    if (!$path || !dci_trasparenza_transfer_create_content_file($data, $path)) {
        dci_trasparenza_transfer_notice('error', __('Impossibile creare il pacchetto contenuti.', 'design_comuni_italia'));
    }
    dci_trasparenza_transfer_download($path, 'trasparenza-contenuti-' . gmdate('Ymd-His') . '.json', 'application/json');
}
add_action('admin_post_dci_trasparenza_export_content', 'dci_trasparenza_transfer_export_content');

function dci_trasparenza_transfer_export_media() {
    if (!dci_trasparenza_transfer_allowed()) {
        wp_die(esc_html__('Operazione non consentita.', 'design_comuni_italia'), '', array('response' => 403));
    }
    check_admin_referer('dci_trasparenza_export');
    $requested_post_types = (array) wp_unslash($_POST['export_post_types'] ?? array());
    $requested_term_ids = (array) wp_unslash($_POST['export_term_ids'] ?? array());
    if (empty($requested_post_types) || (in_array('elemento_trasparenza', $requested_post_types, true) && empty($requested_term_ids))) {
        dci_trasparenza_transfer_notice('error', __('Seleziona almeno una tipologia e, per gli elementi generici, almeno una categoria.', 'design_comuni_italia'));
    }
    $data = dci_trasparenza_transfer_export_data(
        $requested_post_types,
        $requested_term_ids
    );
    $attachment_ids = array_values((array) $data['attachment_ids']);
    $media_parts = dci_trasparenza_transfer_media_parts($attachment_ids, 600 * MB_IN_BYTES);
    $total_parts = count($media_parts);
    $part = max(1, absint($_POST['media_part'] ?? 1));
    if ($part > $total_parts) {
        dci_trasparenza_transfer_notice('error', sprintf(__('La parte richiesta non esiste. Parti disponibili: %d.', 'design_comuni_italia'), $total_parts));
    }
    $data['attachment_ids'] = $media_parts[$part - 1];
    $data['media_part'] = $part;
    $data['media_total_parts'] = $total_parts;
    $path = wp_tempnam('trasparenza-file.zip');
    $result = $path ? dci_trasparenza_transfer_create_media_zip($data, $path) : new WP_Error('temp', 'Errore file temporaneo');
    if (is_wp_error($result)) {
        dci_trasparenza_transfer_notice('error', $result->get_error_message());
    }
    dci_trasparenza_transfer_download(
        $path,
        sprintf('trasparenza-file-parte-%1$d-di-%2$d-%3$s.zip', $part, $total_parts, gmdate('Ymd-His')),
        'application/zip',
        array(
            'X-DCI-Media-Part'        => $part,
            'X-DCI-Media-Total-Parts' => $total_parts,
        )
    );
}
add_action('admin_post_dci_trasparenza_export_media', 'dci_trasparenza_transfer_export_media');

function dci_trasparenza_transfer_validate_content($data) {
    if (!is_array($data) || ($data['format'] ?? '') !== 'dci-trasparenza-content' || 1 !== (int) ($data['version'] ?? 0)) {
        return new WP_Error('invalid_content', __('Pacchetto contenuti non valido o incompatibile.', 'design_comuni_italia'));
    }
    foreach (array('transfer_id', 'posts', 'terms', 'options') as $key) {
        if (!isset($data[$key]) || ('transfer_id' !== $key && !is_array($data[$key]))) {
            return new WP_Error('invalid_content', __('Il pacchetto contenuti è incompleto.', 'design_comuni_italia'));
        }
    }
    return true;
}

function dci_trasparenza_transfer_remap_meta($value, $attachment_map, $attachment_urls = array(), $force = false) {
    if (is_array($value)) {
        foreach ($value as $key => $item) {
            $child_force = $force || in_array((string) $key, array('id', 'ID', 'attachment_id'), true);
            $value[$key] = dci_trasparenza_transfer_remap_meta($item, $attachment_map, $attachment_urls, $child_force);
        }
        return $value;
    }
    if (is_string($value)) {
        foreach ($attachment_map as $source_id => $destination_id) {
            if (!empty($attachment_urls[$source_id])) {
                $value = str_replace($attachment_urls[$source_id], wp_get_attachment_url($destination_id), $value);
            }
        }
    }
    if ($force && is_numeric($value) && isset($attachment_map[(int) $value])) {
        return is_string($value) ? (string) $attachment_map[(int) $value] : $attachment_map[(int) $value];
    }
    return $value;
}

function dci_trasparenza_transfer_import_content_data($data, $requested_post_types = array(), $requested_term_ids = array()) {
    global $wpdb;
    $package_post_types = (array) ($data['scope']['post_types'] ?? $data['post_types'] ?? array());
    $selected_post_types = array_values(array_intersect(
        dci_trasparenza_transfer_post_types(),
        $package_post_types,
        array_map('sanitize_key', (array) $requested_post_types)
    ));
    if (empty($selected_post_types)) {
        return new WP_Error('empty_scope', __('Seleziona almeno una tipologia da importare.', 'design_comuni_italia'));
    }
    $package_term_ids = array_map('intval', (array) ($data['scope']['term_ids'] ?? wp_list_pluck($data['terms'], 'source_id')));
    $selected_term_ids = array_values(array_intersect(
        $package_term_ids,
        array_values(array_unique(array_filter(array_map('absint', (array) $requested_term_ids))))
    ));
    if (in_array('elemento_trasparenza', $selected_post_types, true) && empty($selected_term_ids)) {
        return new WP_Error('empty_terms', __('Seleziona almeno una categoria per gli elementi generici.', 'design_comuni_italia'));
    }
    if (!in_array('elemento_trasparenza', $selected_post_types, true)) {
        $selected_term_ids = array();
    }
    $backup_dir = dci_trasparenza_transfer_dir('backups');
    wp_mkdir_p($backup_dir);
    $backup_path = trailingslashit($backup_dir) . 'prima-import-' . gmdate('Ymd-His') . '.json';
    if (!dci_trasparenza_transfer_create_content_file(dci_trasparenza_transfer_export_data(), $backup_path)) {
        return new WP_Error('backup_failed', __('Backup preventivo non riuscito: importazione annullata.', 'design_comuni_italia'));
    }

    $attachment_map = get_option('dci_trasparenza_media_map_' . sanitize_key($data['transfer_id']), array());
    foreach ((array) ($data['attachment_ids'] ?? array()) as $source_attachment_id) {
        if (empty($attachment_map[(int) $source_attachment_id])) {
            return new WP_Error(
                'media_missing',
                __('Pacchetto file mancante o incompleto: importa prima lo ZIP generato insieme a questo JSON.', 'design_comuni_italia')
            );
        }
    }
    $wpdb->query('START TRANSACTION');
    try {
        $selected_slugs = array();
        foreach ($data['terms'] as $term) {
            if (in_array((int) $term['source_id'], $selected_term_ids, true)) {
                $selected_slugs[] = (string) $term['slug'];
            }
        }
        $existing_args = array(
            'post_type'      => $selected_post_types,
            'post_status'    => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
            'posts_per_page' => -1,
            'fields'         => 'ids',
        );
        if (in_array('elemento_trasparenza', $selected_post_types, true) && 1 === count($selected_post_types)) {
            $existing_args['tax_query'] = array(array(
                'taxonomy' => 'tipi_cat_amm_trasp',
                'field'    => 'slug',
                'terms'    => $selected_slugs,
                'include_children' => false,
            ));
        }
        $existing = get_posts($existing_args);
        foreach ($existing as $post_id) {
            if ('elemento_trasparenza' === get_post_type($post_id)) {
                $existing_post_terms = wp_get_object_terms($post_id, 'tipi_cat_amm_trasp', array('fields' => 'slugs'));
                $existing_post_terms = is_wp_error($existing_post_terms) ? array() : (array) $existing_post_terms;
                if (empty(array_intersect($existing_post_terms, $selected_slugs))) {
                    continue;
                }
                $unselected_post_terms = array_diff($existing_post_terms, $selected_slugs);
                if (!empty($unselected_post_terms)) {
                    $removed = wp_remove_object_terms($post_id, $selected_slugs, 'tipi_cat_amm_trasp');
                    if (is_wp_error($removed)) {
                        throw new Exception($removed->get_error_message());
                    }
                    continue;
                }
            }
            if (!wp_delete_post($post_id, true)) {
                throw new Exception('Impossibile eliminare un contenuto esistente.');
            }
        }

        $term_map = array();
        $needed_term_ids = $selected_term_ids;
        foreach ($data['terms'] as $term) {
            if (!in_array((int) $term['source_id'], $selected_term_ids, true)) {
                continue;
            }
            $parent_id = (int) ($term['parent'] ?? 0);
            while ($parent_id) {
                $needed_term_ids[] = $parent_id;
                $parent_entry = null;
                foreach ($data['terms'] as $candidate) {
                    if ((int) $candidate['source_id'] === $parent_id) {
                        $parent_entry = $candidate;
                        break;
                    }
                }
                $parent_id = $parent_entry ? (int) ($parent_entry['parent'] ?? 0) : 0;
            }
        }
        $needed_term_ids = array_unique($needed_term_ids);
        $pending = array_values(array_filter($data['terms'], static function ($term) use ($needed_term_ids) {
            return in_array((int) $term['source_id'], $needed_term_ids, true);
        }));
        $guard = count($pending) + 1;
        while ($pending && $guard-- > 0) {
            foreach ($pending as $index => $term) {
                $parent = (int) ($term['parent'] ?? 0);
                if ($parent && !isset($term_map[$parent])) {
                    continue;
                }
                $is_selected_term = in_array((int) $term['source_id'], $selected_term_ids, true);
                $existing_term = get_term_by('slug', (string) $term['slug'], 'tipi_cat_amm_trasp');
                if ($existing_term && $is_selected_term) {
                    $created = wp_update_term(
                        $existing_term->term_id,
                        'tipi_cat_amm_trasp',
                        array(
                            'name'        => (string) $term['name'],
                            'slug'        => (string) ($term['slug'] ?? ''),
                            'description' => (string) ($term['description'] ?? ''),
                            'parent'      => $parent ? $term_map[$parent] : 0,
                        )
                    );
                } else {
                    $created = $existing_term ? array('term_id' => $existing_term->term_id) : wp_insert_term(
                    (string) $term['name'],
                    'tipi_cat_amm_trasp',
                    array(
                        'slug'        => (string) ($term['slug'] ?? ''),
                        'description' => (string) ($term['description'] ?? ''),
                        'parent'      => $parent ? $term_map[$parent] : 0,
                    )
                    );
                }
                if (is_wp_error($created)) {
                    throw new Exception($created->get_error_message());
                }
                $new_term_id = (int) $created['term_id'];
                $term_map[(int) $term['source_id']] = $new_term_id;
                if ($is_selected_term || !$existing_term) {
                    if ($is_selected_term && $existing_term) {
                        foreach (array_keys(get_term_meta($new_term_id)) as $existing_meta_key) {
                            delete_term_meta($new_term_id, $existing_meta_key);
                        }
                    }
                    foreach ((array) ($term['meta'] ?? array()) as $key => $values) {
                        foreach ((array) $values as $value) {
                            add_term_meta($new_term_id, $key, $value);
                        }
                    }
                }
                unset($pending[$index]);
            }
        }
        if ($pending) {
            throw new Exception('Gerarchia delle categorie non valida.');
        }

        $post_map = array();
        foreach ($data['posts'] as $entry) {
            $post_data = (array) ($entry['data'] ?? array());
            if (!in_array($post_data['post_type'] ?? '', $selected_post_types, true)) {
                continue;
            }
            if ('elemento_trasparenza' === $post_data['post_type'] && empty(array_intersect(array_map('intval', (array) ($entry['terms'] ?? array())), $selected_term_ids))) {
                continue;
            }
            if (!in_array($post_data['post_type'] ?? '', dci_trasparenza_transfer_post_types(), true)) {
                throw new Exception('Tipologia contenuto non consentita nel pacchetto.');
            }
            $source_parent = (int) ($post_data['post_parent'] ?? 0);
            $post_data['post_parent'] = $post_map[$source_parent] ?? 0;
            if (empty($post_data['post_author']) || !get_user_by('id', (int) $post_data['post_author'])) {
                $post_data['post_author'] = get_current_user_id();
            }
            $post_data['post_content'] = dci_trasparenza_transfer_remap_meta(
                $post_data['post_content'] ?? '',
                $attachment_map,
                (array) ($data['attachment_urls'] ?? array())
            );
            foreach ($attachment_map as $source_attachment_id => $destination_attachment_id) {
                $post_data['post_content'] = str_replace(
                    array('wp-image-' . $source_attachment_id, 'attachment_' . $source_attachment_id),
                    array('wp-image-' . $destination_attachment_id, 'attachment_' . $destination_attachment_id),
                    $post_data['post_content']
                );
            }
            $new_id = wp_insert_post(wp_slash($post_data), true);
            if (is_wp_error($new_id)) {
                throw new Exception($new_id->get_error_message());
            }
            $post_map[(int) $entry['source_id']] = (int) $new_id;
            foreach ((array) ($entry['meta'] ?? array()) as $key => $values) {
                $is_attachment_field = (bool) preg_match('/attachment|alleg|file|document|curriculum|immagine|thumbnail|media/i', (string) $key);
                foreach ((array) $values as $value) {
                    $value = dci_trasparenza_transfer_remap_meta($value, $attachment_map, (array) ($data['attachment_urls'] ?? array()), $is_attachment_field);
                    add_post_meta($new_id, $key, $value);
                }
            }
            $new_terms = array();
            foreach ((array) ($entry['terms'] ?? array()) as $source_term_id) {
                if (in_array((int) $source_term_id, $selected_term_ids, true) && isset($term_map[(int) $source_term_id])) {
                    $new_terms[] = $term_map[(int) $source_term_id];
                }
            }
            wp_set_object_terms($new_id, $new_terms, 'tipi_cat_amm_trasp', false);
        }

        if (count($selected_post_types) === count(dci_trasparenza_transfer_post_types()) && count($selected_term_ids) === count($package_term_ids) && array_key_exists('trasparenza', $data['options'])) {
            update_option('trasparenza', $data['options']['trasparenza']);
        }
        $wpdb->query('COMMIT');
    } catch (Throwable $error) {
        $wpdb->query('ROLLBACK');
        return new WP_Error('import_failed', sprintf(__('Importazione annullata: %s', 'design_comuni_italia'), $error->getMessage()));
    }

    return array('backup' => $backup_path, 'posts' => count($post_map), 'terms' => count($selected_term_ids));
}

function dci_trasparenza_transfer_import_content() {
    if (!dci_trasparenza_transfer_allowed()) {
        wp_die(esc_html__('Operazione non consentita.', 'design_comuni_italia'), '', array('response' => 403));
    }
    check_admin_referer('dci_trasparenza_import_content');
    if (sanitize_text_field(wp_unslash($_POST['confirmation'] ?? '')) !== 'SOSTITUISCI') {
        dci_trasparenza_transfer_notice('error', __('Scrivi SOSTITUISCI per confermare.', 'design_comuni_italia'));
    }
    $tmp = $_FILES['content_package']['tmp_name'] ?? '';
    if (!$tmp || !is_uploaded_file($tmp)) {
        dci_trasparenza_transfer_notice('error', __('Seleziona un pacchetto contenuti JSON.', 'design_comuni_italia'));
    }
    if (filesize($tmp) > 100 * MB_IN_BYTES) {
        dci_trasparenza_transfer_notice('error', __('Il pacchetto contenuti supera il limite di sicurezza di 100 MB.', 'design_comuni_italia'));
    }
    $data = json_decode(file_get_contents($tmp), true);
    $valid = dci_trasparenza_transfer_validate_content($data);
    if (is_wp_error($valid)) {
        dci_trasparenza_transfer_notice('error', $valid->get_error_message());
    }
    $result = dci_trasparenza_transfer_import_content_data(
        $data,
        wp_unslash($_POST['import_post_types'] ?? array()),
        wp_unslash($_POST['import_term_ids'] ?? array())
    );
    if (is_wp_error($result)) {
        dci_trasparenza_transfer_notice('error', $result->get_error_message());
    }
    dci_trasparenza_transfer_notice('success', sprintf(__('Importati %1$d contenuti e %2$d categorie. Backup: %3$s', 'design_comuni_italia'), $result['posts'], $result['terms'], $result['backup']));
}
add_action('admin_post_dci_trasparenza_import_content', 'dci_trasparenza_transfer_import_content');

function dci_trasparenza_transfer_safe_zip_path($path) {
    return $path !== '' && false === strpos($path, '..') && 0 !== strpos($path, '/') && false === strpos($path, '\\');
}

function dci_trasparenza_transfer_import_media() {
    if (!dci_trasparenza_transfer_allowed()) {
        wp_die(esc_html__('Operazione non consentita.', 'design_comuni_italia'), '', array('response' => 403));
    }
    check_admin_referer('dci_trasparenza_import_media');
    if (!class_exists('ZipArchive')) {
        dci_trasparenza_transfer_notice('error', __('Estensione PHP ZipArchive non disponibile.', 'design_comuni_italia'));
    }
    $tmp = $_FILES['media_package']['tmp_name'] ?? '';
    if (!$tmp || !is_uploaded_file($tmp)) {
        dci_trasparenza_transfer_notice('error', __('Seleziona un pacchetto file ZIP.', 'design_comuni_italia'));
    }
    $zip = new ZipArchive();
    if (true !== $zip->open($tmp)) {
        dci_trasparenza_transfer_notice('error', __('Pacchetto ZIP non leggibile.', 'design_comuni_italia'));
    }
    $total_uncompressed = 0;
    for ($index = 0; $index < $zip->numFiles; $index++) {
        $stat = $zip->statIndex($index);
        $entry_name = (string) ($stat['name'] ?? '');
        $entry_size = (int) ($stat['size'] ?? 0);
        if (!dci_trasparenza_transfer_safe_zip_path($entry_name) || $entry_size > 512 * MB_IN_BYTES) {
            $zip->close();
            dci_trasparenza_transfer_notice('error', __('Il pacchetto contiene un file non sicuro o troppo grande.', 'design_comuni_italia'));
        }
        $total_uncompressed += $entry_size;
        if ($total_uncompressed > 2 * GB_IN_BYTES) {
            $zip->close();
            dci_trasparenza_transfer_notice('error', __('Il pacchetto supera il limite di sicurezza di 2 GB non compressi.', 'design_comuni_italia'));
        }
    }
    $manifest_raw = $zip->getFromName('manifest.json');
    $manifest = $manifest_raw ? json_decode($manifest_raw, true) : null;
    if (!is_array($manifest) || ($manifest['format'] ?? '') !== 'dci-trasparenza-media' || 1 !== (int) ($manifest['version'] ?? 0)) {
        $zip->close();
        dci_trasparenza_transfer_notice('error', __('Manifest del pacchetto file non valido.', 'design_comuni_italia'));
    }
    $transfer_id = sanitize_key($manifest['transfer_id']);
    $base_dir = dci_trasparenza_transfer_dir('imported/' . $transfer_id);
    wp_mkdir_p($base_dir);
    $uploads = wp_upload_dir();
    $map = (array) get_option('dci_trasparenza_media_map_' . $transfer_id, array());

    foreach ((array) $manifest['attachments'] as $attachment) {
        $source_id = (int) ($attachment['source_id'] ?? 0);
        $attachment_dir = trailingslashit($base_dir) . $source_id;
        wp_mkdir_p($attachment_dir);
        $main_path = '';
        foreach ((array) ($attachment['files'] ?? array()) as $file) {
            $zip_path = (string) ($file['path'] ?? '');
            if (!dci_trasparenza_transfer_safe_zip_path($zip_path)) {
                $zip->close();
                dci_trasparenza_transfer_notice('error', __('Il pacchetto contiene un percorso non sicuro.', 'design_comuni_italia'));
            }
            $contents = $zip->getFromName($zip_path);
            if (false === $contents || hash('sha256', $contents) !== ($file['sha256'] ?? '')) {
                $zip->close();
                dci_trasparenza_transfer_notice('error', __('Checksum di un file non valido.', 'design_comuni_italia'));
            }
            $destination = trailingslashit($attachment_dir) . sanitize_file_name($file['name']);
            file_put_contents($destination, $contents);
            if (!empty($file['main'])) {
                $main_path = $destination;
            }
        }
        if (!$main_path) {
            continue;
        }
        $post_data = (array) ($attachment['post'] ?? array());
        $post_data['post_type'] = 'attachment';
        $post_data['post_status'] = 'inherit';
        $post_data['post_parent'] = 0;
        $new_id = wp_insert_attachment(wp_slash($post_data), $main_path, 0, true);
        if (is_wp_error($new_id)) {
            $zip->close();
            dci_trasparenza_transfer_notice('error', $new_id->get_error_message());
        }
        update_attached_file($new_id, $main_path);
        foreach ((array) ($attachment['meta'] ?? array()) as $key => $values) {
            if (in_array($key, array('_wp_attached_file', '_wp_attachment_metadata'), true)) {
                continue;
            }
            delete_post_meta($new_id, $key);
            foreach ((array) $values as $value) {
                add_post_meta($new_id, $key, $value);
            }
        }
        if (is_array($attachment['metadata'] ?? null)) {
            $metadata = $attachment['metadata'];
            $metadata['file'] = _wp_relative_upload_path($main_path);
            wp_update_attachment_metadata($new_id, $metadata);
        }
        update_post_meta($new_id, '_dci_trasparenza_transfer_id', $transfer_id);
        update_post_meta($new_id, '_dci_trasparenza_source_attachment_id', $source_id);
        $map[$source_id] = (int) $new_id;
    }
    $zip->close();
    update_option('dci_trasparenza_media_map_' . $transfer_id, $map, false);
    $part = max(1, (int) ($manifest['part'] ?? 1));
    $total_parts = max(1, (int) ($manifest['total_parts'] ?? 1));
    dci_trasparenza_transfer_notice(
        'success',
        sprintf(
            __('Importata la parte %1$d di %2$d. Allegati complessivamente disponibili: %3$d.', 'design_comuni_italia'),
            $part,
            $total_parts,
            count($map)
        )
    );
}
add_action('admin_post_dci_trasparenza_import_media', 'dci_trasparenza_transfer_import_media');

function dci_trasparenza_transfer_admin_menu() {
    if (dci_trasparenza_transfer_allowed()) {
        add_management_page(
            __('Trasferimento Trasparenza', 'design_comuni_italia'),
            __('Trasferimento Trasparenza', 'design_comuni_italia'),
            'manage_options',
            'dci-trasparenza-transfer',
            'dci_trasparenza_transfer_admin_page'
        );
    }
}
add_action('admin_menu', 'dci_trasparenza_transfer_admin_menu');

function dci_trasparenza_transfer_admin_page() {
    if (!dci_trasparenza_transfer_allowed()) {
        wp_die(esc_html__('Operazione non consentita.', 'design_comuni_italia'), '', array('response' => 403));
    }
    $notice = get_transient('dci_trasparenza_transfer_notice_' . get_current_user_id());
    delete_transient('dci_trasparenza_transfer_notice_' . get_current_user_id());
    $post_type_labels = dci_trasparenza_transfer_post_type_labels();
    $terms = get_terms(array(
        'taxonomy'   => 'tipi_cat_amm_trasp',
        'hide_empty' => false,
        'orderby'    => 'name',
    ));
    $terms = is_wp_error($terms) ? array() : $terms;
    $terms = dci_trasparenza_transfer_order_terms_hierarchically($terms);
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Trasferimento Amministrazione Trasparente', 'design_comuni_italia'); ?></h1>
        <p><?php esc_html_e('I due pacchetti sono separati. Sul sito di destinazione importa prima i file e poi i contenuti.', 'design_comuni_italia'); ?></p>
        <?php if ($notice) : ?>
            <div class="notice notice-<?php echo esc_attr($notice[0]); ?> is-dismissible"><p><?php echo esc_html($notice[1]); ?></p></div>
        <?php endif; ?>

        <div class="card" style="max-width:900px">
            <h2><?php esc_html_e('1. Esporta', 'design_comuni_italia'); ?></h2>
            <p><?php esc_html_e('L’esportazione legge i dati senza modificarli.', 'design_comuni_italia'); ?></p>
            <form id="dci-trasparenza-export-form" method="post">
                <?php wp_nonce_field('dci_trasparenza_export'); ?>
                <p>
                    <button type="button" class="button dci-trasparenza-check-all" data-checkbox-container="dci-trasparenza-export-form" data-checked="1"><?php esc_html_e('Seleziona tutto', 'design_comuni_italia'); ?></button>
                    <button type="button" class="button dci-trasparenza-check-all" data-checkbox-container="dci-trasparenza-export-form" data-checked="0"><?php esc_html_e('Deseleziona tutto', 'design_comuni_italia'); ?></button>
                </p>
                <h3><?php esc_html_e('Tipologie', 'design_comuni_italia'); ?></h3>
                <fieldset style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:8px">
                    <?php foreach ($post_type_labels as $post_type => $label) : ?>
                        <label><input type="checkbox" name="export_post_types[]" value="<?php echo esc_attr($post_type); ?>" checked> <?php echo esc_html($label); ?></label>
                    <?php endforeach; ?>
                </fieldset>
                <h3><?php esc_html_e('Categorie degli elementi generici', 'design_comuni_italia'); ?></h3>
                <p class="description"><?php esc_html_e('Il check del padre seleziona i suoi discendenti; puoi poi escludere singoli figli senza deselezionare il padre e gli elementi pubblicati direttamente in esso.', 'design_comuni_italia'); ?></p>
                <fieldset style="max-height:300px;overflow:auto;border:1px solid #dcdcde;padding:12px">
                    <?php foreach ($terms as $term_entry) : ?>
                        <?php $term = $term_entry['term']; $depth = (int) $term_entry['depth']; ?>
                        <label style="display:block;margin-left:<?php echo esc_attr($depth * 18); ?>px"><input class="dci-trasparenza-term-checkbox" type="checkbox" name="export_term_ids[]" value="<?php echo (int) $term->term_id; ?>" data-term-id="<?php echo (int) $term->term_id; ?>" data-parent-id="<?php echo (int) $term->parent; ?>" checked> <?php echo esc_html($term->name); ?></label>
                    <?php endforeach; ?>
                </fieldset>
                <p>
                    <button class="button button-primary" formaction="<?php echo esc_url(admin_url('admin-post.php?action=dci_trasparenza_export_content')); ?>"><?php esc_html_e('Esporta contenuti JSON', 'design_comuni_italia'); ?></button>
                </p>
                <p>
                    <label>
                        <?php esc_html_e('Inizia dalla parte:', 'design_comuni_italia'); ?>
                        <input type="number" name="media_part" value="1" min="1" step="1" style="width:75px">
                    </label>
                    <button id="dci-trasparenza-export-media" class="button" formaction="<?php echo esc_url(admin_url('admin-post.php?action=dci_trasparenza_export_media')); ?>"><?php esc_html_e('Esporta e scarica tutti gli ZIP', 'design_comuni_italia'); ?></button>
                </p>
                <p id="dci-trasparenza-media-progress" class="description"><?php esc_html_e('Gli allegati vengono suddivisi in ZIP da circa 600 MB. Il pulsante crea e scarica automaticamente tutte le parti, una dopo l’altra. Un singolo allegato più grande rimane nel proprio ZIP.', 'design_comuni_italia'); ?></p>
            </form>
        </div>

        <div class="card" style="max-width:900px">
            <h2><?php esc_html_e('2. Importa file', 'design_comuni_italia'); ?></h2>
            <p><?php esc_html_e('Se l’esportazione ha creato più parti, importale tutte, una alla volta e in qualsiasi ordine, prima di importare il JSON dei contenuti.', 'design_comuni_italia'); ?></p>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="dci_trasparenza_import_media">
                <?php wp_nonce_field('dci_trasparenza_import_media'); ?>
                <input type="file" name="media_package" accept=".zip,application/zip" required>
                <?php submit_button(__('Importa file ZIP', 'design_comuni_italia'), 'secondary', 'submit', false); ?>
            </form>
        </div>

        <div class="card" style="max-width:900px;border-left:4px solid #d63638">
            <h2><?php esc_html_e('3. Sostituisci contenuti', 'design_comuni_italia'); ?></h2>
            <p><strong><?php esc_html_e('Attenzione:', 'design_comuni_italia'); ?></strong> <?php esc_html_e('questa operazione elimina e sostituisce soltanto le tipologie e le categorie di Amministrazione Trasparente. Prima viene creato un backup JSON automatico.', 'design_comuni_italia'); ?></p>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="dci_trasparenza_import_content">
                <?php wp_nonce_field('dci_trasparenza_import_content'); ?>
                <p><input id="dci-trasparenza-content-package" type="file" name="content_package" accept=".json,application/json" required></p>
                <div id="dci-trasparenza-import-scope"><p class="description"><?php esc_html_e('Seleziona il JSON per scegliere cosa sostituire.', 'design_comuni_italia'); ?></p></div>
                <p><label><?php esc_html_e('Per confermare scrivi SOSTITUISCI:', 'design_comuni_italia'); ?><br><input type="text" name="confirmation" autocomplete="off" required></label></p>
                <?php submit_button(__('Sostituisci i contenuti Trasparenza', 'design_comuni_italia'), 'delete', 'submit', false); ?>
            </form>
        </div>
    </div>
    <script>
    (function () {
        const input = document.getElementById('dci-trasparenza-content-package');
        const scope = document.getElementById('dci-trasparenza-import-scope');
        const mediaButton = document.getElementById('dci-trasparenza-export-media');
        const mediaProgress = document.getElementById('dci-trasparenza-media-progress');
        const labels = <?php echo wp_json_encode($post_type_labels); ?>;
        if (mediaButton && typeof fetch === 'function') {
            mediaButton.addEventListener('click', async function (event) {
                event.preventDefault();
                const form = mediaButton.form;
                const partInput = form ? form.querySelector('[name="media_part"]') : null;
                let part = Math.max(1, Number(partInput ? partInput.value : 1) || 1);
                let totalParts = part;
                mediaButton.disabled = true;

                try {
                    do {
                        if (partInput) partInput.value = part;
                        mediaProgress.textContent = '<?php echo esc_js(__('Creazione della parte', 'design_comuni_italia')); ?> ' + part + '…';
                        const response = await fetch(mediaButton.formAction, {
                            method: 'POST',
                            credentials: 'same-origin',
                            body: new FormData(form)
                        });
                        const contentType = response.headers.get('Content-Type') || '';
                        if (!response.ok || contentType.indexOf('application/zip') === -1) {
                            throw new Error('<?php echo esc_js(__('Il server non ha completato la creazione dello ZIP.', 'design_comuni_italia')); ?>');
                        }

                        totalParts = Math.max(part, Number(response.headers.get('X-DCI-Media-Total-Parts')) || part);
                        const disposition = response.headers.get('Content-Disposition') || '';
                        const filenameMatch = disposition.match(/filename="?([^";]+)"?/i);
                        const filename = filenameMatch ? filenameMatch[1] : 'trasparenza-file-parte-' + part + '-di-' + totalParts + '.zip';
                        const blobUrl = URL.createObjectURL(await response.blob());
                        const download = document.createElement('a');
                        download.href = blobUrl;
                        download.download = filename;
                        document.body.appendChild(download);
                        download.click();
                        download.remove();
                        setTimeout(function () { URL.revokeObjectURL(blobUrl); }, 60000);
                        mediaProgress.textContent = '<?php echo esc_js(__('Scaricata la parte', 'design_comuni_italia')); ?> ' + part + ' <?php echo esc_js(__('di', 'design_comuni_italia')); ?> ' + totalParts + '.';
                        part++;
                    } while (part <= totalParts);
                    mediaProgress.textContent = '<?php echo esc_js(__('Esportazione completata: tutte le parti sono state scaricate.', 'design_comuni_italia')); ?>';
                } catch (error) {
                    mediaProgress.textContent = error.message + ' <?php echo esc_js(__('Puoi riprendere dal numero di parte mostrato nel campo.', 'design_comuni_italia')); ?>';
                } finally {
                    mediaButton.disabled = false;
                }
            });
        }
        document.addEventListener('click', function (event) {
            const button = event.target.closest('.dci-trasparenza-check-all');
            if (!button) return;
            const container = document.getElementById(button.dataset.checkboxContainer || '');
            if (!container) return;
            const checked = button.dataset.checked === '1';
            container.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = checked;
                checkbox.indeterminate = false;
            });
        });
        function bindTermTree(container) {
            if (!container) return;
            const checkboxes = Array.from(container.querySelectorAll('.dci-trasparenza-term-checkbox'));
            const childrenByParent = new Map();
            checkboxes.forEach(checkbox => {
                const parentId = String(checkbox.dataset.parentId || '0');
                if (!childrenByParent.has(parentId)) childrenByParent.set(parentId, []);
                childrenByParent.get(parentId).push(checkbox);
            });
            function setDescendants(termId, checked) {
                const pendingParents = [String(termId || '')];
                while (pendingParents.length) {
                    const parentId = pendingParents.shift();
                    (childrenByParent.get(parentId) || []).forEach(child => {
                        child.checked = checked;
                        child.indeterminate = false;
                        pendingParents.push(String(child.dataset.termId || ''));
                    });
                }
            }
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    checkbox.indeterminate = false;
                    // La scelta modifica solo questo ramo: gli antenati possono
                    // contenere elementi propri e devono restare invariati.
                    setDescendants(checkbox.dataset.termId, checkbox.checked);
                });
            });
        }
        bindTermTree(document.getElementById('dci-trasparenza-export-form'));
        if (!input || !scope || typeof FileReader === 'undefined') return;
        input.addEventListener('change', function () {
            scope.innerHTML = '<p><?php echo esc_js(__('Analisi del pacchetto…', 'design_comuni_italia')); ?></p>';
            if (!input.files.length) return;
            const reader = new FileReader();
            reader.onload = function () {
                try {
                    const data = JSON.parse(reader.result);
                    if (data.format !== 'dci-trasparenza-content') throw new Error('invalid');
                    const packageScope = data.scope || {};
                    const types = packageScope.post_types || data.post_types || [];
                    const termIds = (packageScope.term_ids || (data.terms || []).map(term => term.source_id)).map(Number);
                    let html = '<h3><?php echo esc_js(__('Scegli cosa sostituire', 'design_comuni_italia')); ?></h3>';
                    html += '<p class="description"><?php echo esc_js(__('Le voci non selezionate resteranno intatte nel sito di destinazione.', 'design_comuni_italia')); ?></p>';
                    html += '<p><button type="button" class="button dci-trasparenza-check-all" data-checkbox-container="dci-trasparenza-import-scope" data-checked="1"><?php echo esc_js(__('Seleziona tutto', 'design_comuni_italia')); ?></button> ';
                    html += '<button type="button" class="button dci-trasparenza-check-all" data-checkbox-container="dci-trasparenza-import-scope" data-checked="0"><?php echo esc_js(__('Deseleziona tutto', 'design_comuni_italia')); ?></button></p>';
                    types.forEach(type => {
                        html += '<label style="display:block"><input type="checkbox" name="import_post_types[]" value="' + type + '" checked> ' + (labels[type] || type) + '</label>';
                    });
                    html += '<h4><?php echo esc_js(__('Categorie degli elementi generici', 'design_comuni_italia')); ?></h4>';
                    const termsById = {};
                    (data.terms || []).forEach(term => { termsById[Number(term.source_id)] = term; });
                    const selectedTermIds = new Set(termIds);
                    const childrenByParent = {};
                    termIds.forEach(id => {
                        const term = termsById[id];
                        if (!term) return;
                        const parentId = selectedTermIds.has(Number(term.parent || 0)) ? Number(term.parent || 0) : 0;
                        if (!childrenByParent[parentId]) childrenByParent[parentId] = [];
                        childrenByParent[parentId].push(id);
                    });
                    Object.keys(childrenByParent).forEach(parentId => {
                        childrenByParent[parentId].sort((first, second) => String(termsById[first].name).localeCompare(String(termsById[second].name), 'it'));
                    });
                    const orderedTermIds = [];
                    const appendBranch = function (parentId) {
                        (childrenByParent[parentId] || []).forEach(id => {
                            orderedTermIds.push(id);
                            appendBranch(id);
                        });
                    };
                    appendBranch(0);
                    orderedTermIds.forEach(id => {
                        const term = termsById[id];
                        if (!term) return;
                        let depth = 0, parent = Number(term.parent || 0), guard = 20;
                        while (parent && termsById[parent] && guard-- > 0) { depth++; parent = Number(termsById[parent].parent || 0); }
                        html += '<label style="display:block;margin-left:' + (depth * 18) + 'px"><input class="dci-trasparenza-term-checkbox" type="checkbox" name="import_term_ids[]" value="' + id + '" data-term-id="' + id + '" data-parent-id="' + Number(term.parent || 0) + '" checked> ' + String(term.name).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char])) + '</label>';
                    });
                    scope.innerHTML = html;
                    bindTermTree(scope);
                } catch (error) {
                    scope.innerHTML = '<p style="color:#b32d2e"><?php echo esc_js(__('Il file selezionato non è un pacchetto Trasparenza valido.', 'design_comuni_italia')); ?></p>';
                }
            };
            reader.readAsText(input.files[0]);
        });
    }());
    </script>
    <?php
}
