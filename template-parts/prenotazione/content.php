<?php
    $uffici = get_posts(array(
        'posts_per_page' => -1,
        'fields' => 'ids',
        'post_status' => 'publish',
        'post_type' => 'unita_organizzativa',
        'orderby' => 'post_title',
        'order' => 'ASC',
        'no_found_rows' => true,
        'ignore_sticky_posts' => true,
        'update_post_term_cache' => false,
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => '_dci_unita_organizzativa_orario_uo',
                'compare' => 'EXISTS',
            ),
            array(
                'key' => '_dci_unita_organizzativa_orario_uo',
                'value' => '',
                'compare' => '!=',
            ),
            array(
                'key' => '_dci_unita_organizzativa_elenco_servizi_offerti',
                'compare' => 'EXISTS',
            ),
            array(
                'key' => '_dci_unita_organizzativa_elenco_servizi_offerti',
                'value' => '',
                'compare' => '!=',
            ),
            array(
                'key' => '_dci_unita_organizzativa_elenco_servizi_offerti',
                'value' => 'a:0:{}',
                'compare' => '!=',
            ),
        ),
    ));

    $months = array();
    $currentMonth = intval(date('m'));

    for ($i=0; $i < 12; $i++) {
        array_push($months, $currentMonth);
        if($currentMonth >= 12) $currentMonth = 0;
        $currentMonth++;
    }
    $privacy_url = dci_get_template_page_url('page-templates/privacy.php') ?: home_url('/page-templates/privacy');
    $area_riservata_url = dci_get_option('area_riservata') ?: wp_login_url();
?>

<style>
    #radio-appointment {
        max-height: 420px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    #radio-appointment .radio-body label {
        overflow-wrap: anywhere;
    }
</style>

