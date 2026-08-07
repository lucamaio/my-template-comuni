<?php
/* Template Name: Amministrazione Trasparente
 *
 * Amministrazione Traparente template file
 *
 * @package Design_Comuni_Italia
 */
global $post, $with_shadow, $url_img;

if (!function_exists('dci_format_trasparenza_section_title')) {
    function dci_format_trasparenza_section_title($title)
    {
        $title = (string) $title;

        if (!preg_match('/\p{Lu}{7,}/u', $title)) {
            return $title;
        }

        $lowercase_title = mb_strtolower($title, 'UTF-8');

        return mb_strtoupper(mb_substr($lowercase_title, 0, 1, 'UTF-8'), 'UTF-8')
            . mb_substr($lowercase_title, 1, null, 'UTF-8');
    }
}

if (!function_exists('dci_trasparenza_get_seo_description')) {
    function dci_trasparenza_get_seo_description()
    {
        $nome_comune = trim((string) dci_get_option('nome_comune'));
        $ente = $nome_comune !== '' ? ' del ' . $nome_comune : '';

        return 'Consulta l’Amministrazione Trasparente' . $ente . ': atti, documenti, dati, bandi, personale, organizzazione, pagamenti e informazioni pubbliche dell’ente.';
    }
}

if (!function_exists('dci_trasparenza_get_visible_root_terms')) {
    function dci_trasparenza_get_visible_root_terms($limit = 12)
    {
        $terms = get_terms('tipi_cat_amm_trasp', array(
            'hide_empty' => false,
            'parent'     => 0,
            'orderby'    => 'ID',
            'order'      => 'ASC',
        ));

        if (is_wp_error($terms) || empty($terms)) {
            return array();
        }

        $terms = array_filter($terms, function ($term) {
            return get_term_meta($term->term_id, 'visualizza_elemento', true) == 1;
        });

        usort($terms, function ($a, $b) {
            $ordinamento_a = (int) get_term_meta($a->term_id, 'ordinamento', true);
            $ordinamento_b = (int) get_term_meta($b->term_id, 'ordinamento', true);

            if ($ordinamento_a === $ordinamento_b) {
                return strcmp($a->name, $b->name);
            }

            return $ordinamento_a <=> $ordinamento_b;
        });

        return array_slice($terms, 0, $limit);
    }
}

if (!function_exists('dci_trasparenza_print_seo_metadata')) {
    function dci_trasparenza_print_seo_metadata()
    {
        if (!is_page_template('page-templates/amministrazione-trasparente.php')) {
            return;
        }

        if (dci_get_option('ck_abilita_trasparenza') !== 'true') {
            return;
        }

        $description = dci_trasparenza_get_seo_description();
        $page_url = get_permalink();
        $page_title = wp_get_document_title();
        $root_terms = dci_trasparenza_get_visible_root_terms();
        $item_list = array();

        foreach ($root_terms as $index => $term) {
            $term_url = get_term_meta($term->term_id, 'term_url', true);
            $term_link = !empty($term_url) ? $term_url : get_term_link($term, 'tipi_cat_amm_trasp');

            if (is_wp_error($term_link)) {
                continue;
            }

            $item_list[] = array(
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => dci_format_trasparenza_section_title($term->name),
                'url' => esc_url_raw($term_link),
            );
        }

        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            '@id' => esc_url_raw($page_url) . '#amministrazione-trasparente',
            'url' => esc_url_raw($page_url),
            'name' => $page_title,
            'description' => $description,
            'isPartOf' => array(
                '@type' => 'WebSite',
                'url' => esc_url_raw(home_url('/')),
                'name' => get_bloginfo('name'),
            ),
        );

        if (!empty($item_list)) {
            $schema['mainEntity'] = array(
                '@type' => 'ItemList',
                'name' => 'Sezioni dell’Amministrazione Trasparente',
                'itemListElement' => $item_list,
            );
        }
        ?>
<meta name="description" content="<?php echo esc_attr($description); ?>">
<meta property="og:title" content="<?php echo esc_attr($page_title); ?>">
<meta property="og:description" content="<?php echo esc_attr($description); ?>">
<meta property="og:url" content="<?php echo esc_url($page_url); ?>">
<meta property="og:type" content="website">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="<?php echo esc_attr($page_title); ?>">
<meta name="twitter:description" content="<?php echo esc_attr($description); ?>">
<script type="application/ld+json"><?php echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
        <?php
    }
}
add_action('wp_head', 'dci_trasparenza_print_seo_metadata', 5);

