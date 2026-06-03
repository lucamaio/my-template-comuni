(function () {
  'use strict';

  function markDeferredImageLoaded(image) {
    var frame = image.closest('.dci-deferred-img-frame');

    image.classList.add('dci-deferred-img-loaded');
    if (frame) {
      frame.classList.add('dci-deferred-img-frame--loaded');
    }
  }

  function loadDeferredImages(scope) {
    var root = scope || document;

    root.querySelectorAll('img[data-dci-src]').forEach(function (image) {
      var src = image.getAttribute('data-dci-src');
      if (!src) {
        return;
      }

      image.addEventListener('load', function () {
        markDeferredImageLoaded(image);
      }, { once: true });
      image.addEventListener('error', function () {
        markDeferredImageLoaded(image);
      }, { once: true });

      image.setAttribute('src', src);
      image.removeAttribute('data-dci-src');

      if (image.complete) {
        markDeferredImageLoaded(image);
      }
    });
  }

  if (document.readyState === 'complete') {
    loadDeferredImages();
  } else {
    window.addEventListener('load', function () {
      loadDeferredImages();
    }, { once: true });
  }

  document.addEventListener('dci:async-template-loaded', function (event) {
    loadDeferredImages(event.detail && event.detail.container ? event.detail.container : document);
  });
}());
