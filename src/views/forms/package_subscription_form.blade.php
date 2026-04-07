<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Free Estimation Form</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet" />
    <!-- Font Awesome 6 Free CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <script src="https://js.stripe.com/v3/"></script>

    <style>
        /* GENERAL */
        body {
            background: #f7f9ff;
            font-family: "Poppins", sans-serif;
            color: #555;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        p {
            margin: 0;
            padding: 0;
        }

        label {
            font-size: 16px;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: #00011c;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        ul li {
            margin: 0;
            padding: 0;

        }


        button.prev-btn,
        button.next-btn,
        button.submit-btn {
            font-size: 17px;
            font-weight: bold;
            position: relative;
            width: 150px;
            height: 50px;
            background: #2d6efd;
            margin-top: 40px;
            overflow: hidden;
            z-index: 1;
            cursor: pointer;
            transition: color 0.3s;
            text-align: center;
            color: #fff;
            border: 0;
            -webkit-border-bottom-right-radius: 5px;
            -webkit-border-bottom-left-radius: 5px;
            -moz-border-radius-bottomright: 5px;
            -moz-border-radius-bottomleft: 5px;
            border-bottom-right-radius: 5px;
            border-bottom-left-radius: 5px;
        }

        button.prev-btn:after,
        button.next-btn:after,
        button.submit-btn:after {
            position: absolute;
            top: 90%;
            left: 0;
            width: 100%;
            height: 100%;
            background: #0340c4;
            content: "";
            z-index: -2;
            transition: transform 0.3s;
        }

        button.prev-btn:hover::after,
        button.next-btn:hover::after,
        button.submit-btn:hover::after {
            transform: translateY(-80%);
            transition: transform 0.3s;
        }


        .form-check-input:checked[type=radio],
        .form-check-input:checked[type=radio]:hover,
        .form-check-input:checked[type=radio]:focus,
        .form-check-input:checked[type=radio]:active {
            border: none !important;
            -webkit-outline: 0px !important;
            box-shadow: none !important;
        }

        .form-check-input:focus,
        input[type="radio"]:hover {
            box-shadow: none;
            cursor: pointer !important;
        }

        .q-box__question {
            margin-bottom: 10px;
        }

        .question__input {
            position: absolute;
            left: -9999px;
        }

        .question__label {
            position: relative;
            display: block;
            line-height: 40px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            background-color: #fff;
            padding: 5px 20px 5px 50px;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
        }

        .question__label:hover {
            border-color: #2d6efd;
        }

        .question__label:before,
        .question__label:after {
            position: absolute;
            content: "";
        }

        .question__label:before {
            top: 12px;
            left: 10px;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background-color: #fff;
            box-shadow: inset 0 0 0 1px #ced4da;
            -webkit-transition: all 0.15s ease-in-out;
            -moz-transition: all 0.15s ease-in-out;
            -o-transition: all 0.15s ease-in-out;
            transition: all 0.15s ease-in-out;
        }

        .question__input:checked+.question__label:before {
            background-color: #2d6efd;
            box-shadow: 0 0 0 0;
        }

        .question__input:checked+.question__label:after {
            top: 22px;
            left: 18px;
            width: 10px;
            height: 5px;
            border-left: 2px solid #fff;
            border-bottom: 2px solid #fff;
            transform: rotate(-45deg);
        }

        .form-check-input:checked,
        .form-check-input:focus {
            background-color: #2d6efd !important;
            outline: none !important;
            border: none !important;
        }

        input:focus {
            outline: none;
        }

        #input-container {
            display: inline-block;
            box-shadow: none !important;
            margin-top: 36px !important;
        }

        label.form-check-label.radio-lb {
            margin-right: 15px;
        }

        #q-box__buttons {
            text-align: center;
        }

        input[type="text"],
        input[type="email"] {
            padding: 8px 14px;
        }

        input[type="text"]:focus,
        input[type="email"]:focus {
            border: 1px solid #2d6efd;
            border-radius: 5px;
            outline: 0px !important;
            -webkit-appearance: none;
            box-shadow: none !important;
            -webkit-transition: all 0.15s ease-in-out;
            -moz-transition: all 0.15s ease-in-out;
            -o-transition: all 0.15s ease-in-out;
            transition: all 0.15s ease-in-out;
        }

        .form-check-input:checked[type="radio"],
        .form-check-input:checked[type="radio"]:hover,
        .form-check-input:checked[type="radio"]:focus,
        .form-check-input:checked[type="radio"]:active {
            border: none !important;
            -webkit-outline: 0px !important;
            box-shadow: none !important;
        }

        .form-check-input:focus,
        input[type="radio"]:hover {
            box-shadow: none;
            cursor: pointer !important;
        }
    </style>
</head>

