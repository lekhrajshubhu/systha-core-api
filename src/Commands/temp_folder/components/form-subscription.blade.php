 <div class="container d-flex justify-content-center py-3 my-3 custom-form">
     <div class="row w-100 overflow-hidden" style="max-width: 900px;">
         <!-- Left Panel -->
         <div class="col-md-4 bg-theme text-white p-4">
             <div class="h-100 d-flex justify-content-center">
                 <div id="left-review">
                     @include($viewPath . '::components._form_partials._left_panel_package')
                 </div>
             </div>
         </div>

         <!-- Right Panel -->
         <div class="col-md-8 bg-white px-0" id="form-container">
             <div>
                 <div class="progress" role="progressbar" aria-label="Animated striped example" aria-valuenow="0"
                     style="border-radius: 0" aria-valuemin="0" aria-valuemax="100">
                     <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%;"></div>
                 </div>
             </div>
             <div class="p-5">
                 {{-- <div>
                         @include($viewPath . '::components._form_partials._step_calendar')
                    </div> --}}
                 <div class="needs-validation" id="subsForm" method="post" name="form-wrapper" novalidate="">
                     <div id="steps-container">


                         <div id="stepperForm">
                             <div class="step" data-step="1">
                                 <div>
                                     <h4>Enter your email</h4>

                                     <div>
                                         <input type="email" style="width: 270px" id="emailInput"
                                             placeholder="Enter your email" />

                                         <button id="sendOtpBtn" class="next-btn" style="width: 126px">Send OTP</button>
                                     </div>

                                     <div>

                                         <div id="otpSection" style="display:none;">
                                             <input type="text" style="width: 270px" id="otpInput"
                                                 placeholder="Enter OTP" maxlength="6" />
                                             <button id="verifyOtpBtn" class="next-btn" style="width: 126px">Verify
                                                 OTP</button>
                                         </div>

                                     </div>

                                 </div>

                             </div>

                             <div class="step d-none" data-step="2">
                                 <div class="mb-5">
                                     <h4 style="font-weight: 800">Choose Plan</h4>
                                 </div>
                                 @foreach ($package->plans as $plan)
                                     <div class="q-box__question">
                                         <input class="form-check-input question__input" id="q{{ $plan->id }}"
                                             name="plan_id" type="radio" value="{{ $plan->id }}"
                                             @if ($loop->first) checked @endif>

                                         <label class="form-check-label question__label" for="q{{ $plan->id }}">
                                             <p>{{ $plan->description }}</p>
                                             <p>{{ priceFormat($plan->amount) }} / {{ $plan->duration }}
                                                 {{ $plan->type_name }}</p>
                                         </label>
                                     </div>
                                 @endforeach


                                 <div class="d-flex align-items-center justify-content-around mt-4" id="q-box__buttons">

                                     <button id="next-btn-addr" class="next-btn next" type="button">Next</button>
                                 </div>

                             </div>

                             <div class="step d-none" data-step="3">
                                 <div>
                                     @include($viewPath . '::components._form_partials._step_calendar')
                                 </div>
                                 <div class="d-flex align-items-center justify-content-around mt-4">

                                     <button type="button" class="btn btn-primary next next-btn">Next</button>
                                 </div>
                             </div>

                             <div class="step d-none" data-step="4">
                                 <div class="mb-5 d-flex align-items-center">
                                     <button type="button" class="btn btn-primary prev circle me-3">
                                         <i class="fa-solid fa-arrow-left"></i>
                                     </button>
                                     <h4 style="font-weight: 800">Contact Details</h4>
                                 </div>

                                 <div id="section-contact">
                                     <div class="row">
                                         <div class="col-12 col-md-6">
                                             <div class="mb-4">
                                                 <label class="form-label" for="fname">First Name:</label>
                                                 <input class="form-control" id="fname" name="fname"
                                                     type="text">
                                                 <small class="text-danger error-msg" data-error-for="fname"></small>
                                             </div>
                                         </div>
                                         <div class="col-12 col-md-6">
                                             <div class="mb-4">
                                                 <label class="form-label" for="lname">Last Name:</label>
                                                 <input class="form-control" id="lname" name="lname"
                                                     type="text">
                                                 <small class="text-danger error-msg" data-error-for="lname"></small>
                                             </div>
                                         </div>
                                         <div class="col-12">
                                             <div class="mb-4">
                                                 <label class="form-label" for="email">Email:</label>
                                                 <input class="form-control" id="email" name="email"
                                                     type="email">
                                                 <small class="text-danger error-msg" data-error-for="email"></small>
                                             </div>
                                         </div>
                                         <div class="col-12">
                                             <div class="mb-4">
                                                 <label class="form-label" for="phone_no">Phone Number:</label>
                                                 <input class="form-control phone_no" id="phone_no" name="phone_no"
                                                     type="text">
                                                 <small class="text-danger error-msg"
                                                     data-error-for="phone_no"></small>
                                             </div>
                                         </div>
                                     </div>

                                 </div>

                                 <div class="d-flex align-items-center justify-content-around mt-4">

                                     <button type="button" class="btn btn-primary next next-btn">Next</button>
                                 </div>
                             </div>

                             <div class="step d-none" data-step="5" id="section-address">
                                 <div class="mb-5 d-flex align-items-center">
                                     <button type="button" class="btn btn-primary prev circle me-3">
                                         <i class="fa-solid fa-arrow-left"></i>
                                     </button>
                                     <h4 style="font-weight: 800">Service Address</h4>
                                 </div>
                                 <div>
                                     <div class="row">
                                         <div class="col-12 col-md-12">
                                             <div class="mb-4">
                                                 <label class="form-label">Address</label>
                                                 <input class="form-control" id="add1" name="add1"
                                                     type="text">
                                                 <small class="text-danger error-msg" data-error-for="add1"></small>
                                             </div>
                                         </div>

                                         <div class="col-12 col-md-12">
                                             <div class="mb-4">
                                                 <label class="form-label">Address 2:</label>
                                                 <input class="form-control" id="add2" name="add2"
                                                     type="text">
                                                 <small class="text-danger error-msg" data-error-for="add2"></small>
                                             </div>
                                         </div>

                                         <div class="col-12 col-md-4">
                                             <div class="mb-4">
                                                 <label class="form-label">City:</label>
                                                 <input class="form-control" id="city" name="city"
                                                     type="text">
                                                 <small class="text-danger error-msg" data-error-for="city"></small>
                                             </div>
                                         </div>

                                         <div class="col-12 col-md-4">
                                             <div class="mb-4">
                                                 <label class="form-label">State:</label>
                                                 <input class="form-control" id="state" name="state"
                                                     type="text">
                                                 <small class="text-danger error-msg" data-error-for="state"></small>
                                             </div>
                                         </div>

                                         <div class="col-12 col-md-4">
                                             <div class="mb-4">
                                                 <label class="form-label">ZIP:</label>
                                                 <input class="form-control" id="zip" name="zip"
                                                     type="text">
                                                 <small class="text-danger error-msg" data-error-for="zip"></small>
                                             </div>
                                         </div>
                                     </div>

                                 </div>
                                 <div class="d-flex align-items-center justify-content-around mt-4">

                                     <button type="button" class="btn btn-primary next next-btn">Next</button>
                                 </div>
                             </div>

                             <div class="step d-none" data-step="6">
                                 <div class="mb-5 d-flex align-items-center">
                                     <button type="button" class="btn btn-primary prev circle me-3">
                                         <i class="fa-solid fa-arrow-left"></i>
                                     </button>
                                     <h4 style="font-weight: 800">Additional Notes</h4>
                                 </div>
                                 <div>
                                     <div class="mb-4">
                                         {{-- <label class="form-label" for="description">Describe your cleaning
                                                needs:</label> --}}
                                         <textarea class="form-control" id="description" name="description" rows="4" placeholder=""></textarea>
                                         <small class="text-danger error-msg" data-error-for="description"></small>
                                     </div>
                                 </div>
                                 <div class="d-flex align-items-center justify-content-around mt-4">

                                     <button type="button" class="btn btn-primary next next-btn">Next</button>
                                 </div>
                             </div>

                             <div class="step d-none" data-step="7">
                                 <div class="mb-5 d-flex align-items-center">
                                     <button type="button" class="btn btn-primary prev circle me-3">
                                         <i class="fa-solid fa-arrow-left"></i>
                                     </button>
                                     <h4 style="font-weight: 800">Subscription Summary</h4>
                                 </div>
                                 <div id="reviewSection" class="mb-3">

                                     <div class="">
                                         <div class="d-flex justify-content-between align-items-center mb-1">
                                             <h6 class="fw-bold mb-3">Selected Plan</h6>
                                             <button class="btn btn-sm btn-outline-secondary back-step"
                                                 data-step="2">
                                                 <i class="fa-solid fa-pen-to-square"></i>
                                             </button>
                                         </div>
                                         <div class="mb-4 bg-light p-3">
                                             <p class="mb-1" id="rev-plan-name">Weekly Standard Plan</p>
                                             <span class="text-muted" id="rev-plan-type">$148.50 / 1 week</span>
                                         </div>

                                         <div class="d-flex justify-content-between align-items-center mb-1">
                                             <h6 class="fw-bold mb-3">Preferred Date & Time</h6>
                                             <button class="btn btn-sm btn-outline-secondary back-step"
                                                 data-step="3">
                                                 <i class="fa-solid fa-pen-to-square"></i>
                                             </button>
                                         </div>
                                         <div class="mb-4 bg-light p-3">
                                             <p class="mb-0" id="rev-date">July 17, 2025</p>
                                             <small id="rev-time">at 04:31 PM</small>
                                         </div>

                                         <div class="d-flex justify-content-between align-items-center mb-1">
                                             <h6 class="fw-bold mb-0">Contact Information</h6>
                                             <button class="btn btn-sm btn-outline-secondary back-step"
                                                 data-step="4">
                                                 <i class="fa-solid fa-pen-to-square"></i>
                                             </button>
                                         </div>
                                         <div class="mb-4 bg-light p-3">
                                             <p class="mb-1"><span id="rev-fname">Lekh Raj</span> <span
                                                     id="rev-lname">Rai</span></p>
                                             <p class="mb-1" id="rev-email">lekhraj@gmail.com</p>
                                             <p id="rev-phone_no">(323) 423-4242</p>
                                         </div>

                                         <div class="d-flex justify-content-between align-items-center mb-1">
                                             <h6 class="fw-bold mb-3">Service Address</h6>
                                             <button class="btn btn-sm btn-outline-secondary back-step"
                                                 data-step="5">
                                                 <i class="fa-solid fa-pen-to-square"></i>
                                             </button>
                                         </div>
                                         <div class="mb-4 bg-light p-3">
                                             <p class="mb-1"><span id="rev-add1"></span> <span
                                                     id="rev-add2"></span></p>
                                             <p><span id="rev-city"></span>, <span id="rev-state"></span> <span
                                                     id="rev-zip"></span></p>
                                         </div>

                                         <div class="d-flex justify-content-between align-items-center mb-1">
                                             <h6 class="fw-bold mb-3">Additional Note</h6>
                                             <button class="btn btn-sm btn-outline-secondary back-step"
                                                 data-step="6">
                                                 <i class="fa-solid fa-pen-to-square"></i>
                                             </button>
                                         </div>
                                         <div class="mb-4 bg-light p-3">
                                             <p class="mb-0" id="rev-description">No Description</p>
                                         </div>
                                     </div>
                                 </div>
                                 <div>
                                     <h6 class="fw-bold mb-3"><i class="bi bi-box-seam me-2"></i>Payment</h6>
                                     <div class="mb-4">
                                         <form id="payment-form">
                                             <div class="bg-light p-3 rounded">
                                                 <div id="card-element">
                                                     <!-- Stripe Card Element will go here -->
                                                 </div>
                                                 <div id="card-errors" role="alert"
                                                     style="color:red; margin-top:10px;"></div>
                                             </div>
                                             <div class="d-flex align-items-center justify-content-around">
                                                 <button id="submit-btn" class="next-btn btn btn-success"
                                                     type="submit">Proceed</button>

                                             </div>
                                             <div>
                                                 <p class="text-danger" style="text-align: center;padding: 20px;font-size: 14px;" id="payment-error"></p>
                                             </div>
                                         </form>

                                     </div>
                                 </div>

                             </div>

                         </div>

                     </div>

                 </div>
             </div>
         </div>
     </div>
 </div>
 <script>
     $(document).ready(function() {
         window.package = @json($package);

         // Global AJAX setup for CSRF (only once)
         if (typeof $._csrfInitialized === "undefined") {
             $.ajaxSetup({
                 headers: {
                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                 }
             });
             $._csrfInitialized = true;
         }

         /**
          * Global reusable sendAjax function with optional finally callback
          * @param {Object} config - { url, method, data, dataType }
          * @param {Function} onSuccess - success callback
          * @param {Function} onError - error callback
          * @param {Function} onFinally - final callback (always called)
          */
         function sendAjax({
                 url = '',
                 method = 'POST',
                 data = {},
                 dataType = 'json'
             },
             onSuccess = () => {},
             onError = () => {},
             onFinally = () => {}
         ) {
             $.ajax({
                 url: url,
                 type: method,
                 data: data,
                 dataType: dataType,
                 success: onSuccess,
                 error: onError,
                 complete: onFinally
             });
         }

         $('.phone_no').inputmask('(999) 999-9999');

         // Number of steps you have:
         const totalSteps = $(".step").length; // = 6 in your case


         function updateProgressBar(value) {
             $(".progress-bar").first().css("width", `${value}%`);
         }





         // ---------- helpers ----------
         function showError(id, msg = "Required") {
             $(`#${id}`).addClass("is-invalid");
             $(`.error-msg[data-error-for="${id}"]`).text(msg);
         }

         function clearError(id) {
             $(`#${id}`).removeClass("is-invalid");
             $(`.error-msg[data-error-for="${id}"]`).text("");
         }

         function isEmail(v) {
             return /^[\w-.]+@([\w-]+\.)+[\w-]{2,}$/i.test(v);
         }

         function isPhone(v) {
             return /^\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/.test(v);
         }

         function isZip(v) {
             return /^\d{5}(-\d{4})?$/.test(v);
         }



         let currentStep = 1;

         updateProgressBar((currentStep));

         function showStep(step) {
             $(".step").addClass("d-none");
             $('.step[data-step="' + step + '"]').removeClass("d-none");
             updateProgressBar((100 / 7) * (step));
         }

         // Step 1: Email verification
         $('#sendOtpBtn').on('click', function() {
             const email = $('#emailInput').val().trim();
             if (!email || !email.includes('@')) {
                 alert('Please enter a valid email.');
                 return;
             }

             const $btn = $(this);

             // Disable button and show loading spinner
             $btn
                 .attr('disabled', true)
                 .html(`<i class="fas fa-spinner fa-spin me-1"></i> Sending...`);

             sendAjax({
                 url: "/login-signup",
                 method: 'POST',
                 data: {
                     email
                 },
             }, function(response) {
                 $('#otpSection').show();

                 // ✅ Restore button after success
                 $btn
                     .attr('disabled', false)
                     .html(`Send OTP`);
             }, function(error) {
                 console.error('Error sending OTP:', {
                     error
                 });
                 // ✅ Restore button after error
                 $btn
                     .attr('disabled', false)
                     .html(`Send OTP`);
             });
         });

         $('#verifyOtpBtn').on('click', function() {
             const email = $('#emailInput').val().trim();
             const otp = $('#otpInput').val().trim();
             if (!otp || otp.length !== 6) {
                 return;
             }
             sendAjax({
                 url: "/verify-otp",
                 method: 'POST',
                 data: {
                     email,
                     otp
                 },
             }, function(response) {
                 currentStep++;
                 showStep(currentStep)

             }, function(error) {
                 console.log({
                     error
                 });
             });
             // Call backend to verify OTP
         });


         function validateStep1() { // Choose plan
             if (!$('input[name="plan_id"]:checked').length) {
                 alert("Please select a plan.");
                 return false;
             }
             return true;
         }

         function validateStep2() { // Date & time
             let ok = true;
             if (!$("#preferred_date").val()) {
                 showError("preferred_date");
                 ok = false;
             } else {
                 clearError("preferred_date");
             }
             if (!$("#preferred_time").val()) {
                 showError("preferred_time");
                 ok = false;
             } else {
                 clearError("preferred_time");
             }
             return ok;
         }

         function validateStep3() { // Contact
             let ok = true;

             const map = [{
                     id: "fname",
                     rule: v => v.trim() ? true : "Required"
                 },
                 {
                     id: "lname",
                     rule: v => v.trim() ? true : "Required"
                 },
                 {
                     id: "email",
                     rule: v => {
                         if (!v.trim()) return "Required";
                         if (!isEmail(v)) return "Invalid e‑mail";
                         return true;
                     }
                 },
                 {
                     id: "phone_no",
                     rule: v => {
                         if (!v.trim()) return "Required";
                         if (!isPhone(v)) return "Invalid phone number";
                         return true;
                     }
                 }
             ];

             let firstInvalid = null;

             map.forEach(({
                 id,
                 rule
             }) => {
                 const value = $(`#${id}`).val();
                 const result = rule(value);

                 if (result !== true) {
                     showError(id, result); // result contains error message
                     if (!firstInvalid) firstInvalid = id;
                     ok = false;
                 } else {
                     clearError(id);
                 }
             });
             return ok;
         }

         // Regex for 5‑ or 6‑digit ZIP / postal codes (tweak for your locale)
         const zipPattern = /^\d{5,6}(-\d{4})?$/;

         function validateStep4() { // --- Service Address step ---
             const checks = [{
                     id: "add1",
                     test: v => v.trim().length,
                     msg: "Required"
                 },
                 {
                     id: "city",
                     test: v => v.trim().length,
                     msg: "Required"
                 },
                 {
                     id: "state",
                     test: v => v.trim().length,
                     msg: "Required"
                 },
                 {
                     id: "zip",
                     test: v => v.trim().length,
                     msg: "Required"
                 }
                 // add2 is optional — no test needed
             ];

             let firstInvalid = null;

             checks.forEach(({
                 id,
                 test,
                 msg
             }) => {
                 const val = $(`#${id}`).val();
                 if (!test(val)) {
                     showError(id, msg);
                     if (!firstInvalid) firstInvalid = id;
                 } else {
                     clearError(id);
                 }
             });

             // If something failed, scroll it into view
             if (firstInvalid) {
                 document.getElementById(firstInvalid).scrollIntoView({
                     behavior: "smooth",
                     block: "center"
                 });
                 return false;
             }
             return true;
         }


         // Step 5 (notes) is optional; validate only if you require content.
         function validateStep5() {
             return true;
         }


         const allowedNames = ['fname', 'lname', 'email', 'phone_no', 'add1', 'add2', 'city', 'state', 'zip',
             'description'
         ];

         $("input, textarea").on("input", function() {
             const name = $(this).attr("name");

             if (allowedNames.includes(name)) {
                 const value = $(this).val();
                 $("#rev-" + name).text(value);
             } else {
                 $("#rev-" + name).text("");
             }
         });



         $(".next").on("click", function() {
             const validators = {
                 2: validateStep1,
                 3: validateStep2,
                 4: validateStep3,
                 5: validateStep4,
                 6: validateStep5
             };

             console.log({
                 currentStep
             });

             if (!validators[currentStep]()) return; // stop if validation fails

             // build review block right before step 6
             if (currentStep === 5) buildReviewHtml();

             if (currentStep < $(".step").length) {
                 currentStep++;
                 showStep(currentStep);
             }
         });

         function formatAmount(amount, currency = 'USD', locale = 'en-US') {
             return new Intl.NumberFormat(locale, {
                 style: 'currency',
                 currency: currency,
                 minimumFractionDigits: 2
             }).format(amount);
         }


         function buildReviewHtml() {
             const selectedPlanValue = $('input[name="plan_id"]:checked').val();
             const plan = window.package.plans.find(plan => plan.id == selectedPlanValue);


             $("#rev-plan-name").text(plan.description);
             $("#rev-plan-type").text(formatAmount(plan.amount) + " / " + plan.duration + " " + plan.type_name);
             // const planText = selectedPlan.closest(".q-box__question").find("label").text().trim();

             const dateVal = $("#preferred_date").val();
             const timeVal = $("#preferred_time").val();
             const [hr, min] = timeVal.split(":");
             const timeObj = new Date();
             timeObj.setHours(hr);
             timeObj.setMinutes(min);
             const formattedDate = new Date(dateVal).toLocaleDateString("en-US", {
                 month: "long",
                 day: "numeric",
                 year: "numeric"
             });
             const formattedTime = timeObj.toLocaleTimeString("en-US", {
                 hour: "2-digit",
                 minute: "2-digit",
                 hour12: true
             });

             $("#rev-time").text(formattedTime);
             $("#rev-date").text(formattedDate)
         }



         $(".prev").on("click", function() {
             currentStep--;
             showStep(currentStep);
         });

         showStep(currentStep);


         const stripePublicKey = "{{ $stripe_public_key }}";
         var stripe, card;

         if (stripePublicKey) {
             initializeStripe(stripePublicKey);
         }


         function initializeStripe(publicKey) {
             stripe = Stripe(publicKey);
             const elements = stripe.elements();

             card = elements.create('card', {
                 style: {
                     base: {
                         fontSize: '16px',
                         color: '#32325d',
                         '::placeholder': {
                             color: '#a0aec0'
                         },
                     },
                 },
             });

             card.mount('#card-element');

             card.on('change', function(event) {
                 const displayError = document.getElementById('card-errors');
                 if (displayError) {
                     displayError.textContent = event.error ? event.error.message : '';
                 }
             });
         }

         const form = document.getElementById('payment-form');

         form.addEventListener('submit', async (event) => {
             event.preventDefault();

             // Show loading state
             $("#submit-btn").html(`<i class="fas fa-spinner fa-spin me-2"></i> Wait...`);
             $("#submit-btn").prop("disabled", true);

             let contactData = [];
             let addressData = [];
             $('#section-contact input').each(function() {
                 const $field = $(this);
                 const name = $field.attr('name');
                 if (!name) return;

                 contactData[name] = $field.val();
             });

             $('#section-address input').each(function() {
                 const $field = $(this);
                 const name = $field.attr('name');
                 if (!name) return;

                 addressData[name] = $field.val();
             });


             const {
                 paymentMethod,
                 error: pmError
             } = await stripe.createPaymentMethod({
                 type: 'card',
                 card: card, // <-- FIXED HERE
                 billing_details: {
                     email: contactData['email'],
                     name: contactData['fname'] + " " + contactData["lname"],
                     phone: contactData['phone_no'],
                     address: {
                         line1: addressData['add1'],
                         line2: addressData['add2'] || '', // Optional
                         city: addressData['city'],
                         state: addressData['state'],
                         postal_code: addressData['zip'],
                         //  country: addressData['country'], // Must be 2-letter code
                     }
                 },
             });

             if (pmError) {
                 $("#submit-btn").html("Proceed").prop("disabled", false);
                 document.getElementById('card-errors').textContent = pmError.message;
                 return;
             }

             sendAjax({
                 url: '/add-payment-method',
                 method: 'POST',
                 data: {
                     customer_name: contactData['fname'] + " " + contactData["lname"],
                     customer_email: contactData['email'],
                     customer_phone: contactData['phone_no'],
                     payment_method_id: paymentMethod.id,
                 }
             }, (response) => {
                 handleSubscribe({
                     payment_method_id: paymentMethod.id,
                     stripe_customer: response.data.stripe_customer,
                     contactData,
                     addressData,
                 })
             }, (error) => {
                 $("#submit-btn").html("Proceed").prop("disabled", false);
                 $("#payment-error").text("Failed to add payment method. Please try again. " + error.responseJSON.message);
             })
         });

         function handleSubscribe(params) {
             let addr = params.addressData,
                 contact = params.contactData;

             let selectedPlanId = $('input[name="plan_id"]:checked').val();

             sendAjax({
                 url: `/package-plans/${selectedPlanId}/subscribe`,
                 method: 'POST',
                 data: {
                     customer_email: params.contactData["email"],
                     customer_name: params.contactData["fname"],
                     customer_phone: params.contactData["phone_no"],
                     stripe_customer_id: params.stripe_customer,
                     payment_method_id: params.payment_method_id,
                     address: {
                         add1: addr['add1'],
                         add2: addr['add2'] || '', // Optional
                         city: addr['city'],
                         state: addr['state'],
                         zip: addr['zip'],
                         country: '', // Must be 2-letter code
                     },
                     contact: {
                         fname: contact['fname'],
                         lname: contact['lname'],
                         email: contact['email'],
                         phone_no: contact['phone_no'],
                     },
                     preferred_date: $("#preferred_date").val(),
                     preferred_time: $("#preferred_time").val(),
                 }
             }, (response) => {

                 $("#submit-btn").html("Submit").prop("disabled", false);
                 if (response.requires_action) {
                     confirmPayment(response.client_secret, params.payment_method_id, response
                         .packageSubscription);
                 } else {
                     $("#form-container").empty().append(response.temp);
                     console.log("subscription successful");
                 }
             }, (error) => {
                 $("#submit-btn").html("Proceed").prop("disabled", false);
                 $("#payment-error").text("Failed to subscribe. Please try again." + " " + error
                     .responseJSON.message);
             })
         }

         async function confirmPayment(clientSecret, paymentMethodId, packageSubscription) {

             if (!clientSecret || !paymentMethodId) return;


             try {
                 const result = await stripe.confirmCardPayment(clientSecret, {
                     payment_method: paymentMethodId
                 });

                 if (result.error) {
                     $("#submit-btn").html("Proceed").prop("disabled", false);
                     $("#payment-error").text("Payment failed: " + result.error.message);
                 } else if (result.paymentIntent.status === 'succeeded') {
                     savePayment(result.paymentIntent, packageSubscription);
                     console.log("Payment successful!", result.paymentIntent);

                 }
             } catch (err) {
                 console.log("Something went wrong: " + err.message);
                 $("#submit-btn").html("Proceed").prop("disabled", false);
                 $("#payment-error").text("Unexpected error: " + err.message);
             }
         }

         function savePayment(paymentIntent, packageSubscription) {
             try {
                 sendAjax({
                     url: `/package-subscriptions/${packageSubscription.id}/store-card-payment`,
                     method: 'POST',
                     data: {
                         payment_intent_id: paymentIntent.id,
                         subscription_id: packageSubscription.id,
                         payment_method_id: paymentIntent.payment_method,
                         amount: paymentIntent.amount / 100,
                     }
                 }, (response) => {
                     $("#form-container").empty().append(response.temp);

                 }, (error) => {
                     this.loading = false;
                     $("#submit-btn").html("Proceed").prop("disabled", false);
                     $("#payment-error").text("Error processing payment: " + error.responseJSON
                         .message || error.statusText);
                 });
             } catch (error) {
                 this.loading = false
                 $("#submit-btn").html("Proceed").prop("disabled", false);
                 $("#payment-error").text("Unexpected error: " + error.message);
             }
         }


         function submitForm(token) {
             const contactData = {},
                 addressData = {};

             $('#section-contact input').each(function() {
                 const $field = $(this);
                 const name = $field.attr('name');
                 if (!name) return;

                 contactData[name] = $field.val();
             });

             $('#section-address input').each(function() {
                 const $field = $(this);
                 const name = $field.attr('name');
                 if (!name) return;

                 addressData[name] = $field.val();
             });


             const planId = $('input[name="plan_id"]:checked').val();

             const requestData = {
                 contact: contactData,
                 address: addressData,
                 // service_selected: selected_answers,
                 stripeToken: token.id,
                 plan_id: planId,
                 description: $("#description").val(),
                 preferred_date: $("#preferred_date").val(),
                 preferred_time: $("#preferred_time").val(),
             };



             sendAjax({
                 url: `package-plans/${planId}/subscribe`,
                 method: 'POST',
                 data: requestData,
             }, function(response) {
                 $("#form-container").empty().append(response.temp);
             }, function(xhr, status, error) {
                 // $("#submit-btn").text('Pay');
                 $("#submit-btn").html("Pay").prop("disabled", false);
                 console.error("Error submitting form:", error);
                 // Handle error (e.g., show an error message)
             });

         }

         $(".back-step").on('click', function(e) {
             e.preventDefault();
             currentStep = $(this).attr('data-step');
             if (currentStep > 0) {
                 $("#nextStep").removeClass('d-none');
             }
             showStep(currentStep);
         })

     });
 </script>
