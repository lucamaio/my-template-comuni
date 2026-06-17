(function () {
  'use strict';

  var settings = window.dciAsyncTemplateParts || {};

  function getLoaderMarkup(message) {
    return '<div class="container py-5"><div class="dci-async-loader" aria-hidden="true"><span class="dci-async-loader__spinner"></span><span class="dci-async-loader__line dci-async-loader__line--long"></span><span class="dci-async-loader__line"></span></div><span class="visually-hidden">' + message + '</span></div>';
  }

  function showTemplateTimeout(placeholder) {
    placeholder.setAttribute('aria-busy', 'false');
    placeholder.classList.add('dci-async-template--error');
    placeholder.innerHTML = '<div class="container py-5"><p class="mb-0">Non è stato possibile caricare questa sezione. Ricarica la pagina più tardi.</p></div>';
  }

  function scheduleTemplateRetry(placeholder) {
    var retryCount = parseInt(placeholder.getAttribute('data-retry-count') || '0', 10) + 1;
    var retryTimeoutMs = parseInt(settings.retryTimeoutMs, 10) || 600000;
    var retryStartedAt = parseInt(placeholder.getAttribute('data-retry-started-at') || '0', 10);
    var elapsedMs;
    var retryDelay;

    if (!retryStartedAt) {
      retryStartedAt = Date.now();
      placeholder.setAttribute('data-retry-started-at', retryStartedAt);
    }

    elapsedMs = Date.now() - retryStartedAt;
    if (elapsedMs >= retryTimeoutMs) {
      showTemplateTimeout(placeholder);
      return Promise.resolve();
    }

    retryDelay = Math.min(20000, 2000 * retryCount, retryTimeoutMs - elapsedMs);

    placeholder.setAttribute('data-retry-count', retryCount);
    placeholder.setAttribute('aria-busy', 'true');
    placeholder.classList.remove('dci-async-template--error');
    placeholder.innerHTML = getLoaderMarkup('Nuovo tentativo di caricamento della sezione');

    return new Promise(function (resolve) {
      window.setTimeout(function () {
        resolve(loadTemplate(placeholder));
      }, retryDelay);
    });
  }

  function getAjaxUrl() {
    var ajaxUrl;

    try {
      ajaxUrl = new URL(settings.ajaxurl, window.location.href);
    } catch (error) {
      return settings.ajaxurl;
    }

    if (window.location.protocol === 'https:' && ajaxUrl.protocol === 'http:' && ajaxUrl.host === window.location.host) {
      ajaxUrl.protocol = 'https:';
    }

    return ajaxUrl.toString();
  }

  if (!settings.ajaxurl || !window.fetch) {
    return;
  }

  function executeInjectedScripts(container) {
    container.querySelectorAll('script').forEach(function (script) {
      var clone = document.createElement('script');

      Array.prototype.slice.call(script.attributes).forEach(function (attribute) {
        clone.setAttribute(attribute.name, attribute.value);
      });

      clone.text = script.text;
      script.parentNode.replaceChild(clone, script);
    });
  }

  function initInjectedComponents(container) {
    if (!container) {
      return;
    }

    container.querySelectorAll('.carousel').forEach(function (carousel) {
      if (window.bootstrap && window.bootstrap.Carousel) {
        window.bootstrap.Carousel.getOrCreateInstance(carousel);
      }
    });

    container.querySelectorAll('[data-bs-carousel-splide]').forEach(function (carousel) {
      if (window.bootstrap && window.bootstrap.CarouselBI) {
        window.bootstrap.CarouselBI.getOrCreateInstance(carousel);
      }
    });

    if (typeof window.CustomEvent === 'function') {
      document.dispatchEvent(new CustomEvent('dci:async-template-loaded', {
        detail: { container: container }
      }));
    }
  }

  function loadTemplate(placeholder) {
    var templateKey = placeholder.getAttribute('data-template-key');

    if (!templateKey) {
      return Promise.resolve();
    }

    var body = new URLSearchParams();
    var controller = window.AbortController ? new AbortController() : null;
    var timeoutMs = parseInt(settings.timeoutMs, 10) || 12000;
    var timeoutId = null;
    var fetchOptions;

    body.append('action', 'dci_load_template_part');
    body.append('template_key', templateKey);
    body.append('page_id', placeholder.getAttribute('data-page-id') || '0');
    body.append('query_string', window.location.search.replace(/^\?/, ''));
    body.append('current_url', window.location.href);
    body.append('term_id', placeholder.getAttribute('data-term-id') || '0');
    body.append('taxonomy', placeholder.getAttribute('data-taxonomy') || '');

    fetchOptions = {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'Cache-Control': 'no-cache'
      },
      body: body.toString()
    };

    if (controller) {
      fetchOptions.signal = controller.signal;
      timeoutId = window.setTimeout(function () {
        controller.abort();
      }, timeoutMs);
    }

    return fetch(getAjaxUrl(), fetchOptions)
      .then(function (response) {
        if (timeoutId) {
          window.clearTimeout(timeoutId);
        }

        if (!response.ok) {
          throw new Error('HTTP ' + response.status);
        }

        return response.json();
      })
      .then(function (payload) {
        if (!payload || !payload.success || !payload.data || typeof payload.data.html !== 'string') {
          throw new Error('Risposta non valida');
        }

        placeholder.removeAttribute('data-retry-count');
        placeholder.removeAttribute('data-retry-started-at');
        var wrapper = document.createElement('div');
        wrapper.innerHTML = payload.data.html;
        wrapper.className = 'dci-async-template__content';
        placeholder.replaceWith(wrapper);
        executeInjectedScripts(wrapper);
        initInjectedComponents(wrapper);
      })
      .catch(function () {
        if (timeoutId) {
          window.clearTimeout(timeoutId);
        }

        return scheduleTemplateRetry(placeholder);
      });
  }

  function boot() {
    var placeholders = Array.prototype.slice.call(document.querySelectorAll('.dci-async-template[data-template-key]'));
    var maxConcurrent = parseInt(settings.maxConcurrent, 10) || 4;
    var active = 0;
    var index = 0;

    function runNext() {
      if (index >= placeholders.length || active >= maxConcurrent) {
        return;
      }

      active += 1;
      loadTemplate(placeholders[index]).then(function () {
        active -= 1;
        runNext();
      }, function () {
        active -= 1;
        runNext();
      });
      index += 1;
      runNext();
    }

    runNext();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
}());