<body>

    <!-- Navbar with Logo and Title -->
    <nav class="navbar navbar-light bg-white shadow-sm sticky-top">
        <div class="container d-flex align-items-center">
            <a class="navbar-brand d-flex align-items-center gap-2" href="/">
                <img src="/logo-image" alt="Logo" height="40" class="" />
                <span class="fw-bold">{{ $vendor->name }}</span>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container d-flex justify-content-center py-3 my-3">
        <div class="row w-100 overflow-hidden" style="max-width: 900px;">
            <!-- Left Panel -->
            <div class="col-md-4 bg-primary text-white p-4">
                <div class="h-100 d-flex justify-content-center">


                    <div id="left-review">
                        @include($viewPath.'::frontend.forms._left_panel_package')
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
                         @include($viewPath.'::frontend.forms._step_calendar')
                    </div> --}}
                    <div class="needs-validation" id="subsForm" method="post" name="form-wrapper" novalidate="">
                        <div id="steps-container">


                            <div id="stepperForm">
                                <div class="step" data-step="1">
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


                                    <div class="d-flex align-items-center justify-content-around mt-4"
                                        id="q-box__buttons">

                                        <button id="next-btn-addr" class="next-btn next" type="button">Next</button>
                                    </div>

                                </div>

                                <div class="step d-none" data-step="2">
                                    <div>
                                        @include($viewPath.'::frontend.forms._step_calendar')
                                    </div>
                                    <div class="d-flex align-items-center justify-content-around mt-4">

                                        <button type="button" class="btn btn-primary next next-btn">Next</button>
                                    </div>
                                </div>

                                <div class="step d-none" data-step="3">
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
                                                    <small class="text-danger error-msg"
                                                        data-error-for="lname"></small>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="mb-4">
                                                    <label class="form-label" for="email">Email:</label>
                                                    <input class="form-control" id="email" name="email"
                                                        type="email">
                                                    <small class="text-danger error-msg"
                                                        data-error-for="email"></small>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="mb-4">
                                                    <label class="form-label" for="phone_no">Phone Number:</label>
                                                    <input class="form-control phone_no" id="phone_no"
                                                        name="phone_no" type="text">
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

                                <div class="step d-none" data-step="4" id="section-address">
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
                                                    <small class="text-danger error-msg"
                                                        data-error-for="add1"></small>
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-12">
                                                <div class="mb-4">
                                                    <label class="form-label">Address 2:</label>
                                                    <input class="form-control" id="add2" name="add2"
                                                        type="text">
                                                    <small class="text-danger error-msg"
                                                        data-error-for="add2"></small>
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-4">
                                                <div class="mb-4">
                                                    <label class="form-label">City:</label>
                                                    <input class="form-control" id="city" name="city"
                                                        type="text">
                                                    <small class="text-danger error-msg"
                                                        data-error-for="city"></small>
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-4">
                                                <div class="mb-4">
                                                    <label class="form-label">State:</label>
                                                    <input class="form-control" id="state" name="state"
                                                        type="text">
                                                    <small class="text-danger error-msg"
                                                        data-error-for="state"></small>
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


                                <div class="step d-none" data-step="5">
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

                                <div class="step d-none" data-step="6">
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
                                                    data-step="1">
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
                                                    data-step="2">
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
                                                    data-step="3">
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
                                                    data-step="4">
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
                                                    data-step="5">
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
                                                        type="submit">Pay</button>

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


    <!-- jQuery & Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>

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

            function updateProgressBar(currentStep, totalSteps) {
                const percent = ((currentStep - 1) / (totalSteps - 1)) * 100;
                $(".progress-bar")
                    .css("width", percent + "%")
                    .attr("aria-valuenow", percent);
                // .text(Math.round(percent) + "%");
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

            function showStep(step) {
                $(".step").addClass("d-none");
                $('.step[data-step="' + step + '"]').removeClass("d-none");

                updateProgressBar(step, $(".step").length); // 👈 Add this line
            }


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
                    1: validateStep1,
                    2: validateStep2,
                    3: validateStep3,
                    4: validateStep4,
                    5: validateStep5
                };

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

            // Replace with your own Stripe publishable key
            const stripe = Stripe("{{ $stripe_public_key }}");
            const elements = stripe.elements();

            // Create an instance of the card Element
            const card = elements.create('card', {
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

            // Mount the card Element into the DOM
            card.mount('#card-element');

            // Handle real-time validation errors from the card Element
            card.on('change', (event) => {
                const displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });


            const form = document.getElementById('payment-form');

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                // Add spinner and loading text
                $("#submit-btn").html(`<i class="fas fa-spinner fa-spin me-2"></i> Wait...`);
                $("#submit-btn").prop("disabled", true); // Optional: prevent double click

                const {
                    token,
                    error
                } = await stripe.createToken(card);

                if (error) {
                    // Restore original button
                    $("#submit-btn").html("Pay").prop("disabled", false);
                    document.getElementById('card-errors').textContent = error.message;
                } else {
                    submitForm(token); // send to backend
                }
            });


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
                console.log($(this).attr('data-step'));
                currentStep = $(this).attr('data-step');
                if (currentStep > 0) {
                    $("#nextStep").removeClass('d-none');
                }
                showStep(currentStep);
            })

        });
    </script>


</body>

</html>
