<?php
/**
 * Paginazione per le query personalizzate dell'Amministrazione Trasparente.
 *
 * @var array $args {
 *     @type WP_Query $query   Query da paginare.
 *     @type int      $current Pagina corrente.
 *     @type string   $page_arg Parametro GET riservato alla query.
 * }
 */

$pagination_query = $args['query'] ?? null;

if (
    !($pagination_query instanceof WP_Query)
    || (int) $pagination_query->max_num_pages < 2
) {
    return;
}

$pagination_current = isset($args['current'])
    ? max(1, absint($args['current']))
    : 1;
$pagination_page_arg = isset($args['page_arg'])
    ? sanitize_key($args['page_arg'])
    : 'paged';

if ($pagination_page_arg === '') {
    $pagination_page_arg = 'paged';
}

$pagination_url = '';
$queried_object = get_queried_object();

if ($queried_object instanceof WP_Term) {
    $term_link = get_term_link($queried_object);
    if (!is_wp_error($term_link)) {
        $pagination_url = $term_link;
    }
} elseif (get_queried_object_id()) {
    $pagination_url = get_permalink(get_queried_object_id());
}

if ($pagination_url === '') {
    $request_path = wp_parse_url(wp_unslash($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
    $pagination_url = home_url($request_path ?: '/');
}

$pagination_args = [];
foreach ($_GET as $key => $value) {
    if (
        in_array($key, ['paged', 'page', $pagination_page_arg], true)
        || !is_scalar($value)
        || $value === ''
    ) {
        continue;
    }

    $pagination_args[sanitize_key($key)] = dci_removeslashes((string) $value);
}

$pagination_base = add_query_arg($pagination_page_arg, 999999999, $pagination_url);
$pagination_links = paginate_links([
    'base'      => str_replace('999999999', '%#%', esc_url_raw($pagination_base)),
    'format'    => '',
    'current'   => $pagination_current,
    'total'     => (int) $pagination_query->max_num_pages,
    'type'      => 'array',
    'show_all'  => false,
    'end_size'  => 3,
    'mid_size'  => 1,
    'prev_text' => __('« ', 'design_comuni_italia'),
    'next_text' => __(' »', 'design_comuni_italia'),
    'add_args'  => $pagination_args,
]);

if (!is_array($pagination_links)) {
    return;
}
?>
<div class="pagination">
    <ul class="pagination">
        <?php foreach ($pagination_links as $pagination_link) { ?>
            <li class="page-item<?php echo strpos($pagination_link, 'current') !== false ? ' active' : ''; ?>">
                <?php echo wp_kses_post(str_replace('page-numbers', 'page-link', $pagination_link)); ?>
            </li>
        <?php } ?>
    </ul>
</div>