$search_url = esc_url( home_url( '/' ));
$link_amministrazione=dci_get_option("link_ammtrasparente");
$url_img="https://saassipa.cultura.gov.it/wp-content/uploads/2020/04/amm_trasp-1024x381.png";
$trasparenza_attiva = dci_get_option("ck_abilita_trasparenza");


//Indirizza se c'è un link personalizzato, ma ignora il redirect se riporta all'amministrazione trasparente interna al sito.
if (
    isset($link_amministrazione) &&
    !empty($link_amministrazione) &&
    $link_amministrazione != null
) {
    // Rimuove lo slash finale, se presente
    $normalized_link = rtrim($link_amministrazione, '/');

    // Costruisce dinamicamente i link interni da ignorare (senza slash finale)
    $link_da_ignorare = array(
        rtrim(home_url('/amministrazione-trasparente'), '/'),
        rtrim(home_url('/index.php/amministrazione-trasparente'), '/'),
    );

    // Confronto
    if (!in_array($normalized_link, $link_da_ignorare, true)) {
        header("Location: $link_amministrazione");
       exit;
    }
}



function info(){?>

<section class="hero-img mb-20 mb-lg-50">
    <div class="container">
        <div class="row">
            <p class="text-justify">Benvenuti nella sezione dedicata all’Amministrazione Trasparente.</p>
            <p class="text-justify">
                Questa area è realizzata in ottemperanza al Decreto Legislativo 14 marzo 2013, n. 33, e successive modificazioni,
                con l’obiettivo di garantire la massima accessibilità alle informazioni concernenti l’organizzazione, l’attività e l’utilizzo delle risorse pubbliche da parte dell’amministrazione.
            </p>
            <p class="text-justify">
                La trasparenza è un principio cardine del nostro operato, volto a promuovere la legalità, l’efficienza e la partecipazione attiva dei cittadini.
                Tutti i dati e i documenti pubblicati sono consultabili in maniera chiara, ordinata e facilmente reperibile, nel rispetto dei criteri di completezza, aggiornamento e semplicità d’accesso.
            </p>
            <p class="text-justify">
                Invitiamo cittadini, professionisti e operatori del settore a utilizzare questo strumento come punto di riferimento per conoscere, controllare e interagire con l’attività amministrativa in modo consapevole e informato.
            </p>
        </div>
    </div>
</section>



<?php }

get_header();
?>
	<main>

<?php if ($trasparenza_attiva == 'true') { ?>

    <?php
    while ( have_posts() ) :
        the_post();

        $with_shadow = true;
    ?>

        <?php get_template_part("template-parts/single/hero-custom"); ?>

        <?php info(); ?>

        <?php // dci_get_template_part_server_cached("trasparenza-categorie"); // ripistino il vecchio sistema?>

        <?php  get_template_part("template-parts/amministrazione-trasparente/categorie"); ?>
        <?php
        // Se il portale gestisce solo la nostra Trasparenza in modo esterno, indirizza all'home del comune.
        $portalesoloperusoesterno = dci_get_option("ck_portalesoloperusoesterno");

        // Se è attiva la trasparenza esterna, non visualizzare questi elementi
        if ($portalesoloperusoesterno !== 'true') {

            get_template_part("template-parts/common/valuta-servizio");
            get_template_part("template-parts/common/assistenza-contatti");
        }
        ?>

    <?php endwhile; ?>

<?php } else { ?>

    <section class="section py-5">
        <div class="container">

            <div class="row justify-content-center">
                <div class="col-lg-8">

                    <div class="alert alert-warning p-5">

                        <h2 class="mb-4">
                            Amministrazione Trasparente non disponibile
                        </h2>

                        <p>
                            La licenza attualmente installata su questo portale
                            <strong>non prevede la sezione Amministrazione Trasparente.</strong>
                        </p>

                        <p class="mb-0">
                            Se ritieni che la funzionalità debba essere disponibile,
                            contatta il fornitore del portale.
                        </p>

                    </div>

                </div>
            </div>

        </div>
    </section>

<?php } ?>

</main>

<?php
get_footer();?>










