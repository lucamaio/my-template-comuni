<?php
$footer_address = dci_get_option('contatti_indirizzo', 'footer');
$footer_cf_piva = dci_get_option('contatti_CF_PIVA', 'footer');
$footer_pec = dci_get_option('contatti_PEC', 'footer');
$footer_centralino = dci_get_option('centralino_unico', 'footer');
$footer_numero_verde = dci_get_option('numero_verde', 'footer');
$footer_sms = dci_get_option('SMS_Whatsapp', 'footer');
$footer_dpo = dci_get_option('dpo_email', 'footer');
$urp_office_id = dci_get_option('contatti_URP', 'footer');
$ente_name = dci_get_option('nome_comune') ?: get_bloginfo('name');
$contacts_page = isset($_GET['contatti_page']) ? max(1, (int) $_GET['contatti_page']) : 1;

if (!function_exists('dci_organigramma_normalize_list')) {
    function dci_organigramma_normalize_list($value)
    {
        if (empty($value)) {
            return [];
        }

        return is_array($value) ? array_values(array_filter($value)) : [$value];
    }
}

if (!function_exists('dci_organigramma_collect_contacts')) {
    function dci_organigramma_collect_contacts($post_id)
    {
        $contact_ids = dci_organigramma_normalize_list(dci_get_meta('contatti', '_dci_unita_organizzativa_', $post_id));
        $contacts = [
            'telefono' => [],
            'email' => [],
            'pec' => [],
            'indirizzo' => [],
            'url' => [],
        ];

        foreach ($contact_ids as $contact_id) {
            $full_contact = dci_get_full_punto_contatto($contact_id);
            if (!is_array($full_contact)) {
                continue;
            }

            foreach ($contacts as $key => $values) {
                if (!empty($full_contact[$key]) && is_array($full_contact[$key])) {
                    $contacts[$key] = array_merge($contacts[$key], array_filter($full_contact[$key]));
                }
            }
        }

        $sede_principale = dci_get_meta('sede_principale', '_dci_unita_organizzativa_', $post_id);
        if (!empty($sede_principale)) {
            $indirizzo = dci_get_meta('indirizzo', '_dci_luogo_', $sede_principale);
            if (!empty($indirizzo)) {
                array_unshift($contacts['indirizzo'], $indirizzo);
            }
        }

        foreach ($contacts as $key => $values) {
            $contacts[$key] = array_values(array_unique(array_filter($values)));
        }

        return $contacts;
    }
}

$urp_contacts = !empty($urp_office_id) ? dci_organigramma_collect_contacts($urp_office_id) : [
    'telefono' => [],
    'email' => [],
    'pec' => [],
    'indirizzo' => [],
    'url' => [],
];

$office_query = new WP_Query([
    'post_type' => 'unita_organizzativa',
    'posts_per_page' => 10,
    'paged' => $contacts_page,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC',
    'tax_query' => [
        [
            'taxonomy' => 'tipi_unita_organizzativa',
            'field' => 'slug',
            'terms' => ['ufficio'],
        ],
    ],
]);
?>

