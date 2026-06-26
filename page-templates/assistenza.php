<?php
/* Template Name: Assistenza
 *
 * assistenza template file
 *
 * @package Design_Comuni_Italia
 */

function dci_enqueue_dci_assistence_script()  {
    wp_enqueue_script( 'dci-utils', get_template_directory_uri() . '/assets/js/utils.js', array(), false, true);

    $script_path = get_template_directory() . '/assets/js/assistenza.js';
    wp_enqueue_script(
        'dci-assistenza',
        get_template_directory_uri() . '/assets/js/assistenza.js',
        array(),
        file_exists($script_path) ? filemtime($script_path) : null,
        true
    );

    $variables = array(
        'url' => admin_url( 'admin-ajax.php' )
    );
    wp_localize_script('dci-assistenza', "data_assistenza", $variables);
}
add_action( 'wp_enqueue_scripts', 'dci_enqueue_dci_assistence_script' );

get_header();
?>
<main>
    <?php
        while ( have_posts() ) :
            the_post();

            $description = dci_get_meta('descrizione','_dci_page_',$post->ID);
            $privacy_url = dci_get_template_page_url('page-templates/privacy.php') ?: home_url('/page-templates/privacy');
            $area_riservata_url = dci_get_option('area_riservata') ?: wp_login_url();
            $categorie_servizio = get_terms(array (
                'taxonomy' => 'categorie_servizio',
                'orderby' => 'name',
                'order' => 'ASC',
                'hide_empty' => true,
            ));
            if (is_wp_error($categorie_servizio)) {
                $categorie_servizio = array();
            }

            $assistance_steps = array(
                1 => 'Privacy',
                2 => 'Richiesta',
                3 => 'Richiedente',
                4 => 'Riepilogo',
            );
    ?>
    <div class="container" id="main-container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <?php get_template_part("template-parts/common/breadcrumb"); ?>
            </div>
        </div>
    </div>

    <div id="assistance-form-steps">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10">
                    <div class="cmp-hero">
                        <section class="it-hero-wrapper bg-white align-items-start">
                            <div class="it-hero-text-wrapper pt-0 ps-0 pb-3 pb-lg-4">
                                <h1 class="text-black hero-title" data-element="page-name">Richiesta assistenza</h1>
                                <?php if ($description) { ?>
                                    <p class="hero-text text-paragraph mb-0"><?php echo wp_kses_post($description); ?></p>
                                <?php } ?>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>

        <div class="container report-progress-navigation">
            <?php foreach ($assistance_steps as $active_step => $active_label) { ?>
                <div class="cmp-info-progress d-flex d-none" data-progress="<?php echo esc_attr($active_step); ?>">
                    <?php foreach ($assistance_steps as $step => $label) {
                        $classes = 'info-progress-wrapper d-none d-lg-flex w-100 px-3 flex-column justify-content-end';
                        if ($step < $active_step) {
                            $classes .= ' completed';
                        } elseif ($step === $active_step) {
                            $classes .= ' step-active';
                        }
                        ?>
                        <div class="<?php echo esc_attr($classes); ?>">
                            <div class="info-progress-body d-flex justify-content-between align-self-end align-items-end w-100 py-3">
                                <span class="d-block h-100 title-medium text-uppercase"><?php echo esc_html($label); ?></span>
                                <?php if ($step < $active_step) { ?>
                                    <svg class="d-block icon icon-primary icon-sm" aria-hidden="true"><use href="#it-check"></use></svg>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="iscrizioni-header d-lg-none w-100">
                        <h2 class="step-title d-flex align-items-center justify-content-between drop-shadow">
                            <span><?php echo esc_html($active_label); ?></span>
                            <span class="step"><?php echo esc_html($active_step); ?>/4</span>
                        </h2>
                        <?php if ($active_step < 4) { ?>
                            <p class="title-xsmall mt-40 mb-3">I campi contraddistinti dal simbolo asterisco sono obbligatori</p>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>

        <div class="container">
            <div class="row report-form-layout">
                <aside class="col-12 col-lg-3 mb-4 report-side-navigation" aria-label="Indice del passaggio corrente">
                    <div class="cmp-navscroll sticky-top">
                        <nav class="navbar it-navscroll-wrapper navbar-expand-lg" aria-label="Informazioni richieste">
                            <div class="navbar-custom w-100">
                                <div class="menu-wrapper">
                                    <div class="link-list-wrapper">
                                        <div class="accordion">
                                            <div class="accordion-item">
                                                <span class="accordion-header" id="assistance-index-title">
                                                    <button
                                                        class="accordion-button pb-10 px-3"
                                                        type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#assistance-index"
                                                        aria-expanded="true"
                                                        aria-controls="assistance-index"
                                                    >
                                                        INFORMAZIONI RICHIESTE
                                                        <svg class="icon icon-xs right" aria-hidden="true"><use href="#it-expand"></use></svg>
                                                    </button>
                                                </span>
                                                <div class="progress">
                                                    <div
                                                        class="progress-bar it-navscroll-progressbar"
                                                        role="progressbar"
                                                        aria-valuenow="0"
                                                        aria-valuemin="0"
                                                        aria-valuemax="100"
                                                    ></div>
                                                </div>
                                                <div id="assistance-index" class="accordion-collapse collapse show" aria-labelledby="assistance-index-title">
                                                    <div class="accordion-body">
                                                        <ul class="link-list report-index-list" data-index-step="1">
                                                            <li class="nav-item"><a class="nav-link" href="#assistance-privacy"><span class="title-medium">Informativa sulla privacy</span></a></li>
                                                        </ul>
                                                        <ul class="link-list report-index-list d-none" data-index-step="2">
                                                            <li class="nav-item"><a class="nav-link" href="#assistance-request"><span class="title-medium">Richiesta</span></a></li>
                                                        </ul>
                                                        <ul class="link-list report-index-list d-none" data-index-step="3">
                                                            <li class="nav-item"><a class="nav-link" href="#applicant"><span class="title-medium">Dati richiedente</span></a></li>
                                                        </ul>
                                                        <ul class="link-list report-index-list d-none" data-index-step="4">
                                                            <li class="nav-item"><a class="nav-link" href="#assistance-summary"><span class="title-medium">Riepilogo</span></a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </nav>
                    </div>
                </aside>

                <div class="col-12 col-lg-8 offset-lg-1 section-wrapper">
                    <div class="steppers-content container-assistenza" aria-live="polite">
                        <div class="it-page-sections-container">
                            <section class="page-step active it-page-section" data-steps="1" id="assistance-privacy">
                                <div class="row justify-content-center">
                                    <div class="col-12 pb-40 pb-lg-60">
                                        <h2 class="title-xxlarge mb-3">Informativa sulla privacy</h2>
                                        <p class="text-paragraph mb-3 report-privacy-description">
                                            Il <?php echo esc_html(dci_get_option('nome_comune')); ?> utilizza i dati inseriti in questo modulo
                                            esclusivamente per acquisire e gestire la richiesta di assistenza e ricontattarti
                                            qualora siano necessarie ulteriori informazioni.
                                        </p>
                                        <p class="text-paragraph mb-4 report-privacy-description">
                                            Nome, cognome ed email sono necessari per identificare il richiedente e consentire le comunicazioni
                                            relative alla pratica. Il numero di telefono e&rsquo; facoltativo. I dati non saranno pubblicati e saranno trattati nel rispetto della normativa vigente.
                                        </p>
                                        <a
                                            href="<?php echo esc_url($privacy_url); ?>"
                                            class="btn btn-outline-primary report-privacy-link"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <svg class="icon icon-primary icon-sm me-2" aria-hidden="true"><use href="#it-external-link"></use></svg>
                                            Leggi l&rsquo;informativa completa
                                            <span class="visually-hidden">(si apre in una nuova finestra)</span>
                                        </a>
                                        <div class="form-check mt-4 mb-3">
                                            <div class="checkbox-body d-flex align-items-center">
                                                <input type="checkbox" id="privacy" name="privacy-field" value="1" required>
                                                <label class="title-small-semi-bold pt-1" for="privacy">
                                                    Ho letto e compreso l&rsquo;informativa sulla privacy
                                                    <span class="text-danger" aria-hidden="true">*</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="d-none page-step it-page-section" data-steps="2">
                                <div class="cmp-card mb-40" id="assistance-request">
                                    <div class="card has-bkg-grey shadow-sm p-big">
                                        <div class="card-header border-0 p-0 mb-3">
                                            <h2 class="title-xxlarge mb-1">Richiesta</h2>
                                            <p class="subtitle-small mb-0">Indica il servizio per cui vuoi ricevere assistenza.</p>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="form-wrapper bg-white p-4">
                                                <div class="select-wrapper p-0 select-partials report-select-field mb-4">
                                                    <label for="category">
                                                        Categoria di servizio <span class="text-danger" aria-hidden="true">*</span>
                                                    </label>
                                                    <select id="category" name="category" required>
                                                        <option selected value="">Seleziona categoria</option>
                                                        <?php foreach ($categorie_servizio as $categoria) { ?>
                                                            <option value="<?php echo esc_attr($categoria->term_id); ?>">
                                                                <?php echo esc_html($categoria->name); ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                                <div class="select-wrapper p-0 select-partials report-select-field mb-4">
                                                    <label for="service">
                                                        Servizio <span class="text-danger" aria-hidden="true">*</span>
                                                    </label>
                                                    <select id="service" name="service" disabled required>
                                                        <option selected value="">Scegli il servizio</option>
                                                    </select>
                                                </div>

                                                <div class="cmp-text-area p-0">
                                                    <div class="form-group">
                                                        <label for="description" class="d-block">
                                                            Dettagli <span class="text-danger" aria-hidden="true">*</span>
                                                        </label>
                                                        <textarea
                                                            class="text-area form-control"
                                                            id="description"
                                                            name="description"
                                                            rows="7"
                                                            maxlength="600"
                                                            required
                                                        ></textarea>
                                                        <span class="label">
                                                            Descrivi brevemente la richiesta. Massimo 600 caratteri.
                                                            <span id="assistance-description-counter" aria-live="polite">0/600</span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="d-none page-step it-page-section" data-steps="3">
                                <p class="subtitle-small pb-3 mb-0 d-lg-none">
                                    Hai un&rsquo;identita digitale SPID o CIE?
                                    <a class="title-small-semi-bold t-primary underline" href="<?php echo esc_url($area_riservata_url); ?>">Accedi</a>
                                </p>
                                <div class="cmp-card" id="applicant">
                                    <div class="card has-bkg-grey shadow-sm p-big">
                                        <div class="card-header border-0 p-0 mb-3">
                                            <h2 class="title-xxlarge mb-1">Dati richiedente</h2>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="form-wrapper bg-white p-4">
                                                <div class="form-group cmp-input mb-0">
                                                    <label class="cmp-input__label" for="name">
                                                        Nome <span class="text-danger" aria-hidden="true">*</span>
                                                    </label>
                                                    <input type="text" class="form-control" id="name" name="name" autocomplete="given-name" required>
                                                </div>
                                                <div class="form-group cmp-input mb-0">
                                                    <label class="cmp-input__label" for="surname">
                                                        Cognome <span class="text-danger" aria-hidden="true">*</span>
                                                    </label>
                                                    <input type="text" class="form-control" id="surname" name="surname" autocomplete="family-name" required>
                                                </div>
                                                <div class="form-group cmp-input mb-0">
                                                    <label class="cmp-input__label" for="email">
                                                        Email <span class="text-danger" aria-hidden="true">*</span>
                                                    </label>
                                                    <input type="email" class="form-control" id="email" name="email" autocomplete="email" required>
                                                    <span class="form-text cmp-input__text">Riceverai qui eventuali comunicazioni sulla richiesta.</span>
                                                </div>
                                                <div class="form-group cmp-input mb-0">
                                                    <label class="cmp-input__label" for="phone">Telefono</label>
                                                    <input type="tel" class="form-control" id="phone" name="phone" autocomplete="tel" maxlength="30">
                                                    <span class="form-text cmp-input__text">Facoltativo. Inserisci anche il prefisso, se necessario.</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="d-none page-step it-page-section" data-steps="4">
                                <div class="mt-2" id="assistance-summary">
                                    <h2 class="title-xxlarge mb-3">Riepilogo della richiesta</h2>
                                    <p class="text-paragraph mb-4">Controlla i dati prima dell&rsquo;invio. Puoi tornare direttamente alla sezione da modificare.</p>

                                    <div class="cmp-card mb-4">
                                        <div class="card has-bkg-grey shadow-sm p-big">
                                            <div class="card-header border-0 p-0 mb-3 d-flex justify-content-between align-items-start">
                                                <h3 class="title-large-semi-bold mb-0">Richiesta</h3>
                                                <button type="button" class="btn btn-link p-0 report-edit-step" data-edit-step="2">Modifica</button>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="cmp-info-summary bg-white p-3 p-lg-4">
                                                    <div class="single-line-info border-light">
                                                        <div class="text-paragraph-small">Categoria</div>
                                                        <p class="data-text" id="review-category"></p>
                                                    </div>
                                                    <div class="single-line-info border-light">
                                                        <div class="text-paragraph-small">Servizio</div>
                                                        <p class="data-text" id="review-service"></p>
                                                    </div>
                                                    <div class="single-line-info border-light">
                                                        <div class="text-paragraph-small">Dettagli</div>
                                                        <p class="data-text report-review-multiline" id="review-description"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="cmp-card mb-4">
                                        <div class="card has-bkg-grey shadow-sm p-big">
                                            <div class="card-header border-0 p-0 mb-3 d-flex justify-content-between align-items-start">
                                                <h3 class="title-large-semi-bold mb-0">Richiedente</h3>
                                                <button type="button" class="btn btn-link p-0 report-edit-step" data-edit-step="3">Modifica</button>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="cmp-info-summary bg-white p-3 p-lg-4">
                                                    <div class="single-line-info border-light">
                                                        <div class="text-paragraph-small">Nome</div>
                                                        <p class="data-text" id="review-name"></p>
                                                    </div>
                                                    <div class="single-line-info border-light">
                                                        <div class="text-paragraph-small">Cognome</div>
                                                        <p class="data-text" id="review-surname"></p>
                                                    </div>
                                                    <div class="single-line-info border-light">
                                                        <div class="text-paragraph-small">Email</div>
                                                        <p class="data-text" id="review-email"></p>
                                                    </div>
                                                    <div class="single-line-info border-light">
                                                        <div class="text-paragraph-small">Telefono</div>
                                                        <p class="data-text" id="review-phone"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <div class="cmp-nav-steps">
                            <nav class="steppers-nav" aria-label="Navigazione della richiesta di assistenza">
                                <button type="button" class="btn btn-sm steppers-btn-prev p-0 btn-back-step" disabled>
                                    <svg class="icon icon-primary icon-sm" aria-hidden="true"><use href="#it-chevron-left"></use></svg>
                                    <span class="text-button-sm t-primary">Indietro</span>
                                </button>
                                <button type="button" class="btn btn-primary btn-sm steppers-btn-confirm btn-next-step" disabled>
                                    <span class="text-button-sm">Avanti</span>
                                    <svg class="icon icon-white icon-sm" aria-hidden="true"><use href="#it-chevron-right"></use></svg>
                                </button>
                            </nav>
                            <div id="assistance-error" class="alert alert-danger mt-3 d-none" role="alert" tabindex="-1"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section id="assistance-success" class="d-none" aria-live="polite" tabindex="-1">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10">
                    <div class="report-success-heading">
                        <p class="text-uppercase title-xsmall-semi-bold t-primary mb-2">Richiesta assistenza</p>
                        <h1 class="title-xxlarge mb-2">Richiesta inviata</h1>
                        <p class="text-paragraph mb-4">
                            La richiesta di assistenza e&rsquo; stata inviata correttamente.
                        </p>
                    </div>
                    <div class="alert alert-success rounded p-4 mb-5" role="status">
                        <h2 class="title-large mb-2">Richiesta acquisita</h2>
                        <p class="mb-2">
                            Grazie. Il Comune potra&rsquo; contattarti all&rsquo;email
                            <strong id="assistance-email-recap"></strong>.
                        </p>
                        <p class="mb-0">Se serviranno ulteriori informazioni riceverai una comunicazione ai recapiti indicati.</p>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <?php get_template_part("template-parts/common/valuta-servizio"); ?>

    <?php
        $visualizza_contatto = dci_get_option('visualizzaContatto', 'footer'); // Opzione non definita nella sezione options footer
        if($visualizza_contatto == 'visible') {
            get_template_part("template-parts/common/assistenza-contatti");
        }
    ?>
    <?php
        endwhile;
    ?>
</main>

<?php
get_footer();
