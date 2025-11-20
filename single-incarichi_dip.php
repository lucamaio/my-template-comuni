<?php
/**
 * Template single – Incarichi conferiti ai dipendenti
 *
 * @package Design_Comuni_Italia
 */

get_header();
?>

<main>
<?php
while ( have_posts() ) :
    the_post();

    $prefix = '_dci_icad_';                          
    $id     = get_the_ID();

    // Meta fields
    $descrizione_breve = get_post_meta( $id, $prefix . 'descrizione_breve', true );
    $data_pubbl        = get_the_date( 'j F Y', $id );

    // Anno conferimento - gestione migliorata
    $anno_conferimento = get_post_meta(get_the_ID(), $prefix . 'anno_conferimento', true);
    $anno_conferimento_formatted = !empty($anno_conferimento) ? date_i18n('Y', intval($anno_conferimento)) : '-';

    // Compenso lordo - gestione numerica
    $compenso_raw = get_post_meta( $id, $prefix . 'compenso_lordo', true );
    $compenso_num = floatval( str_replace( [ '.', ',' ], [ '', '.' ], $compenso_raw ) );
    $compenso     = $compenso_num > 0 ? number_format( $compenso_num, 2, ',', '.' ) . '€' : 'Non specificato';

    $sogg_dichiarante = get_post_meta( $id, $prefix . 'soggetto_dichiarante', true );
    $sogg_percettore  = get_post_meta( $id, $prefix . 'soggetto_percettore',  true );
    $sogg_conferente  = get_post_meta( $id, $prefix . 'soggetto_conferente',  true );

    $dirigente_flag   = get_post_meta( $id, $prefix . 'dirigente_non_dirigente', true );

    // Data conferimento autorizzazione - gestione migliorata
    $data_aut_raw = get_post_meta( $id, $prefix . 'data_conferimento_autorizzazione', true );
    if ( ! empty( $data_aut_raw ) ) {
        // Assumiamo che sia un timestamp Unix (numero intero)
        $timestamp = intval( $data_aut_raw );
        $data_aut = $timestamp ? date_i18n( 'j F Y', $timestamp ) : '-';
    } else {
        $data_aut = '-';
    }

    $oggetto      = get_post_meta( $id, $prefix . 'oggetto_incarico', true );
    $durata       = get_post_meta( $id, $prefix . 'durata',           true );

    // Allegati
    $documenti = get_post_meta( $id, $prefix . 'allegati', true );
    ?>

    <div class="container" id="main-container">
        <div class="row">
            <div class="col px-lg-4">
                <?php get_template_part( 'template-parts/common/breadcrumb' ); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 px-lg-4 py-lg-2">
                <h1 data-audio><?php echo esc_html( get_the_title() ); ?></h1>
                <?php if ( $descrizione_breve ) : ?>
                    <p data-audio><?php echo esc_html( $descrizione_breve ); ?></p>
                <?php endif; ?>
            </div>

            <div class="col-lg-3 offset-lg-1">
                <?php 
                $inline = true; 
                get_template_part( 'template-parts/single/actions' ); 
                ?>
            </div>
        </div>

        <div class="row mt-5 mb-4">
            <div class="col-6">
                <small>Data pubblicazione:</small>
                <p class="fw-semibold font-monospace"><?php echo esc_html( $data_pubbl ); ?></p>
            </div>
            <div class="col-6">
                <small>Anno conferimento:</small>
                <p class="fw-semibold font-monospace">
                    <?php echo esc_html( $anno_conferimento_formatted ); ?>
                </p>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row border-top border-light row-column-border row-column-menu-left">
            <aside class="col-lg-4">
                <?php 
                get_template_part( 'template-parts/single/page-index', null, [
                    'descrizione_breve' => $descrizione_breve,
                    'documenti'         => $documenti,
                    'compenso'          => $compenso,
                ] ); 
                ?>
            </aside>

            <section class="col-lg-8 it-page-sections-container border-light mb-5">
                <?php if ( $descrizione_breve ) : ?>
                    <article class="it-page-section anchor-offset">
                        <h4 id="desc-breve">Descrizione breve</h4>
                        <div class="richtext-wrapper lora"><?php echo nl2br( esc_html( $descrizione_breve ) ); ?></div>
                    </article>
                <?php endif; ?>

                <article class="it-page-section anchor-offset mt-5">
                    <h4 id="dati">Dati principali</h4>
                    <div class="card card-border-top mb-0">
                        <ul class="mb-0 list-unstyled">
                            <li><strong>Soggetto dichiarante:</strong> <?php echo esc_html( $sogg_dichiarante ?: 'Non specificato' ); ?></li>
                            <li><strong>Soggetto percettore:</strong>  <?php echo esc_html( $sogg_percettore  ?: 'Non specificato' ); ?></li>
                            <li><strong>Soggetto conferente:</strong>  <?php echo esc_html( $sogg_conferente  ?: 'Non specificato' ); ?></li>
                            <li><strong>Dirigente / Non dirigente:</strong> 
                                <?php 
                                    echo $dirigente_flag === 'dirigente' ? 'Dirigente' : ( $dirigente_flag === 'non_dirigente' ? 'Non Dirigente' : '-' ); 
                                ?>
                            </li>
                            <li><strong>Data autorizzazione:</strong> <?php echo esc_html( $data_aut ); ?></li>
                            <li><strong>Durata:</strong> <?php echo esc_html( $durata ?: '-' ); ?></li>
                            <li><strong>Compenso lordo:</strong> <?php echo esc_html( $compenso ); ?></li>
                        </ul>
                    </div>
                </article>

                <article class="it-page-section anchor-offset mt-5">
                    <h4 id="oggetto">Oggetto dell’incarico</h4>
                    <p><?php echo esc_html( $oggetto ?: '-' ); ?></p>
                </article>

                <?php if ( ! empty( $documenti ) && is_array( $documenti ) ) : ?>
                    <article class="it-page-section anchor-offset mt-5">
                        <h4 id="documenti">Documenti</h4>
                        <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                            <?php foreach ( $documenti as $file_url ) : 
                                $file_id = attachment_url_to_postid( $file_url );
                                $allegato = get_post( $file_id );
                                if ( $allegato ) : 
                                    $title_allegato = $allegato->post_title;
                                    if ( strlen( $title_allegato ) > 50 ) {
                                        $title_allegato = substr( $title_allegato, 0, 50 ) . '...';
                                    }
                                    if ( preg_match( '/[A-Z]{5,}/', $title_allegato ) ) {
                                        $title_allegato = ucfirst( strtolower( $title_allegato ) );
                                    }
                            ?>
                                <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                    <svg class="icon" aria-hidden="true">
                                        <use xlink:href="#it-clip"></use>
                                    </svg>
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <a class="text-decoration-none" href="<?php echo esc_url( $file_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Scarica l'allegato <?php echo esc_attr( $allegato->post_title ); ?>" title="Scarica l'allegato <?php echo esc_attr( $allegato->post_title ); ?>">
                                                <?php echo esc_html( $title_allegato ); ?>
                                            </a>
                                        </h5>
                                    </div>
                                </div>
                            <?php 
                                endif;
                            endforeach; ?>
                        </div>
                    </article>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <?php 
    get_template_part( 'template-parts/common/valuta-servizio' ); 
    get_template_part( 'template-parts/common/assistenza-contatti' ); 
    ?>

<?php
endwhile;
?>
</main>

<?php get_footer(); ?>