<style>
    .dci-organigramma {
        color: #17324d;
    }

    .dci-organigramma__intro {
        margin-bottom: 2.5rem;
    }

    .dci-organigramma__intro h2 {
        margin-bottom: 1rem;
    }

    .dci-organigramma__meta p {
        margin-bottom: .5rem;
        line-height: 1.55;
    }

    .dci-organigramma__grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .dci-organigramma__card {
        background: #fff;
        border: 1px solid #e4eaf1;
        border-left: 4px solid #7db7e8;
        box-shadow: 0 .125rem .25rem rgba(23, 50, 77, .06);
        padding: 1rem 1rem .9rem;
        min-width: 0;
    }

    .dci-organigramma__card-title {
        font-size: 1.05rem;
        line-height: 1.3;
        margin-bottom: .75rem;
    }

    .dci-organigramma__card-line {
        margin-bottom: .45rem;
        font-size: .95rem;
        line-height: 1.5;
        color: #455a64;
        word-break: break-word;
    }

    .dci-organigramma__card-line:last-child {
        margin-bottom: 0;
    }

    .dci-organigramma__card-line strong {
        color: #17324d;
    }

    .dci-organigramma__pagination {
        margin-top: 2rem;
    }

    .dci-organigramma__pagination .pagination {
        justify-content: center;
    }

    @media (max-width: 991.98px) {
        .dci-organigramma__grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .dci-organigramma__grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="dci-organigramma">
    <section class="dci-organigramma__intro">
        <h2 class="title-xlarge"><?php echo esc_html($ente_name); ?> - contatti</h2>
        <div class="dci-organigramma__meta">
            <?php if (!empty($footer_address)) { ?>
                <p><?php echo esc_html($footer_address); ?></p>
            <?php } ?>

            <?php if (!empty($footer_cf_piva)) { ?>
                <p><?php echo esc_html($footer_cf_piva); ?></p>
            <?php } ?>

            <?php if (!empty($footer_centralino) || !empty($footer_numero_verde) || !empty($footer_sms)) { ?>
                <p><strong>Riferimenti telefonici</strong></p>
                <?php if (!empty($footer_centralino)) { ?>
                    <p>Telefono: <a class="text-decoration-none" href="tel:<?php echo esc_attr($footer_centralino); ?>"><?php echo esc_html($footer_centralino); ?></a></p>
                <?php } ?>
                <?php if (!empty($footer_numero_verde)) { ?>
                    <p>Numero verde: <a class="text-decoration-none" href="tel:<?php echo esc_attr($footer_numero_verde); ?>"><?php echo esc_html($footer_numero_verde); ?></a></p>
                <?php } ?>
                <?php if (!empty($footer_sms)) { ?>
                    <p>SMS e Whatsapp: <?php echo esc_html($footer_sms); ?></p>
                <?php } ?>
            <?php } ?>

            <?php if (!empty($urp_contacts['email'])) { ?>
                <p>E-mail: <a class="text-decoration-none" href="mailto:<?php echo esc_attr($urp_contacts['email'][0]); ?>"><?php echo esc_html($urp_contacts['email'][0]); ?></a></p>
            <?php } elseif (!empty($footer_dpo)) { ?>
                <p>E-mail: <a class="text-decoration-none" href="mailto:<?php echo esc_attr($footer_dpo); ?>"><?php echo esc_html($footer_dpo); ?></a></p>
            <?php } ?>

            <?php if (!empty($footer_pec)) { ?>
                <p>Posta Elettronica Certificata (PEC): <a class="text-decoration-none" href="mailto:<?php echo esc_attr($footer_pec); ?>"><?php echo esc_html($footer_pec); ?></a></p>
            <?php } elseif (!empty($urp_contacts['pec'])) { ?>
                <p>Posta Elettronica Certificata (PEC): <a class="text-decoration-none" href="mailto:<?php echo esc_attr($urp_contacts['pec'][0]); ?>"><?php echo esc_html($urp_contacts['pec'][0]); ?></a></p>
            <?php } ?>
        </div>
    </section>

    <section>
        <h2 class="title-xlarge mb-4">Uffici</h2>
        <div class="dci-organigramma__grid">
            <?php if ($office_query->have_posts()) {
                while ($office_query->have_posts()) {
                    $office_query->the_post();
                    $office_contacts = dci_organigramma_collect_contacts(get_the_ID());
                    ?>
                    <article class="dci-organigramma__card">
                        <h3 class="dci-organigramma__card-title">
                            <a class="text-decoration-none" href="<?php echo esc_url(get_permalink()); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h3>

                        <?php if (!empty($office_contacts['indirizzo'])) { ?>
                            <p class="dci-organigramma__card-line">
                                <strong>Indirizzo:</strong>
                                <?php echo esc_html($office_contacts['indirizzo'][0]); ?>
                            </p>
                        <?php } ?>

                        <?php if (!empty($office_contacts['telefono'])) { ?>
                            <p class="dci-organigramma__card-line">
                                <strong>Telefono:</strong>
                                <a class="text-decoration-none" href="tel:<?php echo esc_attr($office_contacts['telefono'][0]); ?>">
                                    <?php echo esc_html($office_contacts['telefono'][0]); ?>
                                </a>
                            </p>
                        <?php } ?>

                        <?php if (!empty($office_contacts['email'])) { ?>
                            <p class="dci-organigramma__card-line">
                                <strong>Email:</strong>
                                <a class="text-decoration-none" href="mailto:<?php echo esc_attr($office_contacts['email'][0]); ?>">
                                    <?php echo esc_html($office_contacts['email'][0]); ?>
                                </a>
                            </p>
                        <?php } ?>

                        <?php if (!empty($office_contacts['pec'])) { ?>
                            <p class="dci-organigramma__card-line">
                                <strong>PEC:</strong>
                                <a class="text-decoration-none" href="mailto:<?php echo esc_attr($office_contacts['pec'][0]); ?>">
                                    <?php echo esc_html($office_contacts['pec'][0]); ?>
                                </a>
                            </p>
                        <?php } ?>

                        <?php if (!empty($office_contacts['url'])) { ?>
                            <p class="dci-organigramma__card-line">
                                <strong>Sito web:</strong>
                                <a class="text-decoration-none" href="<?php echo esc_url($office_contacts['url'][0]); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php echo esc_html($office_contacts['url'][0]); ?>
                                </a>
                            </p>
                        <?php } ?>

                        <?php if (empty($office_contacts['indirizzo']) && empty($office_contacts['telefono']) && empty($office_contacts['email']) && empty($office_contacts['pec']) && empty($office_contacts['url'])) { ?>
                            <p class="dci-organigramma__card-line">Nessun contatto disponibile.</p>
                        <?php } ?>
                    </article>
                <?php }
            } else { ?>
                <p>Nessun ufficio disponibile.</p>
            <?php } ?>
        </div>
        <?php if ((int) $office_query->max_num_pages > 1) { ?>
            <nav class="pagination-wrapper justify-content-center dci-organigramma__pagination" aria-label="Paginazione contatti">
                <div class="pagination">
                    <ul class="pagination">
                        <?php
                        $current_url = get_term_link(get_queried_object());
                        for ($page = 1; $page <= (int) $office_query->max_num_pages; $page++) {
                            $page_url = add_query_arg('contatti_page', $page, $current_url);
                            ?>
                            <li class="page-item<?php echo $page === $contacts_page ? ' active' : ''; ?>">
                                <?php if ($page === $contacts_page) { ?>
                                    <span class="page-link" aria-current="page"><?php echo (int) $page; ?></span>
                                <?php } else { ?>
                                    <a class="page-link" href="<?php echo esc_url($page_url); ?>"><?php echo (int) $page; ?></a>
                                <?php } ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </nav>
        <?php } ?>
    </section>
</div>

<?php wp_reset_postdata(); ?>
