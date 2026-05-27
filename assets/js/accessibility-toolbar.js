(function(){
  var wrap=document.querySelector('[data-dci-a11y]'); if(!wrap) return;
  var KEY='dciA11yPrefs',root=document.documentElement,body=document.body;
  var panel=wrap.querySelector('#dci-a11y-panel'),toggle=wrap.querySelector('[data-a11y-action="toggle"]');
  var target=document.querySelector('main')||document.getElementById('main')||body;
  target.setAttribute('data-dci-a11y-target','1');

  var levels={brightness:[100,108,116,124],contrast:[100,108,116,124],saturation:[100,90,110,125],grayscale:[0,35,70,100],lineHeight:[1.5,1.7,1.9,2.1],letterSpacing:[0,0.03,0.06,0.09],cursor:[0,1,2,3]};
  var prefs={font:100,cursor:0,links:false,dyslexia:false,invert:false,brightness:0,contrast:0,saturation:0,grayscale:0,readableFont:false,highlightAll:false,highlightTitles:false,hideImages:false,mute:false,stopAnimations:false,keyboard:false,lineHeight:0,letterSpacing:0};

  try{prefs=Object.assign(prefs,JSON.parse(localStorage.getItem(KEY)||'{}'));}catch(e){}

  function clamp(n,min,max){return Math.min(max,Math.max(min,n));}
  function cycle(name){prefs[name]=(prefs[name]+1)%levels[name].length;}
  function save(){localStorage.setItem(KEY,JSON.stringify(prefs));}


  function levelInfo(action){
    if(action==='brightness') return {current:prefs.brightness,max:levels.brightness.length-1};
    if(action==='contrast') return {current:prefs.contrast,max:levels.contrast.length-1};
    if(action==='saturation') return {current:prefs.saturation,max:levels.saturation.length-1};
    if(action==='grayscale') return {current:prefs.grayscale,max:levels.grayscale.length-1};
    if(action==='line-height') return {current:prefs.lineHeight,max:levels.lineHeight.length-1};
    if(action==='cursor') return {current:prefs.cursor,max:levels.cursor.length-1};
    if(action==='letter-spacing') return {current:prefs.letterSpacing,max:levels.letterSpacing.length-1};
    if(action==='font-up'||action==='font-down') return {current:Math.max(0,Math.round((prefs.font-90)/10)),max:4};
    return null;
  }

  function isActiveAction(a){
    return (a==='links'&&prefs.links)||(a==='dyslexia'&&prefs.dyslexia)||(a==='cursor'&&prefs.cursor>0)||(a==='invert'&&prefs.invert)||(a==='brightness'&&prefs.brightness>0)||(a==='contrast'&&prefs.contrast>0)||(a==='grayscale'&&prefs.grayscale>0)||(a==='saturation'&&prefs.saturation>0)||(a==='readable-font'&&prefs.readableFont)||(a==='highlight-all'&&prefs.highlightAll)||(a==='highlight-titles'&&prefs.highlightTitles)||(a==='hide-images'&&prefs.hideImages)||(a==='mute'&&prefs.mute)||(a==='stop-animations'&&prefs.stopAnimations)||(a==='keyboard'&&prefs.keyboard)||(a==='line-height'&&prefs.lineHeight>0)||(a==='letter-spacing'&&prefs.letterSpacing>0);
  }

  function apply(){
    root.style.fontSize=prefs.font+'%';
    body.classList.toggle('dci-a11y-links',!!prefs.links);
    body.classList.toggle('dci-a11y-dyslexia',!!prefs.dyslexia);
    body.classList.toggle('dci-a11y-readable-font',!!prefs.readableFont);
    body.classList.toggle('dci-a11y-highlight-all',!!prefs.highlightAll);
    body.classList.toggle('dci-a11y-highlight-titles',!!prefs.highlightTitles);
    body.classList.toggle('dci-a11y-hide-images',!!prefs.hideImages);
    body.classList.toggle('dci-a11y-stop-animations',!!prefs.stopAnimations);
    body.classList.toggle('dci-a11y-keyboard',!!prefs.keyboard);
    body.classList.remove('dci-a11y-cursor-1','dci-a11y-cursor-2','dci-a11y-cursor-3');
    if(prefs.cursor>0){body.classList.add('dci-a11y-cursor-'+prefs.cursor);}

    target.style.lineHeight=levels.lineHeight[prefs.lineHeight];
    target.style.letterSpacing=levels.letterSpacing[prefs.letterSpacing]+'em';
    target.style.setProperty('--dci-a11y-filter','invert('+ (prefs.invert?100:0) +'%) brightness('+levels.brightness[prefs.brightness]+'%) contrast('+levels.contrast[prefs.contrast]+'%) grayscale('+levels.grayscale[prefs.grayscale]+'%) saturate('+levels.saturation[prefs.saturation]+'%)');

    document.querySelectorAll('audio,video').forEach(function(m){ m.muted=!!prefs.mute; });

    wrap.querySelectorAll('.dci-a11y-btn').forEach(function(btn){
      var a=btn.dataset.a11yAction;
      var active=isActiveAction(a);
      btn.classList.toggle('is-active',active);
      btn.setAttribute('aria-pressed',active?'true':'false');
      var lvl=levelInfo(a);
      var dots=btn.querySelectorAll('.dci-a11y-level i');
      if(dots.length&&lvl){
        dots.forEach(function(d,i){ d.classList.toggle('is-on', i < lvl.current); });
      }
    });
  }

  function speak(){
    if(!('speechSynthesis' in window)) return;
    window.speechSynthesis.cancel();
    var t=(window.getSelection&&window.getSelection().toString())||((document.querySelector('main')||body).innerText||'').slice(0,2200);
    if(!t) return;
    var u=new SpeechSynthesisUtterance(t); u.lang='it-IT'; window.speechSynthesis.speak(u);
  }

  function handleAction(a){
    if(a==='font-up') prefs.font=clamp(prefs.font+10,90,130);
    if(a==='font-down') prefs.font=clamp(prefs.font-10,90,130);
    if(a==='cursor') cycle('cursor');
    if(a==='links') prefs.links=!prefs.links;
    if(a==='dyslexia') prefs.dyslexia=!prefs.dyslexia;
    if(a==='readable-font') prefs.readableFont=!prefs.readableFont;
    if(a==='highlight-all') prefs.highlightAll=!prefs.highlightAll;
    if(a==='highlight-titles') prefs.highlightTitles=!prefs.highlightTitles;
    if(a==='hide-images') prefs.hideImages=!prefs.hideImages;
    if(a==='mute') prefs.mute=!prefs.mute;
    if(a==='stop-animations') prefs.stopAnimations=!prefs.stopAnimations;
    if(a==='keyboard') prefs.keyboard=!prefs.keyboard;
    if(a==='invert') prefs.invert=!prefs.invert;
    if(a==='brightness') cycle('brightness');
    if(a==='contrast') cycle('contrast');
    if(a==='saturation') cycle('saturation');
    if(a==='grayscale') cycle('grayscale');
    if(a==='line-height') cycle('lineHeight');
    if(a==='letter-spacing') cycle('letterSpacing');
    if(a==='read') speak();
    if(a==='reset'){
      prefs={font:100,cursor:0,links:false,dyslexia:false,invert:false,brightness:0,contrast:0,saturation:0,grayscale:0,readableFont:false,highlightAll:false,highlightTitles:false,hideImages:false,mute:false,stopAnimations:false,keyboard:false,lineHeight:0,letterSpacing:0};
      if(window.speechSynthesis) window.speechSynthesis.cancel();
    }
    save(); apply();
  }

  toggle.addEventListener('click',function(){var open=panel.hidden; panel.hidden=!open; toggle.setAttribute('aria-expanded',open?'true':'false');});

  wrap.addEventListener('click',function(e){
    var b=e.target.closest('.dci-a11y-btn');
    if(!b) return;
    e.preventDefault();
    handleAction(b.dataset.a11yAction);
  });

  apply();
})();