<div class="it-page-sections-container">

    <!-- Step 1 -->
    <section class="firstStep page-step active it-page-section" data-steps="1" id="booking-privacy">
        <div class="row justify-content-center">
            <div class="col-12 pb-40 pb-lg-60">
                <h2 class="title-xxlarge mb-3">Informativa sulla privacy</h2>
                <p class="text-paragraph mb-3 booking-privacy-description">
                    Il <?php echo esc_html(dci_get_option('nome_comune')); ?> utilizza i dati inseriti nel modulo
                    esclusivamente per gestire la richiesta di appuntamento e comunicare eventuali aggiornamenti.
                </p>
                <p class="text-paragraph mb-4 booking-privacy-description">
                    Nome, cognome ed email sono necessari per identificare il richiedente e confermare la prenotazione.
                    I dati non saranno pubblicati e saranno trattati nel rispetto del Regolamento (UE) 2016/679 e
                    della normativa vigente.
                </p>
                <a
                    href="<?php echo esc_url($privacy_url); ?>"
                    class="btn btn-outline-primary booking-privacy-link"
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
                        <input type="checkbox" id="booking-privacy-consent" name="booking-privacy-consent" value="1" required>
                        <label class="title-small-semi-bold pt-1" for="booking-privacy-consent">
                            Ho letto e compreso l’informativa sulla privacy
                            <span class="text-danger" aria-hidden="true">*</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Step 2 -->
    <section class="d-none page-step it-page-section" data-steps="2">
        <div class="cmp-card mb-40" id="office">
            <div class="card has-bkg-grey shadow-sm p-big">
                <div class="card-header border-0 p-0 mb-lg-30">
                    <div class="d-flex">
                        <h2 class="title-xxlarge mb-0">
                            Ufficio <span class="text-danger" aria-hidden="true">*</span>
                        </h2>
                    </div>
                    <p class="subtitle-small mb-0">
                        Scegli l’ufficio a cui vuoi richiedere l’appuntamento
                    </p>
                </div>
                <div class="card-body p-0">
                    <div class="select-wrapper p-0 select-partials">
                        <label for="office-choice" class="visually-hidden">
                            Tipo di ufficio
                        </label>
                        <select id="office-choice" class="" required>
                            <option selected="selected" value="">
                                Seleziona opzione
                            </option>
                            <?php foreach ($uffici as $uo_id) {
                                $ufficio = get_post($uo_id);
                                echo '<option value="'.$ufficio->ID.'">'.$ufficio->post_title.'</option>';
                            } ?>
                        </select>
                    </div>
                    <fieldset id="place-cards-wrapper"></fieldset>
                </div>
            </div>
        </div>
    </section>

    <!-- Step 3 -->
    <section class="d-none page-step it-page-section" data-steps="3">
        <div class="cmp-card mb-40" id="appointment-available" >
            <div class="card has-bkg-grey shadow-sm p-big">
                <div class="card-header border-0 p-0 mb-lg-30">
                    <div class="d-flex">
                    <h2 class="title-xxlarge mb-2">
                        Appuntamenti disponibili <span class="text-danger" aria-hidden="true">*</span>
                    </h2>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="select-wrapper p-0 mt-1 select-partials">
                        <label for="appointment" class="visually-hidden">
                            Seleziona un mese
                        </label>
                        <select id="appointment" class="">
                            <option selected="selected" value="">
                                Seleziona un mese
                            </option>
                            <?php foreach ($months as $month) {
                                echo '<option value="'.$month.'">'.date_i18n('F', mktime(0, 0, 0, $month, 10)).'</option>';
                            } ?>
                        </select>
                    </div>
                    <div class="cmp-card-radio-list mt-4">
                        <div class="card p-3">
                            <div class="card-body p-0">
                                <div class="form-check m-0" >
                                    <fieldset id="radio-appointment">
                                        Nessunn appuntamento disponibile.
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="cmp-card mb-40" id="office-2">
            <div class="card has-bkg-grey shadow-sm p-big" >
                <div class="card-header border-0 p-0 mb-lg-30">
                    <div class="d-flex">
                        <h2 class="title-xxlarge mb-0">Ufficio</h2>
                    </div>
                </div>
                <div class="card-body p-0" id="selected-place-card"></div>
            </div>
        </div>
    </section>

    <!-- Step 4 -->
    <section class="d-none page-step it-page-section" data-steps="4">
        <div class="cmp-card mb-40" id="reason">
            <div class="card has-bkg-grey shadow-sm p-big">
                <div class="card-header border-0 p-0 mb-lg-30 mb-3">
                    <div class="d-flex">
                        <h2 class="title-xxlarge mb-0">
                            Motivo <span class="text-danger" aria-hidden="true">*</span>
                        </h2>
                    </div>
                    <p class="subtitle-small mb-0">
                        Scegli il motivo dell’appuntamento
                    </p>
                </div>
                <div class="card-body p-0">
                    <div class="select-wrapper p-0 select-partials">
                        <label for="motivo-appuntamento" class="visually-hidden">
                            Motivo dell&#x27;appuntamento
                        </label>
                        <select id="motivo-appuntamento" class="" required>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="cmp-card mb-40" id="details">
            <div class="card has-bkg-grey shadow-sm p-big">
                <div class="card-header border-0 p-0 mb-lg-30 m-0">
                    <div class="d-flex">
                        <h2 class="title-xxlarge mb-0" >
                            Dettagli <span class="text-danger" aria-hidden="true">*</span>
                        </h2>
                    </div>
                    <p class="subtitle-small mb-0 mb-3">
                        Aggiungi ulteriori dettagli
                    </p>
                </div>
                <div class="card-body p-0">
                    <div class="cmp-text-area p-0">
                        <div class="form-group">
                            <label for="form-details" class="visually-hidden">
                                Aggiungi ulteriori dettagli
                            </label>
                            <textarea
                                class="text-area form-control"
                                id="form-details"
                                rows="2"
                                required
                            ></textarea>
                            <span class="label">
                                Inserire massimo 200 caratteri
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Step 5 -->
    <section class="d-none page-step it-page-section" data-steps="5">
        <p class="subtitle-small pb-40 mb-0 d-lg-none">
            Hai un’identità digitale SPID o CIE?
            <a class="title-small-semi-bold t-primary underline"
                href="<?php echo esc_url($area_riservata_url); ?>"
            >
                Accedi
            </a>
        </p>
        <div class="cmp-card" id="applicant">
            <div class="card has-bkg-grey shadow-sm p-big">
                <div class="card-header border-0 p-0 mb-lg-30 m-0">
                    <div class="d-flex">
                        <h2 class="title-xxlarge mb-3" >
                            Richiedente
                        </h2>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="form-wrapper bg-white p-4">
                        <div class="form-group cmp-input mb-0">
                            <label class="cmp-input__label" for="name">
                                Nome <span class="text-danger" aria-hidden="true">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="name"
                                name="name"
                                required
                            />
                            <div class="d-flex">
                                <span class="form-text cmp-input__text">
                                    Inserisci il tuo nome
                                </span>
                            </div>
                        </div>

                        <div class="form-group cmp-input mb-0">
                            <label class="cmp-input__label" for="surname">
                                Cognome <span class="text-danger" aria-hidden="true">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="surname"
                                name="surname"
                                required
                            />
                            <div class="d-flex">
                                <span class="form-text cmp-input__text">
                                    Inserisci il tuo cognome
                                </span>
                            </div>
                        </div>

                        <div class="form-group cmp-input mb-0">
                            <label class="cmp-input__label" for="email">
                                Email <span class="text-danger" aria-hidden="true">*</span>
                            </label>
                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                required
                            />
                            <div class="d-flex">
                                <span class="form-text cmp-input__text">
                                    Inserisci la tua email
                                </span>
                            </div>
                        </div>

                        <div class="form-group cmp-input mb-0">
                            <label class="cmp-input__label" for="phone">
                                Telefono
                            </label>
                            <input
                                type="tel"
                                class="form-control"
                                id="phone"
                                name="phone"
                                autocomplete="tel"
                                maxlength="30"
                            />
                            <div class="d-flex">
                                <span class="form-text cmp-input__text">
                                    Facoltativo. Inserisci anche il prefisso, se necessario.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Step 6 -->
    <section class="d-none page-step it-page-section" data-steps="6">
        <div class="mt-2" id="booking-summary">
            <h2 class="title-xxlarge mb-3">Riepilogo dell’appuntamento</h2>
            <p class="text-paragraph mb-4">
                Controlla i dati prima dell’invio. Puoi tornare direttamente alla sezione da modificare.
            </p>
            <div class="cmp-card mb-4">
                <div class="card has-bkg-grey shadow-sm mb-0">
                    <div class="card-body p-0">
                        <div class="cmp-info-summary bg-white mb-3 p-3 p-lg-4">
                            <div class="card">
                                <div class="card-header border-bottom border-light p-0 mb-0 d-flex justify-content-between">
                                    <h3 class="title-large-semi-bold mb-3">Ufficio</h3>
                                    <button type="button" class="btn btn-link p-0 booking-edit-step" data-edit-step="2">
                                        Modifica
                                    </button>
                                </div>
                                <div class="card-body p-0">
                                    <div class="single-line-info border-light">
                                        <div class="text-paragraph-small">Ufficio</div>
                                        <p class="data-text" id="review-office"></p>
                                    </div>
                                    <div class="single-line-info border-light">
                                        <div class="text-paragraph-small">Sede</div>
                                        <p class="data-text" id="review-place"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="cmp-info-summary bg-white mb-3 p-3 p-lg-4">
                            <div class="card">
                                <div class="card-header border-bottom border-light p-0 mb-0 d-flex justify-content-between">
                                    <h3 class="title-large-semi-bold mb-3">Data e orario</h3>
                                    <button type="button" class="btn btn-link p-0 booking-edit-step" data-edit-step="3">
                                        Modifica
                                    </button>
                                </div>
                                <div class="card-body p-0">
                                    <div class="single-line-info border-light">
                                        <div class="text-paragraph-small">Data</div>
                                        <p class="data-text" id="review-date"></p>
                                    </div>
                                    <div class="single-line-info border-light">
                                        <div class="text-paragraph-small">Ora</div>
                                        <p class="data-text" id="review-hour"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="cmp-info-summary bg-white mb-3 p-3 p-lg-4">
                            <div class="card">
                                <div class="card-header border-bottom border-light p-0 mb-0 d-flex justify-content-between">
                                    <h3 class="title-large-semi-bold mb-3">Dettagli appuntamento</h3>
                                    <button type="button" class="btn btn-link p-0 booking-edit-step" data-edit-step="4">
                                        Modifica
                                    </button>
                                </div>
                                <div class="card-body p-0">
                                    <div class="single-line-info border-light">
                                        <div class="text-paragraph-small">Motivo</div>
                                        <p class="data-text" id="review-service"></p>
                                    </div>
                                    <div class="single-line-info border-light">
                                        <div class="text-paragraph-small">Dettagli</div>
                                        <p class="data-text booking-review-multiline" id="review-details"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="cmp-info-summary bg-white p-3 p-lg-4 mb-0">
                            <div class="card">
                                <div class="card-header border-bottom border-light p-0 mb-0 d-flex justify-content-between">
                                    <h3 class="title-large-semi-bold mb-3">Richiedente</h3>
                                    <button type="button" class="btn btn-link p-0 booking-edit-step" data-edit-step="5">
                                        Modifica
                                    </button>
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
