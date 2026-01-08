<?php
/**
 * The template for displaying archive with modern design
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#archive
 *
 * @package Design_Comuni_Italia
 */

$class = "petrol";
get_header();
?>
    <div class="container" id="main-container">
        <div class="row">
            <div class="col px-lg-4">
                <?php
                    // Ottieni il titolo dell'archivio senza prefissi
                    $archive_title = get_the_archive_title();
                    
                    // Ottieni l'URL della pagina 'Amministrazione'
                    $amministrazione_url = get_permalink(get_page_by_path('amministrazione'));
                    
                    // Verifica se il titolo dell'archivio è 'Dataset' o 'Incarichi'
                    if ($archive_title === 'Dataset' || $archive_title === 'Incarichi') {
                        // Costruisce il breadcrumb
                        echo '<br><nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="' . esc_url(home_url()) . '"><strong>Home</strong></a></li>
                                    <li class="breadcrumb-item"><a href="' . esc_url($amministrazione_url) . '"><strong>Amministrazione</strong></a></li>
                                    <li class="breadcrumb-item active" aria-current="page">' . esc_html($archive_title) . '</li>
                                </ol>
                              </nav>';                    
                    } else {
                        // Include il breadcrumb predefinito per altri casi
                        get_template_part('template-parts/common/breadcrumb');
                    }
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 px-lg-4 py-lg-2">
                <h1 data-audio> <?php the_archive_title(); ?></h1>
                <?php
                    // Visualizza la descrizione appropriata in base al titolo dell'archivio
                    if ($archive_title === 'Dataset') {
                        echo '<p>In questa sezione sono disponibili i dataset pubblicati dall\'Autorità Nazionale Anticorruzione (ANAC), contenenti informazioni dettagliate sui contratti pubblici in Italia, inclusi appalti, stazioni appaltanti e altri dati rilevanti.</p>';
                    } elseif ($archive_title === 'Incarichi') {
                        echo '<p>Questa sezione fornisce informazioni sugli obblighi di pubblicazione riguardanti i titolari di incarichi di collaborazione o consulenza, come disciplinato dall\'articolo 15 del Decreto Legislativo 33/2013.</p>';
                    }
                ?>
            </div>
            <div class="col-lg-3 offset-lg-1">
                <?php
                $inline = true;
                get_template_part('template-parts/single/actions');
                ?>
            </div>
        </div>
    </div><!-- ./main-container -->

    <!-- Content Section with Grid Layout -->
    <section class="section bg-gray-light">
        <div class="container">
            <div class="row">
                <?php if ( have_posts() ) : ?>
                    <?php while ( have_posts() ) : the_post(); ?>
                        <div class="col-md-6 col-xl-4 mb-4"> <!-- Aggiungi mb-4 per spazio tra le righe -->
                            <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
                                <div class="card no-after rounded">
                                    <div class="row g-2 g-md-0 flex-md-column">
                                        <div class="col-12 order-1 order-md-2">
                                            <div class="card-body card-img-none rounded-top">
                                                <div class="category-top cmp-list-card-img__body">
                                                    <span class="category cmp-list-card-img__body-heading-title underline">
                                                        <span class="text fw-semibold">
                                                            <?php
                                                                // Aggiungi solo la categoria, senza link o icona sopra il titolo
                                                                $terms = get_the_terms(get_the_ID(), 'category');
                                                                if ($terms && !is_wp_error($terms)) :
                                                                    $term = $terms[0]; ?>
                                                                    <span class="category-name">                                                                    
                                                                        <font color="black"><?php echo strtoupper($term->name); ?></font>
                                                                    </span>
                                                                <?php else : ?>
                                                                    <span class="category-name">
                                                                        <font color="black"><?php echo $archive_title; ?></font>
                                                                    </span>
                                                                <?php endif; ?>
                                                            <font color="grey" size="1"><span class="data"><?php echo get_the_date('d M Y'); ?></span></font>
                                                        </span>
                                                    </span>
                                                </div>
                                                <a class="text-decoration-none" href="<?php the_permalink(); ?>">
                                                    <h3 class="h5 card-title"><?php the_title(); ?></h3>
                                                </a>
                                                
                                                <p class="card-text d-none d-md-block">
                                                    <?php
                                                        // Usa l'estratto del contenuto o un breve riassunto
                                                        $description = get_the_excerpt();
                                                        if (!empty($description)) {
                                                            echo esc_html($description);
                                                        } else {
                                                            $content = wp_strip_all_tags(get_the_content());
                                                            echo !empty($content) ? wp_trim_words($content, 20, '...') : '';
                                                        }
                                                    ?>
                                                </p>
                                                <hr style="margin-bottom: 40px; width: 200px; height: 1px; background-color: grey; border: none;">
                                                <a class="read-more ps-3"
                                                   href="<?php echo esc_url(get_permalink($post->ID)); ?>"
                                                   aria-label="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" 
                                                   title="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" 
                                                   style="display: inline-flex; align-items: center; margin-top: 30px;">
                                                    <span class="text">Vai alla pagina</span>
                                                    <svg class="icon">
                                                        <use xlink:href="#it-arrow-right"></use>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>

                    <!-- Paginazione -->
                    <div class="col-12 d-flex justify-content-center">
                        <?php echo dci_bootstrap_pagination(); ?>
                    </div>

                <?php else : ?>
                    <p class="text-center">Nessun contenuto trovato.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>

