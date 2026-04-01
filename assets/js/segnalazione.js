(function () {
  var content = document.querySelector('.section-wrapper');
  if (!content) return;

  var steps = Array.prototype.slice.call(content.querySelectorAll('[data-steps]'));
  var btnNext = content.querySelector('.btn-next-step');
  var btnBack = content.querySelector('.btn-back-step');
  var alertMessage = document.getElementById('alert-message');
  var currentStep = 1;

  function getEl(id) {
    return document.getElementById(id);
  }

  function getValue(id) {
    var el = getEl(id);
    return el ? (el.value || '').trim() : '';
  }

  function setProgress() {
    var progresses = document.querySelectorAll('[data-progress]');
    for (var i = 0; i < progresses.length; i++) {
      progresses[i].classList.add('d-none');
    }
    var currentProgress = document.querySelector('[data-progress="' + currentStep + '"]');
    if (currentProgress) currentProgress.classList.remove('d-none');
  }

  function showStep(stepNumber) {
    var node = content.querySelector('[data-steps="' + stepNumber + '"]');
    if (!node) return;
    node.classList.remove('d-none');
    node.classList.add('active');
  }

  function hideStep(stepNumber) {
    var node = content.querySelector('[data-steps="' + stepNumber + '"]');
    if (!node) return;
    node.classList.add('d-none');
    node.classList.remove('active');
  }

  function validateEmail(email) {
    var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
  }

  function updateNextState() {
    if (!btnNext) return;

    if (currentStep === 1) {
      var privacy = getEl('privacy');
      btnNext.disabled = !(privacy && privacy.checked);
      return;
    }

    if (currentStep === 2) {
      var place = getValue('luogo-disservizio');
      var service = getValue('motivo-appuntamento');
      var title = getValue('title');
      var details = getValue('details');
      btnNext.disabled = !(place && service && title && details);
      return;
    }

    if (currentStep === 3) {
      btnNext.disabled = false;
      return;
    }

    if (currentStep === 4) {
      var name = getValue('name');
      var surname = getValue('surname');
      var email = getValue('email');
      btnNext.disabled = !(name && surname && email && validateEmail(email));
      return;
    }

    btnNext.disabled = false;
  }

  function getSelectedText(selectId) {
    var select = getEl(selectId);
    if (!select || select.selectedIndex < 0) return '';
    return select.options[select.selectedIndex].text || '';
  }

  function submitReport() {
    var title = getValue('title');
    var details = getValue('details');
    var name = getValue('name');
    var surname = getValue('surname');
    var email = getValue('email');
    var placeText = getSelectedText('luogo-disservizio');
    var serviceText = getSelectedText('motivo-appuntamento');

    var fullDetails = [
      'Titolo: ' + title,
      'Luogo: ' + placeText,
      'Tipologia disservizio: ' + serviceText,
      'Dettagli: ' + details
    ].join('\n');

    var url = (typeof urlConfirm !== 'undefined' && Array.isArray(urlConfirm)) ? urlConfirm[0] : urlConfirm;
    var body = new URLSearchParams();
    body.append('action', 'save_richiesta_assistenza');
    body.append('nome', name);
    body.append('cognome', surname);
    body.append('email', email);
    body.append('categoria_servizio', '');
    body.append('servizio', serviceText);
    body.append('dettagli', fullDetails);
    body.append('privacyChecked', (getEl('privacy') && getEl('privacy').checked) ? 'true' : 'false');

    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Cache-Control': 'no-cache'
      },
      body: body
    }).then(function (response) {
      if (!response.ok) {
        throw new Error('HTTP error ' + response.status);
      }
      return response.json();
    }).then(function (result) {
      if (!result || !result.success) {
        throw new Error('Salvataggio segnalazione non riuscito');
      }

      var formSteps = getEl('form-steps');
      if (formSteps) formSteps.classList.add('d-none');
      if (alertMessage) alertMessage.classList.remove('d-none');

      var mainContainer = document.querySelector('#main-container');
      if (mainContainer) {
        mainContainer.scrollIntoView({ behavior: 'smooth' });
      }
    });
  }

  function openNext() {
    if (!btnNext || !btnBack) return;

    if (currentStep >= steps.length) {
      btnNext.disabled = true;
      submitReport().catch(function (err) {
        console.error(err);
        btnNext.disabled = false;
      });
      return;
    }

    hideStep(currentStep);
    currentStep += 1;
    showStep(currentStep);

    btnBack.disabled = currentStep === 1;

    var nextLabel = btnNext.querySelector('span');
    if (nextLabel) {
      nextLabel.innerHTML = currentStep === steps.length ? 'Invia' : 'Avanti';
    }

    setProgress();
    updateNextState();
  }

  function backPrevious() {
    if (!btnNext || !btnBack || currentStep === 1) return;

    hideStep(currentStep);
    currentStep -= 1;
    showStep(currentStep);

    btnBack.disabled = currentStep === 1;

    var nextLabel = btnNext.querySelector('span');
    if (nextLabel) nextLabel.innerHTML = 'Avanti';

    setProgress();
    updateNextState();
  }

  btnNext.addEventListener('click', openNext);
  btnBack.addEventListener('click', backPrevious);

  var fields = [
    'privacy',
    'luogo-disservizio',
    'motivo-appuntamento',
    'title',
    'details',
    'name',
    'surname',
    'email'
  ];

  for (var i = 0; i < fields.length; i++) {
    var el = getEl(fields[i]);
    if (!el) continue;

    if (el.tagName === 'SELECT' || el.type === 'checkbox') {
      el.addEventListener('change', updateNextState);
    } else {
      el.addEventListener('input', updateNextState);
    }
  }

  setProgress();
  updateNextState();
})();
