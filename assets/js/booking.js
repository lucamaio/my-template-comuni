function borderCardRadio(){var t=document.querySelectorAll(".radio-card");document.querySelectorAll(".radio-input").forEach(function(e,n){e.addEventListener("change",function(){t.forEach(function(e,t){n==t?e.classList.add("has-border-green"):e.classList.remove("has-border-green")})})})}var content=document.querySelector(".section-wrapper"),currentStep=1,navscroll=document.querySelector('[data-index="'.concat(currentStep,'"]')),progressBar=document.querySelector('[data-progress="'.concat(currentStep,'"]')),btnNext=content.querySelector(".btn-next-step"),btnBack=content.querySelector(".btn-back-step"),submitting=!1;function setBookingSubmitting(e){if(submitting=e,btnNext){btnNext.disabled=e,btnNext.setAttribute("aria-busy",e?"true":"false");var t=btnNext.querySelector("span");t&&(t.innerHTML=e?'<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Invio in corso...':currentStep==content.querySelectorAll("[data-steps]").length?"Invia":"Avanti")}btnBack&&(btnBack.disabled=e||1==currentStep)}function pageSteps(){var e;content&&(e=content.querySelectorAll(".saveBtn"),navscroll.classList.add("d-lg-block"),progressBar.classList.remove("d-none"),e.forEach(function(e){e.classList.add("invisible")}),btnNext&&btnNext.addEventListener("click",function(){openNext()}),btnBack)&&btnBack.addEventListener("click",function(){backPrevious()})}function openNext(){if(submitting)return;btnBack.disabled=!1;var e=content.querySelectorAll(".saveBtn"),t=content.querySelectorAll("[data-steps]"),n=content.querySelector('[data-steps="'.concat(currentStep+1,'"]')),r=content.querySelector("[data-steps].active");navscroll.classList.remove("d-lg-block"),progressBar.classList.remove("d-block"),progressBar.classList.add("d-none"),currentStep==t.length?confirmAppointment():(r.classList.add("d-none"),r.classList.remove("active"),n.classList.add("active"),n.classList.remove("d-none"),currentStep+=1,checkMandatoryFields(),(progressBar=document.querySelector('[data-progress="'.concat(currentStep,'"]'))).classList.add("d-block"),progressBar.classList.remove("d-none"),currentStep<=t.length&&(navscroll=document.querySelector('[data-index="'.concat(currentStep,'"]'))).classList.add("d-lg-block"),currentStep==t.length&&(btnNext.disabled=!1,content.querySelector(".steppers-btn-confirm span").innerHTML="Invia",e.forEach(function(e){e.classList.remove("invisible"),e.classList.add("visible"),setReviews()})))}function backPrevious(){btnNext.disabled=!1;var e=content.querySelectorAll(".saveBtn"),t=content.querySelectorAll("[data-steps]"),n=content.querySelector("[data-steps].active"),r=content.querySelector('[data-steps="'.concat(currentStep-1,'"]'));1!=currentStep&&(r.classList.remove("d-none"),r.classList.add("active"),n.classList.add("d-none"),n.classList.remove("active"),navscroll.classList.remove("d-lg-block"),progressBar.classList.add("d-none"),currentStep-=1,(progressBar=document.querySelector('[data-progress="'.concat(currentStep,'"]'))).classList.toggle("d-none"),content.querySelector(".steppers-btn-confirm span").innerHTML="Avanti",currentStep<t.length&&((navscroll=document.querySelector('[data-index="'.concat(currentStep,'"]'))).classList.add("d-lg-block"),content.classList.add("offset-lg-1")),currentStep<t.length&&e.forEach(function(e){e.classList.remove("visible"),e.classList.add("invisible")}),1==currentStep)&&(btnBack.disabled=!0)}function goToStep(e){var t,n,r,a=content.querySelectorAll("[data-steps]");e<1||e>a.length||(r=content.querySelector("[data-steps].active"),t=content.querySelector('[data-steps="'.concat(e,'"]')),n=content.querySelectorAll(".saveBtn"),r&&(r.classList.add("d-none"),r.classList.remove("active")),navscroll&&navscroll.classList.remove("d-lg-block"),progressBar&&progressBar.classList.add("d-none"),t.classList.remove("d-none"),t.classList.add("active"),currentStep=e,progressBar=document.querySelector('[data-progress="'.concat(currentStep,'"]')),navscroll=document.querySelector('[data-index="'.concat(currentStep,'"]')),progressBar.classList.remove("d-none"),progressBar.classList.add("d-block"),navscroll.classList.add("d-lg-block"),content.classList.add("offset-lg-1"),btnBack.disabled=1===currentStep,content.querySelector(".steppers-btn-confirm span").innerHTML=currentStep===a.length?"Invia":"Avanti",n.forEach(function(e){e.classList.toggle("invisible",currentStep!==a.length),e.classList.toggle("visible",currentStep===a.length)}),currentStep===a.length&&setReviews(),checkMandatoryFields(),(r=document.querySelector(".cmp-hero"))&&r.scrollIntoView({behavior:"smooth",block:"start"}))}pageSteps();const answers={},encodeObject=e=>encodeURIComponent(JSON.stringify(e)),decodeObj=e=>JSON.parse(decodeURIComponent(e)),saveAnswerByValue=(e,t,n=!1)=>{if("office"==e)for(const r in answers)"privacy"!==r&&delete answers[r];n?(n=decodeObj(t),answers[e]=n):answers[e]=t,checkMandatoryFields()},saveAnswerById=(e,t,n)=>{t=document.getElementById(t)?.value;answers[e]=JSON.parse(t),"function"==typeof n&&n(),checkMandatoryFields()},privacyConsent=document.getElementById("booking-privacy-consent"),officeSelect=(privacyConsent.addEventListener("change",()=>{saveAnswerByValue("privacy",privacyConsent.checked?"1":"")}),document.getElementById("office-choice")),appointment=(officeSelect.addEventListener("change",()=>{var e=officeSelect?.value,t=officeSelect?.querySelector(`[value="${e}"]`)?.innerText;saveAnswerByValue("office",encodeObject({id:e,name:t}),!0),officeSelect?.value?(e=new URLSearchParams({id:officeSelect.value}),fetch(window.wpRestApi+"wp/v2/sedi/ufficio/?"+e).then(e=>e.json()).then(e=>{document.querySelector("#place-cards-wrapper").innerHTML='<legend class="visually-hidden">Seleziona un ufficio</legend>';for(const n of e){var t={nome:n.post_title,indirizzo:n.indirizzo,apertura:n.apertura,id:n.identificativo};document.querySelector("#place-cards-wrapper").innerHTML+=`
          <div class="cmp-info-radio radio-card">
            <div class="card p-3 p-lg-4">
              <div class="card-header mb-0 p-0">
                <div class="form-check m-0">
                    <input
                    class="radio-input"
                    name="beneficiaries"
                    type="radio"
                    id=${n?.ID}
                    value='${JSON.stringify(t)}'
                    onclick="saveAnswerById('place', ${n?.ID}, ${()=>setSelectedPlace()})"
                    />
                    <label for=${n?.ID}>
                    <h3 class="big-title mb-0 pb-0">
                        ${n?.post_title}
                    </h3></label
                    >
                </div>
              </div>
              <div class="card-body p-0">
                <div class="info-wrapper">
                  <span class="info-wrapper__label">Indirizzo</span>
                  <p class="info-wrapper__value">
                  ${n?.indirizzo}
                  </p>
                </div>
                <div class="info-wrapper">
                    <span class="info-wrapper__label">Apertura</span>
                    <p class="info-wrapper__value">
                    ${n?.apertura}
                    </p>
                </div>
              </div>
            </div>
          </div>
          `}borderCardRadio()}).catch(e=>{console.log("err",e)}),fetch(window.wpRestApi+"wp/v2/servizi/ufficio?"+e).then(e=>e.json()).then(e=>{document.querySelector("#motivo-appuntamento").innerHTML='<option selected="selected" value="">Seleziona opzione</option>';for(const t of e)document.querySelector("#motivo-appuntamento").innerHTML+=`
          <option value="${t?.ID}">${t?.post_title}</option>
          `}).catch(e=>{console.log("err",e)})):document.querySelector("#place-cards-wrapper").innerHTML=""}),document.getElementById("appointment")),setSelectedPlace=(appointment.addEventListener("change",()=>{answers.appointment=null,checkMandatoryFields();var e=new Date,t=Number(appointment?.value),e=t<e.getMonth()+1?e.getFullYear()+1:e.getFullYear(),t=new URLSearchParams({id:answers?.office?.id,month:t,year:e});fetch(window.wpRestApi+"wp/v2/appuntamenti/ufficio/?"+t).then(e=>{if(e.ok)return e.json();throw new Error("HTTP error "+e.status)}).then(e=>{e=Array.isArray(e)?e:e?.[appointment?.value]||[];const t=new Date;if(e=e.filter(e=>{e=new Date(e?.startDate);return!Number.isNaN(e.getTime())&&e>t}),document.querySelector("#radio-appointment").innerHTML='<legend class="visually-hidden">Seleziona un giorno e orario</legend>',e.length)for(const o of e){var{startDate:n,endDate:r}=o,a=n.split("T")[0],a=new Date(a).toLocaleString([],{weekday:"long",day:"2-digit",month:"long",year:"numeric"}),s=n+"/"+r,r=encodeObject({startDate:n,endDate:r});document.querySelector("#radio-appointment").innerHTML+=`
        <div
        class="radio-body border-bottom border-light"
        >
        <input name="radio" type="radio" id="${s}" onclick="saveAnswerByValue('appointment', '${r}', true)"/>
        <label for="${s}" class="text-capitalize">${a} ore ${n.split("T")[1]}</label>
        </div>
        `}else document.querySelector("#radio-appointment").innerHTML+=`
        <p class="mt-2 mb-0">Nessun appuntamento disponibile per il mese selezionato.</p>
        `}).catch(e=>{console.log("err",e)})}),()=>{var e=answers?.place;document.querySelector("#selected-place-card").innerHTML=`  
  <div class="cmp-info-summary bg-white mb-4 mb-lg-30 p-4">
  <div class="card">
      <div
      class="card-header border-bottom border-light p-0 mb-0 d-flex justify-content-between d-flex justify-content-end"
      >
      <h3 class="title-large-semi-bold mb-3">
        ${e?.nome}
      </h3>
      </div>

      <div class="card-body p-0">
      <div class="single-line-info border-light">
          <div class="text-paragraph-small">Indirizzo</div>
          <div class="border-light">
          <p class="data-text">
            ${e?.indirizzo}
          </p>
          </div>
      </div>
      <div class="single-line-info border-light">
          <div class="text-paragraph-small">Apertura</div>
          <div class="border-light">
          <p class="data-text">
            ${e?.apertura}
          </p>
          </div>
      </div>
      </div>
      <div class="card-footer p-0"></div>
  </div>
  </div>
</div>
  `}),serviceSelect=document.getElementById("motivo-appuntamento"),moreDetailsText=(serviceSelect.addEventListener("change",()=>{var e=serviceSelect?.value,t=serviceSelect?.querySelector(`[value="${e}"]`)?.innerText;saveAnswerByValue("service",encodeObject({id:e,name:t}),!0)}),document.getElementById("form-details")),nameInput=(moreDetailsText.addEventListener("input",()=>{saveAnswerByValue("moreDetails",moreDetailsText?.value)}),document.getElementById("name")),surnameInput=(nameInput.addEventListener("input",()=>{saveAnswerByValue("name",nameInput?.value)}),document.getElementById("surname")),emailInput=(surnameInput.addEventListener("input",()=>{saveAnswerByValue("surname",surnameInput?.value)}),document.getElementById("email")),phoneInput=(emailInput.addEventListener("input",()=>{saveAnswerByValue("email",emailInput?.value)}),document.getElementById("phone")),editButtons=(phoneInput.addEventListener("input",()=>{saveAnswerByValue("phone",phoneInput?.value)}),content.querySelectorAll(".booking-edit-step")),getDay=(editButtons.forEach(function(e){e.addEventListener("click",function(){goToStep(Number(e.dataset.editStep))})}),()=>{var e=answers?.appointment?.startDate?.split("T")[0];return new Date(e).toLocaleString([],{weekday:"long",day:"2-digit",month:"long",year:"numeric"})}),getHour=()=>{var e=answers?.appointment;return[e?.startDate?.split("T")[1],e?.endDate?.split("T")[1]]},setReviews=()=>{document.getElementById("review-office").innerHTML=answers?.office?.name,document.getElementById("review-place").innerHTML=answers?.place?.nome,document.getElementById("review-date").innerHTML=getDay(),document.getElementById("review-hour").innerHTML=getHour()[0]+" - "+getHour()[1],document.getElementById("review-service").innerHTML=answers?.service?.name,document.getElementById("review-details").innerHTML=answers?.moreDetails,document.getElementById("review-name").innerHTML=answers?.name,document.getElementById("review-surname").innerHTML=answers?.surname,document.getElementById("review-email").innerHTML=answers?.email,document.getElementById("review-phone").innerHTML=answers?.phone||"Non indicato"},validatePhone=e=>!e||/^[0-9+().\s/-]{6,30}$/.test(e)&&/[0-9]/.test(e),checkMandatoryFields=()=>{if(submitting)return void(btnNext.disabled=!0);switch(currentStep){case 1:answers?.privacy?btnNext.disabled=!1:btnNext.disabled=!0;break;case 2:answers?.office&&answers?.place?btnNext.disabled=!1:btnNext.disabled=!0;break;case 3:answers?.appointment?btnNext.disabled=!1:btnNext.disabled=!0;break;case 4:answers?.service&&answers?.moreDetails?btnNext.disabled=!1:btnNext.disabled=!0;break;case 5:answers?.name&&answers?.surname&&answers?.email&&validatePhone(answers?.phone)?btnNext.disabled=!1:btnNext.disabled=!0}};async function successFeedback(){document.getElementById("email-recap").innerText=answers?.email,document.getElementById("date-recap").innerText=` ${getDay()} dalle ore ${getHour()[0]} alle ore `+getHour()[1];var e=await getServiceDetail(answers?.service?.id);if(0<e?._dci_servizio_cosa_serve_list?.length||e?._dci_servizio_cosa_serve_introduzione){const t=document.getElementById("needed-recap");t.innerHTML=`
      <p class="font-serif">${e?._dci_servizio_cosa_serve_introduzione}</p>
    `,0<e?._dci_servizio_cosa_serve_list?.length&&(t.innerHTML+="<ul>",e._dci_servizio_cosa_serve_list.forEach(e=>{t.innerHTML+=`<li>${e}</li>`}),t.innerHTML+="</ul>")}document.getElementById("office-recap").innerHTML=`
    <a
      href="#"
      class="text-decoration-none"
      >${answers?.office?.name}</a
    >  
  `,document.getElementById("address-recap").innerHTML=answers?.place?.nome,document.getElementById("form-steps").classList.add("d-none"),document.getElementById("final-step").classList.remove("d-none")}const confirmAppointment=()=>{if(submitting)return;setBookingSubmitting(!0);var e,t=new URLSearchParams;for(e in answers)"object"==typeof answers[e]?t.append(e,JSON.stringify(answers[e])):t.append(e,answers[e]);t.append("action","save_appuntamento"),fetch(urlConfirm,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/x-www-form-urlencoded","Cache-Control":"no-cache"},body:t}).then(e=>{if(e.ok)return e.json();throw new Error("HTTP error "+e.status)}).then(e=>{successFeedback();var t=document.querySelector("#main-container");t&&t.scrollIntoView({behavior:"smooth"})}).catch(e=>{setBookingSubmitting(!1),checkMandatoryFields(),console.log("err",e)})};async function getServiceDetail(e){try{return await fetch(window.wpRestApi+"wp/v2/servizi/"+e).then(e=>{if(e.ok)return e.json();throw new Error("HTTP error "+e.status)}).then(e=>e?.cmb2?._dci_servizio_box_cosa_serve).catch(e=>{console.log("err",e)})}catch(e){console.error(e)}}
