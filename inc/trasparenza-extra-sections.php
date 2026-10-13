<?php
/** Confronto condiviso delle voci extra, senza letture o scritture nel database. */
if (!defined('ABSPATH')) {
    exit;
}

function dci_at_extra_normalize_name($name) {
    $name = str_replace(array('’', '‘'), "'", (string) $name);
    $name = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $name)), 'UTF-8');
    $aliases = array(
        'concorsi' => 'bandi di concorso',
        "cessati dall'incarico" => 'amministratori cessati',
        'titolari di incarichi politici, di amministrazione, di direzione o di governo'
            => 'titolari di incarichi politici di amministrazione di direzione o di governo',
    );
    return $aliases[$name] ?? $name;
}

/** Accetta anche la precedente collocazione dei cessati sotto gli incarichi politici. */
function dci_at_extra_canonical_path($path) {
    if (count($path) >= 3 && 'organizzazione' === $path[0]
        && 'titolari di incarichi politici di amministrazione di direzione o di governo' === $path[1]
        && 'amministratori cessati' === $path[2]) {
        array_splice($path, 1, 1);
    }
    return $path;
}

function dci_at_extra_path_is_excluded($path) {
    // Solo le denominazioni richieste: altri collegamenti restano verificabili.
    if (in_array(end($path), array(
        'normativa',
        'link bdncp',
        'link alla banca dati nazionale dei contratti pubblici bdncp',
        'consulta i bilanci per anno',
        'consultazione dati bdap',
        'Banca dati incarichi di consulenza – PerlaPA' 
    ), true)) {
        return true;
    }
    // Il contenitore resta verificato; tutte le sue sottovoci sono specifiche dell'ente.
    return count($path) > 2 && 'bandi di gara e contratti' === $path[0]
        && 'procedimenti fino al 31/12/2023' === $path[1];
}

/**
 * Usa la struttura corrente e la mappa dei termini già caricata dai due pannelli.
 * I percorsi sono memoizzati e protetti da genitori mancanti o ciclici.
 */
function dci_at_extra_section_ids($nodes, $structure) {
    $expected = array();
    $index = static function ($items, $parents = array()) use (&$index, &$expected) {
        foreach ($items as $key => $value) {
            $name = is_int($key) ? $value : $key;
            $path = array_merge($parents, array(dci_at_extra_normalize_name($name)));
            $expected[json_encode(dci_at_extra_canonical_path($path))] = true;
            if (is_array($value)) {
                $index($value, $path);
            }
        }
    };
    $index($structure);
    if (!$expected) {
        return array();
    }

    $paths = array();
    $visiting = array();
    $resolve = static function ($id) use (&$resolve, &$paths, &$visiting, $nodes) {
        if (array_key_exists($id, $paths)) {
            return $paths[$id];
        }
        if (!isset($nodes[$id]) || isset($visiting[$id])) {
            return null;
        }
        $visiting[$id] = true;
        $parent_path = $nodes[$id]['parent'] ? $resolve($nodes[$id]['parent']) : array();
        $paths[$id] = null === $parent_path ? null : array_merge(
            $parent_path, array(dci_at_extra_normalize_name($nodes[$id]['name']))
        );
        unset($visiting[$id]);
        return $paths[$id];
    };
    $extra = array();
    foreach (array_keys($nodes) as $id) {
        $path = $resolve($id);
        if (null !== $path && (dci_at_extra_path_is_excluded($path)
            || isset($expected[json_encode(dci_at_extra_canonical_path($path))]))) {
            continue;
        }
        $extra[] = (int) $id;
    }
    return $extra;
}
