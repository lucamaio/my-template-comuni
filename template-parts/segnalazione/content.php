<?php
$disservizi = dci_get_disservizi_names();
$privacy_url = dci_get_template_page_url('page-templates/privacy.php') ?: home_url('/page-templates/privacy');
$area_riservata_url = dci_get_option('area_riservata') ?: wp_login_url();

$luoghi = get_posts(array(
    'posts_per_page' => 300,
    'fields' => 'ids',
    'post_status' => 'publish',
    'post_type' => 'luogo',
    'orderby' => 'post_title',
    'order' => 'ASC',
    'no_found_rows' => true,
    'ignore_sticky_posts' => true,
    'update_post_term_cache' => false,
));
?>
<div class="it-page-sections-container">
    <section class="page-step active it-page-section" data-steps="1" id="report-privacy">
        <div class="row justify-content-center">
            <div class="col-12 pb-40 pb-lg-60">
                <h2 class="title-xxlarge mb-3">Informativa sulla privacy</h2>
                <p class="text-paragraph mb-3 report-privacy-description">
                    Il <?php echo esc_html(dci_get_option('nome_comune')); ?> utilizza i dati inseriti in questo modulo
                    esclusivamente per acquisire e gestire la segnalazione, verificare il disservizio e ricontattarti
                    qualora siano necessarie ulteriori informazioni.
                </p>
                <p class="text-paragraph mb-4 report-privacy-description">
                    Nome, cognome ed email sono necessari per identificare il richiedente e consentire le comunicazioni
                    relative alla pratica. Il numero di telefono è facoltativo. I dati non saranno pubblicati e saranno
                    trattati nel rispetto del Regolamento (UE) 2016/679 e della normativa vigente.
                </p>
                <a
                    href="<?php echo esc_url($privacy_url); ?>"
                    class="btn btn-outline-primary report-privacy-link"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <svg class="icon icon-primary icon-sm me-2" aria-hidden="true">
                        <use href="#it-external-link"></use>
                    </svg>
                    Leggi l’informativa completa
                    <span class="visually-hidden">(si apre in una nuova finestra)</span>
                </a>
                <div class="form-check mt-4 mb-3">
                    <div class="checkbox-body d-flex align-items-center">
                        <input type="checkbox" id="privacy" name="privacy-field" value="1" required>
                        <label class="title-small-semi-bold pt-1" for="privacy">
                            Ho letto e compreso l’informativa sulla privacy
                            <span class="text-danger" aria-hidden="true">*</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="d-none page-step it-page-section" data-steps="2">
        <div class="cmp-card mb-40" id="report-location">
            <div class="card has-bkg-grey shadow-sm p-big">
                <div class="card-header border-0 p-0 mb-3">
                    <h2 class="title-xxlarge mb-1">Luogo del disservizio</h2>
                    <p class="subtitle-small mb-0">Indica dove hai riscontrato il problema.</p>
                </div>
                <div class="card-body p-0">
                    <div class="select-wrapper p-0 select-partials report-select-field">
                        <label for="luogo-disservizio">
                            Luogo <span class="text-danger" aria-hidden="true">*</span>
                        </label>
                        <select id="luogo-disservizio" name="luogo-disservizio" required>
                            <option selected value="">Seleziona un luogo</option>
                            <?php foreach ($luoghi as $luogo_id) {
                                $luogo = get_post($luogo_id);
                                if ($luogo) { ?>
                                    <option value="<?php echo esc_attr($luogo->ID); ?>">
                                        <?php echo esc_html($luogo->post_title); ?>
                                    </option>
                                <?php }
                            } ?>
                            <option value="altro">Altro luogo o luogo non presente nell’elenco</option>
                        </select>
                    </div>
                    <div class="form-group cmp-input mt-4 mb-0">
                        <label class="cmp-input__label" for="location-details">Indirizzo o punto di riferimento</label>
                        <input type="text" class="form-control" id="location-details" name="location-details" maxlength="160">
                        <span class="form-text cmp-input__text">Facoltativo. Può aiutare a individuare con precisione il problema.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="cmp-card mb-40" id="report-details">
            <div class="card has-bkg-grey shadow-sm p-big">
                <div class="card-header border-0 p-0 mb-3">
                    <h2 class="title-xxlarge mb-1">Descrizione del disservizio</h2>
                    <p class="subtitle-small mb-0">Indica la tipologia e descrivi brevemente il problema.</p>
                </div>
                <div class="card-body p-0">
                    <div class="select-wrapper p-0 select-partials mb-4 report-select-field">
                        <label for="report-type">
                            Tipologia di disservizio <span class="text-danger" aria-hidden="true">*</span>
                        </label>
                        <select id="report-type" name="report-type" required>
                            <option selected value="">Seleziona una tipologia</option>
                            <?php foreach ($disservizi as $disservizio) { ?>
                                <option value="<?php echo esc_attr($disservizio); ?>">
                                    <?php echo esc_html($disservizio); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group cmp-input mb-4">
                        <label class="cmp-input__label" for="report-reason">
                            Motivo della segnalazione <span class="text-danger" aria-hidden="true">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="report-reason"
                            name="report-reason"
                            maxlength="140"
                            required
                        >
                        <span class="form-text cmp-input__text">Ad esempio: lampione spento in via Roma.</span>
                    </div>

                    <div class="cmp-text-area p-0">
                        <div class="form-group">
                            <label for="report-description" class="d-block">Ulteriori dettagli</label>
                            <textarea
                                class="text-area form-control"
                                id="report-description"
                                name="report-description"
                                rows="7"
                                maxlength="1000"
                            ></textarea>
                            <span class="label">
                                Facoltativo. Puoi indicare cosa accade e da quanto tempo. Massimo 1000 caratteri.
                                <span id="description-counter" aria-live="polite">0/1000</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="d-none page-step it-page-section" data-steps="3">
        <p class="subtitle-small pb-3 mb-0 d-lg-none">
            Hai un’identità digitale SPID o CIE?
            <a class="title-small-semi-bold t-primary underline" href="<?php echo esc_url($area_riservata_url); ?>">Accedi</a>
        </p>
        <div class="cmp-card" id="applicant">
            <div class="card has-bkg-grey shadow-sm p-big">
                <div class="card-header border-0 p-0 mb-3">
                    <h2 class="title-xxlarge mb-1">Richiedente</h2>
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
                            <span class="form-text cmp-input__text">Riceverai qui eventuali comunicazioni sulla segnalazione.</span>
                        </div>
                        <div class="form-group cmp-input mb-0">
                            <label class="cmp-input__label" for="phone">Telefono</label>
                            <input type="tel" class="form-control" id="phone" name="phone" autocomplete="tel" maxlength="30">
                            <span class="form-text cmp-input__text">Facoltativo. Inserisci anche il prefisso, se necessario.</span>
                        </div>
                        <div class="d-none" aria-hidden="true">
                            <label for="report-website">Sito web</label>
                            <input type="text" id="report-website" name="website" tabindex="-1" autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="d-none page-step it-page-section" data-steps="4">
        <div class="mt-2" id="report-summary">
            <h2 class="title-xxlarge mb-3">Riepilogo della segnalazione</h2>
            <p class="text-paragraph mb-4">Controlla i dati prima dell’invio. Puoi tornare direttamente alla sezione da modificare.</p>

            <div class="cmp-card mb-4">
                <div class="card has-bkg-grey shadow-sm mb-0">
                    <div class="card-body p-0">
                        <div class="cmp-info-summary bg-white mb-3 p-3 p-lg-4">
                            <div class="card">
                                <div class="card-header border-bottom border-light p-0 mb-0 d-flex justify-content-between">
                                    <h3 class="title-large-semi-bold mb-3">Segnalazione</h3>
                                    <button type="button" class="btn btn-link p-0 report-edit-step" data-edit-step="2">Modifica</button>
                                </div>
                                <div class="card-body p-0">
                                    <div class="single-line-info border-light">
                                        <div class="text-paragraph-small">Luogo</div>
                                        <p class="data-text" id="review-place"></p>
                                    </div>
                                    <div class="single-line-info border-light">
                                        <div class="text-paragraph-small">Indirizzo o riferimento</div>
                                        <p class="data-text" id="review-location-details"></p>
                                    </div>
                                    <div class="single-line-info border-light">
                                        <div class="text-paragraph-small">Tipologia</div>
                                        <p class="data-text" id="review-type"></p>
                                    </div>
                                    <div class="single-line-info border-light">
                                        <div class="text-paragraph-small">Motivo</div>
                                        <p class="data-text" id="review-reason"></p>
                                    </div>
                                    <div class="single-line-info border-light">
                                        <div class="text-paragraph-small">Ulteriori dettagli</div>
                                        <p class="data-text report-review-multiline" id="review-description"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="cmp-info-summary bg-white p-3 p-lg-4 mb-0">
                            <div class="card">
                                <div class="card-header border-bottom border-light p-0 mb-0 d-flex justify-content-between">
                                    <h3 class="title-large-semi-bold mb-3">Richiedente</h3>
                                    <button type="button" class="btn btn-link p-0 report-edit-step" data-edit-step="3">Modifica</button>
                                </div>
                                <div class="card-body p-0">
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
            </div>
        </div>
    </section>
</div>
