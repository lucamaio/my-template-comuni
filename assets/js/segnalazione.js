(function () {
  'use strict';

  var content = document.querySelector('.section-wrapper');
  if (!content || typeof dciSegnalazione === 'undefined') return;

  var steps = Array.prototype.slice.call(content.querySelectorAll('[data-steps]'));
  var btnNext = content.querySelector('.btn-next-step');
  var btnBack = content.querySelector('.btn-back-step');
  var errorBox = document.getElementById('report-error');
  var successBox = document.getElementById('report-success');
  var currentStep = 1;
  var submitting = false;

  function getEl(id) {
    return document.getElementById(id);
  }

  function getValue(id) {
    var el = getEl(id);
    return el ? (el.value || '').trim() : '';
  }

  function getSelectedText(id) {
    var select = getEl(id);
    if (!select || select.selectedIndex < 0 || !select.value) return '';
    return (select.options[select.selectedIndex].text || '').trim();
  }

  function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function validatePhone(phone) {
    return !phone || (/^[0-9+().\s/-]{6,30}$/.test(phone) && /[0-9]/.test(phone));
  }

  function setText(id, value, fallback) {
    var el = getEl(id);
    if (el) el.textContent = value || fallback || 'Non indicato';
  }

  function setProgress() {
    var progresses = document.querySelectorAll('[data-progress]');
    for (var i = 0; i < progresses.length; i++) {
      progresses[i].classList.toggle('d-none', Number(progresses[i].getAttribute('data-progress')) !== currentStep);
    }

    var indexes = document.querySelectorAll('[data-index-step]');
    for (var j = 0; j < indexes.length; j++) {
      indexes[j].classList.toggle('d-none', Number(indexes[j].getAttribute('data-index-step')) !== currentStep);
    }
  }

  function updateSummary() {
    setText('review-place', getSelectedText('luogo-disservizio'));
    setText('review-location-details', getValue('location-details'));
    setText('review-type', getSelectedText('report-type'));
    setText('review-reason', getValue('report-reason'));
    setText('review-description', getValue('report-description'));
    setText('review-name', getValue('name'));
    setText('review-surname', getValue('surname'));
    setText('review-email', getValue('email'));
    setText('review-phone', getValue('phone'));
  }

  function isStepValid(step) {
    if (step === 1) {
      return Boolean(getEl('privacy') && getEl('privacy').checked);
    }

    if (step === 2) {
      return Boolean(
        getValue('luogo-disservizio') &&
        getValue('report-type') &&
        getValue('report-reason')
      );
    }

    if (step === 3) {
      return Boolean(
        getValue('name') &&
        getValue('surname') &&
        validateEmail(getValue('email')) &&
        validatePhone(getValue('phone'))
      );
    }

    return true;
  }

  function updateNextState() {
    if (!btnNext) return;
    btnNext.disabled = submitting || !isStepValid(currentStep);
  }

  function updateButtons() {
    if (!btnNext || !btnBack) return;
    btnBack.disabled = submitting || currentStep === 1;

    var label = btnNext.querySelector('span');
    if (label) label.textContent = currentStep === steps.length ? 'Invia segnalazione' : 'Avanti';
    updateNextState();
  }

  function showError(message) {
    if (!errorBox) return;
    errorBox.textContent = message;
    errorBox.classList.remove('d-none');
    errorBox.focus();
  }

  function clearError() {
    if (!errorBox) return;
    errorBox.textContent = '';
    errorBox.classList.add('d-none');
  }

  function goToStep(stepNumber, shouldScroll) {
    if (stepNumber < 1 || stepNumber > steps.length) return;

    for (var i = 0; i < steps.length; i++) {
      var isActive = Number(steps[i].getAttribute('data-steps')) === stepNumber;
      steps[i].classList.toggle('d-none', !isActive);
      steps[i].classList.toggle('active', isActive);
    }

    currentStep = stepNumber;
    if (currentStep === steps.length) updateSummary();
    clearError();
    setProgress();
    updateButtons();

    if (shouldScroll !== false) {
      var heading = document.querySelector('.cmp-hero');
      if (heading) heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  function submitReport() {
    if (submitting) return;

    submitting = true;
    clearError();
    updateButtons();

    var body = new URLSearchParams();
    body.append('action', 'save_segnalazione_disservizio');
    body.append('nonce', dciSegnalazione.nonce);
    body.append('luogo_id', getValue('luogo-disservizio'));
    body.append('luogo', getSelectedText('luogo-disservizio'));
    body.append('riferimento_luogo', getValue('location-details'));
    body.append('tipologia', getSelectedText('report-type'));
    body.append('motivo', getValue('report-reason'));
    body.append('dettagli', getValue('report-description'));
    body.append('nome', getValue('name'));
    body.append('cognome', getValue('surname'));
    body.append('email', getValue('email'));
    body.append('telefono', getValue('phone'));
    body.append('privacy', getEl('privacy') && getEl('privacy').checked ? '1' : '0');
    body.append('website', getValue('report-website'));

    fetch(dciSegnalazione.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'Cache-Control': 'no-cache'
      },
      body: body.toString()
    })
      .then(function (response) {
        return response.json().catch(function () {
          throw new Error('Il server ha restituito una risposta non valida.');
        }).then(function (result) {
          if (!response.ok || !result || !result.success) {
            var message = result && result.data && result.data.message
              ? result.data.message
              : 'Non è stato possibile inviare la segnalazione.';
            throw new Error(message);
          }
          return result.data;
        });
      })
      .then(function (data) {
        var formSteps = getEl('form-steps');
        if (formSteps) formSteps.classList.add('d-none');
        setText('report-ticket-code', data.ticket || '');
        if (successBox) {
          successBox.classList.remove('d-none');
          successBox.focus();
          successBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      })
      .catch(function (error) {
        submitting = false;
        updateButtons();
        showError(error.message || 'Si è verificato un errore. Riprova tra qualche istante.');
      });
  }

  btnNext.addEventListener('click', function () {
    if (!isStepValid(currentStep)) {
      showError('Controlla i campi obbligatori prima di proseguire.');
      return;
    }

    if (currentStep === steps.length) {
      submitReport();
      return;
    }

    goToStep(currentStep + 1);
  });

  btnBack.addEventListener('click', function () {
    if (!submitting && currentStep > 1) goToStep(currentStep - 1);
  });

  var editButtons = content.querySelectorAll('.report-edit-step');
  for (var i = 0; i < editButtons.length; i++) {
    editButtons[i].addEventListener('click', function () {
      goToStep(Number(this.getAttribute('data-edit-step')));
    });
  }

  var fields = [
    'privacy',
    'luogo-disservizio',
    'location-details',
    'report-type',
    'report-reason',
    'report-description',
    'name',
    'surname',
    'email',
    'phone'
  ];

  for (var j = 0; j < fields.length; j++) {
    var field = getEl(fields[j]);
    if (!field) continue;
    field.addEventListener(field.tagName === 'SELECT' || field.type === 'checkbox' ? 'change' : 'input', function () {
      clearError();
      updateNextState();
      if (currentStep === steps.length) updateSummary();
    });
  }

  var description = getEl('report-description');
  var counter = getEl('description-counter');
  if (description && counter) {
    description.addEventListener('input', function () {
      counter.textContent = description.value.length + '/1000';
    });
  }

  goToStep(1, false);
})();
