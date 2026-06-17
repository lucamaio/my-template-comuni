<?php
global $load_card_type;

$per_page = 9;

$query = isset($_GET['search'])
    ? sanitize_text_field($_GET['search'])
    : '';

$page = isset($_GET['pagina'])
    ? max(1, intval($_GET['pagina']))
    : 1;

$args = array(
    'post_type'           => 'evento',
    'post_status'         => 'publish',
    'posts_per_page'      => $per_page,
    'paged'               => $page,
    's'                   => $query,
    'orderby'             => 'meta_value',
    'order'               => 'ASC',
    'meta_key'            => '_dci_evento_data_orario_inizio',
    'ignore_sticky_posts' => true
);

$the_query = new WP_Query($args);
?>

<div class="bg-card bg-grey-card py-5">

    <form role="search" method="get">

        <div class="container">

            <h2 class="title-xxlarge mb-4">
                Esplora tutti gli eventi
            </h2>

            <div class="cmp-input-search mb-4">

                <div class="input-group">

                    <input
                        type="search"
                        class="form-control"
                        placeholder="Cerca per parola chiave"
                        name="search"
                        value="<?php echo esc_attr($query); ?>">

                    <button class="btn btn-primary" type="submit">
                        Cerca
                    </button>

                </div>

                <p class="mt-3">
                    <?php echo $the_query->found_posts; ?> eventi trovati
                </p>

            </div>

            <div class="row g-4">

                <?php if ($the_query->have_posts()) : ?>

                    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>

                        <?php
                        $load_card_type = 'evento';
                        get_template_part('template-parts/evento/card-full');
                        ?>

                    <?php endwhile; ?>

                <?php else : ?>

                    <div class="col-12">
                        Nessun evento trovato.
                    </div>

                <?php endif; ?>

            </div>

            <?php if ($the_query->max_num_pages > 1) : ?>

                <div class="text-center mt-5">

                    <?php
                    $base_url = strtok($_SERVER['REQUEST_URI'], '?');

                    for ($i = 1; $i <= $the_query->max_num_pages; $i++) {

                        $url = add_query_arg(array(
                            'pagina' => $i,
                            'search' => $query
                        ), $base_url);

                        if ($i == $page) {

                            echo '<span class="btn btn-primary mx-1">' . $i . '</span>';

                        } else {

                            echo '<a class="btn btn-outline-primary mx-1" href="' .
                                esc_url($url) .
                                '">' .
                                $i .
                                '</a>';
                        }
                    }
                    ?>

                </div>

            <?php endif; ?>

        </div>

    </form>

</div>

<?php wp_reset_postdata(); ?>
