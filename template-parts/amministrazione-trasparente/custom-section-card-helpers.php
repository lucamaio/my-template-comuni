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
