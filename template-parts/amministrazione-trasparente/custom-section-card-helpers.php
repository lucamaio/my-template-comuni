<?php
/**
 * Helper di visualizzazione per le card delle tipologie personalizzate.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('dci_custom_section_card_text')) {
    /**
     * Normalizza i testi nelle card personalizzate senza alterare i dati salvati.
     */
    function dci_custom_section_card_text($value, $limit = 90) {
        if (is_array($value) || is_object($value)) {
            return '-';
        }

        $text = trim(wp_strip_all_tags((string) $value));
        if ($text === '') {
            return '-';
        }

        if (preg_match('/\p{Lu}{7,}/u', $text)) {
            $lower = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
            $first = function_exists('mb_substr') ? mb_substr($lower, 0, 1, 'UTF-8') : substr($lower, 0, 1);
            $rest = function_exists('mb_substr') ? mb_substr($lower, 1, null, 'UTF-8') : substr($lower, 1);
            $text = (function_exists('mb_strtoupper') ? mb_strtoupper($first, 'UTF-8') : strtoupper($first)) . $rest;
        }

        $limit = max(10, absint($limit));
        $length = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
        if ($length > $limit) {
            $slice = function_exists('mb_substr')
                ? mb_substr($text, 0, $limit, 'UTF-8')
                : substr($text, 0, $limit);
            $text = rtrim($slice) . '...';
        }

        return $text;
    }
}

if (!function_exists('dci_custom_section_card_date')) {
    function dci_custom_section_card_date($value) {
        if ($value === '' || $value === null) {
            return '-';
        }

        if (is_numeric($value)) {
            $timestamp = (int) $value;
        } else {
            $timestamp = strtotime((string) $value);
        }

        return $timestamp ? date_i18n('j F Y', $timestamp) : '-';
    }
}

if (!function_exists('dci_custom_section_attachment_id')) {
    /**
     * Risolve anche gli allegati importati che conservano nel file_list il
     * vecchio ID sorgente e l'URL del pacchetto di trasferimento.
     */
    function dci_custom_section_attachment_id($attachment_id = 0, $file_url = '') {
        static $resolved_ids = array();
        static $transfer_maps = array();

        $attachment_id = absint($attachment_id);
        $file_url = is_string($file_url) ? trim($file_url) : '';
        $cache_key = $attachment_id . '|' . $file_url;
        if (array_key_exists($cache_key, $resolved_ids)) {
            return $resolved_ids[$cache_key];
        }

        if ($file_url !== '' && preg_match(
            '~/(?:dci-trasparenza-transfer/)?imported/([a-f0-9]{32})/([0-9]+)/~i',
            (string) wp_parse_url($file_url, PHP_URL_PATH),
            $matches
        )) {
            $transfer_id = sanitize_key($matches[1]);
            $source_id = absint($matches[2]);

            if (!isset($transfer_maps[$transfer_id])) {
                $transfer_maps[$transfer_id] = array();
                global $wpdb;

                // Una sola lettura evita il precedente schema N+1 (una query per allegato importato).
                $imported_attachments = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT source_meta.post_id, source_meta.meta_value
                        FROM {$wpdb->postmeta} AS transfer_meta
                        INNER JOIN {$wpdb->postmeta} AS source_meta
                            ON source_meta.post_id = transfer_meta.post_id
                            AND source_meta.meta_key = '_dci_trasparenza_source_attachment_id'
                        INNER JOIN {$wpdb->posts} AS attachment
                            ON attachment.ID = transfer_meta.post_id
                            AND attachment.post_type = 'attachment'
                            AND attachment.post_status = 'inherit'
                        WHERE transfer_meta.meta_key = '_dci_trasparenza_transfer_id'
                            AND transfer_meta.meta_value = %s
                        ORDER BY attachment.post_date DESC, attachment.ID DESC",
                        $transfer_id
                    ),
                    ARRAY_N
                );

                foreach ($imported_attachments as $imported_attachment) {
                    $imported_id = absint($imported_attachment[0]);
                    $imported_source_id = absint($imported_attachment[1]);
                    if (
                        $imported_id > 0
                        && $imported_source_id > 0
                        && !isset($transfer_maps[$transfer_id][$imported_source_id])
                    ) {
                        $transfer_maps[$transfer_id][$imported_source_id] = $imported_id;
                    }
                }
            }

            if (!empty($transfer_maps[$transfer_id][$source_id])) {
                return $resolved_ids[$cache_key] = $transfer_maps[$transfer_id][$source_id];
            }
        }

        if ($attachment_id > 0 && get_post_type($attachment_id) === 'attachment') {
            return $resolved_ids[$cache_key] = $attachment_id;
        }

        if ($file_url !== '') {
            $url_attachment_id = (int) attachment_url_to_postid($file_url);
            if ($url_attachment_id > 0 && get_post_type($url_attachment_id) === 'attachment') {
                return $resolved_ids[$cache_key] = $url_attachment_id;
            }
        }

        return $resolved_ids[$cache_key] = 0;
    }
}

if (!function_exists('dci_custom_section_attachment_title')) {
    /**
     * Restituisce il titolo editoriale dell'allegato salvato nella Libreria media.
     * Il nome fisico del file non viene usato come etichetta se l'allegato e' noto.
     */
    function dci_custom_section_attachment_title($attachment_id = 0, $file_url = '', $fallback = '') {
        $file_url = is_string($file_url) ? trim($file_url) : '';
        $attachment_id = dci_custom_section_attachment_id($attachment_id, $file_url);

        if ($attachment_id > 0 && get_post_type($attachment_id) === 'attachment') {
            $attachment_title = trim(wp_strip_all_tags((string) get_the_title($attachment_id)));
            if ($attachment_title !== '') {
                return $attachment_title;
            }
        }

        return trim(wp_strip_all_tags((string) $fallback));
    }
}
