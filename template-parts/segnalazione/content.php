
<?php

    $disservizi = dci_get_disservizi_names();

    $uffici = get_posts(array(
        'posts_per_page' => -1,
        'post_type' => 'unita_organizzativa'
    ));

    $luoghi = get_posts(array(
        'posts_per_page' => -1,
        'post_type' => 'luogo'
    ));

    $months = array();
    $currentMonth = intval(date('m'));

    for ($i=0; $i < 12; $i++) {
        array_push($months, $currentMonth);
        if($currentMonth >= 12) $currentMonth = 0;
        $currentMonth++;
    }
?>
<div class="it-page-sections-container">

    <!-- Step 1 -->
    <section class="firstStep page-step active it-page-section" data-steps="1">
        <div class="row justify-content-center">
          <div class="col-12 col-lg-12 pb-40 pb-lg-80">
            <p class="text-paragraph mb-lg-4">
              Il <?php echo dci_get_option("nome_comune"); ?> gestisce i dati personali forniti e liberamente comunicati sulla base dell’articolo 13
              del Regolamento (UE) 2016/679 General data protection regulation (Gdpr) e degli articoli 13 e successive
              modifiche e integrazione del decreto legislativo (di seguito d.lgs) 267/2000 (Testo unico enti locali).
            </p>
            <p class="text-paragraph mb-0">
              Per i dettagli sul trattamento dei dati personali consulta l’
              <a href="#" class="t-primary">informativa sulla privacy.</a>
            </p>
    
            <div class="form-check mt-4 mb-3 mt-md-40 mb-lg-40">
              <div class="checkbox-body d-flex align-items-center">
                <input type="checkbox" id="privacy" name="privacy-field" value="privacy-field">
                <label class="title-small-semi-bold pt-1" for="privacy">Ho letto e compreso l’informativa sulla
                  privacy</label>
              </div>
            </div>
          </div>
        </div>
    </section>

    <!-- Step 2 -->
    <section class="d-none page-step it-page-section" data-steps="2">
        <div class="cmp-card mb-40" id="appointment-available" >
            <div class="card has-bkg-grey shadow-sm p-big">
                <div class="card-header border-0 p-0 mb-lg-30">
                    <div class="d-flex">
                    <h2 class="title-xxlarge mb-2">
                        Luogo
                    </h2>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="select-wrapper p-0 mt-1 select-partials">
                        <label for="appointment" class="visually-hidden">
                            Indica il luogo del disservizio
                        </label>
                        <select id="appointment" class="">
                            <option selected="selected" value="">
                                Indica il luogo del disservizio
                            </option>
                            <?php foreach ($luoghi as $l_id) {
                                $luogo = get_post($l_id);
                                echo '<option value="'.$luogo->ID.'">'.$luogo->post_title.'</option>';
                            } ?>
                        </select>
                    </div>
                    <div class="cmp-card-radio-list mt-4">
                        <div class="card p-3">
                            <div class="card-body p-0">
                                <div class="form-check m-0" >
                                    <fieldset id="radio-appointment">
                                        Nessun luogo disponibile
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

         <div class="cmp-card mb-40" id="reason">
            <div class="card has-bkg-grey shadow-sm p-big">
                <div class="card-header border-0 p-0 mb-lg-30 mb-3">
                    <div class="d-flex">
                        <h2 class="title-xxlarge mb-0" >Disservizio*</h2>
                    </div>
                    <p class="subtitle-small mb-0">
                        Scegli il disservizio
                    </p>
                </div>
                <div class="card-body p-0">
                    <div class="select-wrapper p-md-3 p-lg-4 pb-lg-0 select-partials">
                        <label for="motivo-appuntamento" class="visually-hidden">
                            Tipologia di Disservizio
                        </label>
                        <select id="appointment" class="">
                            <option selected="selected" value="">
                                Tipologia di Disservizio
                            </option>
                            <?php foreach ($disservizi as $d_id) {
                                $luogo = $d_id;
                                echo '<option>'.$d_id.'</option>';
                            } ?>
                        </select>
                        </div>

                        <div class="text-area-wrapper p-3 px-lg-4 pt-lg-5 pb-lg-0 bg-white">
                          
                            <div class="form-group cmp-input mb-0">
                              <label class="cmp-input__label" for="title">Titolo*</label>
                               <input type="text" class="form-control" id="title" name="title" required="">
                            </div>
                        </div>
                        <div class="cmp-text-area m-0 p-3 px-lg-4 pt-lg-5 pb-lg-4 bg-white">
                          <div class="form-group">
                            <label for="details" class="d-block">Dettagli**</label>
                            <textarea class="text-area" id="details" rows="2" required=""></textarea>
                            <span class="label">Inserire al massimo 200 caratteri</span>
                          </div>
                        
                        </div>
                </div>
            </div>
        </div>
        <div class="cmp-card">
      <div class="card has-bkg-grey shadow-sm">
        <div class="card-header border-0 p-0 mb-lg-30 m-0">
          <div class="d-flex">
                <h2 class="title-xxlarge mb-1">Autore della segnalazione</h2>
          </div>
          <p class="subtitle-small mb-0">Informazione su di te</p>
    
    
    
    
        </div>
        <div class="card-body p-0">
          
                        <div class="cmp-info-button-card mt-3">
                          <div class="card p-3 p-lg-4">
                            <div class="card-body p-0">
                              <h3 class="big-title mb-0">Mario Rossi</h3>
                        
                        
                        
                        
                              <p class="card-info">Codice Fiscale <br> <span>GLABNC72H25H501Y</span></p>
                        
                        
                              <div class="accordion-item">
                                <div class="accordion-header" id="heading-collapse-parents">
                                  <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-parents" aria-expanded="true" aria-controls="collapse-parents" data-focus-mouse="false">
                                    <span class="d-flex align-items-center">
                                    Mostra tutto
                                      <svg class="icon icon-primary icon-sm">
                                        <use href="../assets/bootstrap-italia/dist/svg/sprites.svg#it-expand"></use>
                                      </svg>
                                    </span>
                                  </button>
                              
                              
                                </div>
                                <div id="collapse-parents" class="accordion-collapse collapse show" role="region" style="">
                                  <div class="accordion-body p-0">
                                    <div class="cmp-info-summary bg-white has-border">
                                      <div class="card">
                                    
                                        <div class="card-header border-bottom border-light p-0 mb-0 d-flex justify-content-between d-flex justify-content-end">
                                          <h4 class="title-large-semi-bold mb-3">Contatti</h4>
                                          <a href="#" class="d-none text-decoration-none"><span class="text-button-sm-semi t-primary">Modifica</span></a>
                                        </div>
                                    
                                        <div class="card-body p-0">
                                          <div class="single-line-info border-light">
                                            <div class="text-paragraph-small">Telefono</div>
                                            <div class="border-light">
                                              <p class="data-text">
                                                +39 331 1234567
                                              </p>
                                    
                                    
                                    
                                            </div>
                                          </div>
                                          <div class="single-line-info border-light">
                                            <div class="text-paragraph-small">Email</div>
                                            <div class="border-light">
                                              <p class="data-text">
                                                mario.rossi@gmail.com
                                              </p>
                                    
                                    
                                    
                                            </div>
                                          </div>
                                        </div>
                                        <div class="card-footer p-0 d-none">
                                        </div>
                                      </div>
                                    </div>    </div>
                                </div>
                              </div>
                        
                        
                        
                        
                        
                        
                        
                            </div>
                          </div>
                        </div>    </div>
      </div>
    </div>
    </section>

    <!-- Step 3 -->
    <section class="d-none page-step it-page-section" data-steps="3">
        <div class="cmp-card mb-40" id="reason">
            <div class="card has-bkg-grey shadow-sm p-big">
                <div class="card-header border-0 p-0 mb-lg-30 mb-3">
                    <div class="d-flex">
                        <h2 class="title-xxlarge mb-0" >Motivo*</h2>
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
                        <select id="motivo-appuntamento" class=""></select>
                    </div>
                </div>
            </div>
        </div>

        <div class="cmp-card mb-40" id="details">
            <div class="card has-bkg-grey shadow-sm p-big">
                <div class="card-header border-0 p-0 mb-lg-30 m-0">
                    <div class="d-flex">
                        <h2 class="title-xxlarge mb-0" >
                            Dettagli*
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
                                class="text-area"
                                id="form-details"
                                rows="2"
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

    <!-- Step 4 -->
    <section class="d-none page-step it-page-section" data-steps="4">
        <p class="subtitle-small pb-40 mb-0 d-lg-none">
            Hai un’identità digitale SPID o CIE?
            <a class="title-small-semi-bold t-primary underline"
                href="./iscrizione-graduatoria-accedere-servizio.html"
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
                                Nome*
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
                                Cognome*
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
                                Email*
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
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Step 5 -->
    <section class="d-none page-step it-page-section" data-steps="5">
        <div class="mt-2">
            <h2 class="visually-hidden">Dettagli dell'appuntamento</h2>
            <div class="cmp-card mb-4">
                <div class="card has-bkg-grey shadow-sm mb-0">
                    <div class="card-header border-0 p-0 mb-lg-30">
                        <div class="d-flex">
                            <h3 class="subtitle-large mb-0">Riepilogo</h3>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="cmp-info-summary bg-white mb-3 mb-lg-4 p-3 p-lg-4">
                            <div class="card">
                                <div class="card-header border-bottom border-light p-0 mb-0 d-flex justify-content-between d-flex justify-content-end">
                                    <h4 class="title-large-semi-bold mb-3">
                                        Ufficio
                                    </h4>
                                    <a
                                        href="#"
                                        class="text-decoration-none"
                                        title="Modifica Ufficio"
                                        aria-label="Modifica Ufficio"
                                    >
                                        <span class="text-button-sm-semi t-primary">
                                            Modifica
                                        </span>
                                    </a>
                                </div>

                                <div class="card-body p-0">
                                    <div class="single-line-info border-light">
                                    <div class="text-paragraph-small">
                                        Tipologia ufficio
                                    </div>
                                    <div class="border-light">
                                        <p class="data-text" id="review-office"></p>
                                    </div>
                                    </div>
                                    <div class="single-line-info border-light">
                                    <div class="text-paragraph-small">
                                        Municipalità
                                    </div>
                                    <div class="border-light">
                                        <p class="data-text" id="review-place"></p>
                                    </div>
                                    </div>
                                </div>
                                <div class="card-footer p-0"></div>
                            </div>
                        </div>
                        <div class="cmp-info-summary bg-white mb-3 mb-lg-4 p-3 p-lg-4">
                            <div class="card">
                                <div class="card-header border-bottom border-light p-0 mb-0 d-flex justify-content-between d-flex justify-content-end">
                                    <h4 class="title-large-semi-bold mb-3">
                                        Data e orario
                                    </h4>
                                    <a
                                        href="#"
                                        class="text-decoration-none"
                                        title="Modifica Data e orario"
                                        aria-label="Modifica Data e orario"
                                    >
                                        <span class="text-button-sm-semi t-primary">
                                            Modifica
                                        </span>
                                    </a>
                                </div>

                                <div class="card-body p-0">
                                    <div class="single-line-info border-light">
                                    <div class="text-paragraph-small">Data</div>
                                    <div class="border-light">
                                        <p class="data-text" id="review-date"></p>
                                    </div>
                                    </div>
                                    <div class="single-line-info border-light">
                                    <div class="text-paragraph-small">Ora</div>
                                    <div class="border-light">
                                        <p class="data-text" id="review-hour"></p>
                                    </div>
                                    </div>
                                </div>
                                <div class="card-footer p-0"></div>
                            </div>
                        </div>
                        <div class="cmp-info-summary bg-white mb-3 mb-lg-4 p-3 p-lg-4">
                            <div class="card">
                                <div class="card-header border-bottom border-light p-0 mb-0 d-flex justify-content-between d-flex justify-content-end">
                                    <h4 class="title-large-semi-bold mb-3">
                                        Dettagli appuntamento
                                    </h4>
                                    <a
                                        href="#"
                                        class="text-decoration-none"
                                        title="Modifica Dettagli appuntamento"
                                        aria-label="Modifica Dettagli appuntamento"
                                    >
                                        <span class="text-button-sm-semi t-primary">
                                            Modifica
                                        </span>
                                    </a
                                    >
                                </div>

                                <div class="card-body p-0">
                                    <div class="single-line-info border-light">
                                    <div class="text-paragraph-small">Motivo</div>
                                    <div class="border-light">
                                        <p class="data-text" id="review-service"></p>
                                    </div>
                                    </div>
                                    <div class="single-line-info border-light">
                                    <div class="text-paragraph-small">Dettagli</div>
                                    <div class="border-light">
                                        <p class="data-text" id="review-details"></p>
                                    </div>
                                    </div>
                                </div>
                                <div class="card-footer p-0"></div>
                            </div>
                        </div>
                        <div class="cmp-info-summary bg-white p-3 p-lg-4 mb-0">
                            <div class="card">
                                <div class="card-header border-bottom border-light p-0 mb-0 d-flex justify-content-between d-flex justify-content-end">
                                    <h4 class="title-large-semi-bold mb-3">
                                        Richiedente
                                    </h4>
                                    <a
                                        href="#"
                                        class="text-decoration-none"
                                        title="Modifica Richiedente"
                                        aria-label="Modifica Richiedente"
                                    >
                                        <span class="text-button-sm-semi t-primary">
                                            Modifica
                                        </span>
                                    </a>
                                </div>

                                <div class="card-body p-0">
                                    <div class="single-line-info border-light">
                                    <div class="text-paragraph-small">Nome</div>
                                    <div class="border-light">
                                        <p class="data-text" id="review-name"></p>
                                    </div>
                                    </div>
                                    <div class="single-line-info border-light">
                                    <div class="text-paragraph-small">Cognome</div>
                                    <div class="border-light">
                                        <p class="data-text" id="review-surname"></p>
                                    </div>
                                    </div>
                                    <div class="single-line-info border-light">
                                    <div class="text-paragraph-small">Email</div>
                                    <div class="border-light">
                                        <p class="data-text" id="review-email"></p>
                                    </div>
                                    </div>
                                </div>
                                <div class="card-footer p-0"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>