<?php
global $argomento;

$posts = dci_get_grouped_posts_by_term(
    'novita-evento',
    'argomenti',
    $argomento->slug,
    6
);
?>

<section id="novita">
    <div class="bg-grey-card pt-40 pt-md-100 pb-50">
        <div class="container">

            <div class="row row-title">
                <div class="col-12">
                    <h3 class="u-grey-light border-bottom border-semi-dark pb-2 pb-lg-3 mt-lg-3 title-large-semi-bold">
                        Novità
                    </h3>
                </div>
            </div>

            <div class="row pt-4 mt-lg-2 pb-lg-4">

                <?php if (!empty($posts)) : ?>
                    <?php foreach ($posts as $post) : ?>

                        <?php
                        $description     = dci_get_meta('descrizione_breve');
                        $img             = dci_get_meta('immagine');
                        $luogo_notizia   = dci_get_meta('luoghi');
                        $argomenti       = dci_get_meta('argomenti');
                        ?>

                        <?php if ($post->post_type === 'evento') : ?>

                            <?php
                            if ($start = dci_get_meta('data_orario_inizio')) {
                                $start_date_arr = explode('-', date('d-m-Y', $start));
                            }

                            if ($end = dci_get_meta('data_orario_fine')) {
                                $end_date_arr = explode('-', date('d-m-Y', $end));
                                $monthName   = date_i18n('M', mktime(0, 0, 0, $end_date_arr[1], 10));
                                $annoName    = $end_date_arr[2];
                            }

                            $url_eventi = get_permalink(get_page_by_title('Eventi'));
                            ?>

                            <div class="col-12 col-md-6 col-lg-4 d-flex mb-4">
                                <div class="card-wrapper w-100">
                                    <div class="card card-img no-after rounded h-100">

                                        <?php if ($img) : ?>
                                            <div class="img-responsive-wrapper">
                                                <div class="img-responsive img-responsive-panoramic">
                                                    <figure class="img-wrapper">
                                                        <?php dci_get_img($img); ?>
                                                    </figure>
                                                    <div class="card-calendar d-flex flex-column justify-content-center">
                                                        <span class="card-date">
                                                            <?php echo $start_date_arr[0] . '-' . $end_date_arr[0]; ?>
                                                        </span>
                                                        <span class="card-day"><?php echo esc_html($monthName); ?></span>
                                                        <span class="card-day"><?php echo esc_html($annoName); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="card-body p-4">
                                            <div class="category-top">
                                                <a class="fw-semibold text-decoration-none" href="<?php echo esc_url($url_eventi); ?>">
                                                    Eventi
                                                </a>
                                                <?php if (!empty($start_date_arr) && !empty($end_date_arr)) : ?>
                                                    <span class="data u-grey-light">
                                                        DAL <?php echo $start_date_arr[0]; ?>
                                                        AL <?php echo $end_date_arr[0] . ' ' . $monthName . ' ' . $annoName; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <h4 class="title-small-semi-bold-big mb-0">
                                                <a class="text-decoration-none" href="<?php the_permalink(); ?>">
                                                    <?php the_title(); ?>
                                                </a>
                                            </h4>

                                            <p class="pt-3 d-none d-lg-block text-paragraph-card u-grey-light">
                                                <?php echo esc_html($description); ?>
                                            </p>

                                            <?php if (is_array($luogo_notizia) && count($luogo_notizia)) : ?>
                                                <span class="data fw-normal">📍
                                                    <?php
                                                    foreach ($luogo_notizia as $luogo_id) {
                                                        $luogo_post = get_post($luogo_id);
                                                        if ($luogo_post) {
                                                            echo '<a href="' . esc_url(get_permalink($luogo_post->ID)) . '">' .
                                                                esc_html($luogo_post->post_title) .
                                                                '</a> ';
                                                        }
                                                    }
                                                    ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php else : ?>

                            <?php
                            $tipo_notizia      = get_the_terms($post->ID, 'tipi_notizia')[0] ?? null;
                            $tipo_notizia_name = $tipo_notizia->name ?? 'Notizie';
                            $tipo_notizia_link = $tipo_notizia ? get_term_link($tipo_notizia) : '#';
                            $arrdata           = dci_get_data_pubblicazione_arr('data_pubblicazione', '_dci_notizia_', $post->ID);
                            $monthName         = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
                            ?>

                            <div class="col-12 col-md-6 col-lg-4 d-flex mb-4">
                                <div class="card-wrapper w-100">
                                    <div class="card card-img no-after sm-row h-100">

                                        <?php if ($img) : ?>
                                            <div class="img-responsive-wrapper">
                                                <div class="img-responsive img-responsive-panoramic">
                                                    <figure class="img-wrapper">
                                                        <?php dci_get_img($img); ?>
                                                    </figure>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="card-body p-4">
                                            <div class="category-top">
                                                <a class="fw-semibold text-decoration-none" href="<?php echo esc_url($tipo_notizia_link); ?>">
                                                    <?php echo esc_html($tipo_notizia_name); ?>
                                                </a>
                                                <span class="data u-grey-light">
                                                    <?php echo $arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]; ?>
                                                </span>
                                            </div>

                                            <h4 class="title-small-semi-bold-big mb-0">
                                                <a class="text-decoration-none" href="<?php the_permalink(); ?>">
                                                    <?php the_title(); ?>
                                                </a>
                                            </h4>

                                            <p class="pt-3 d-none d-lg-block text-paragraph-card u-grey-light">
                                                <?php echo esc_html($description); ?>
                                            </p>

                                            <hr class="my-2">

                                            Argomenti:
                                            <?php get_template_part('template-parts/common/badges-argomenti'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php endif; ?>

                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            Al momento non sono presenti contenuti disponibili.
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <div class="row mt-lg-2">
                <div class="col-12 col-lg-3 offset-lg-9">
                    <button type="button"
                            class="btn btn-primary text-button w-100"
                            onclick="location.href='<?php echo esc_url(dci_get_template_page_url('page-templates/novita.php')); ?>'">
                        Tutte le novità
                    </button>
                </div>
            </div>

        </div>
    </div>
</section>

<style>
    .card-wrapper {
    display: flex;
    height: 100%;
}

.card {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.card-body {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.text-paragraph-card {
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
}

</style>