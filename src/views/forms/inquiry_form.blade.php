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
            width: 190px;
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


        .step-panel {
            display: none;
        }

        .step-panel.active {
            display: block;
        }

        .step-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .step-item {
            flex: 1;
            text-align: center;
            position: relative;
            font-weight: 500;
        }

        .step-item::before {
            content: attr(data-step);
            display: inline-block;
            width: 34px;
            height: 34px;
            line-height: 34px;
            border-radius: 50%;
            background: #adb5bd;
            color: #fff;
            margin-bottom: 6px;
        }

        .step-item::after {
            content: "";
            position: absolute;
            top: 17px;
            left: 50%;
            right: -50%;
            height: 4px;
            background: #dee2e6;
            z-index: -1;
        }

        .step-item:last-child::after {
            display: none;
        }

        .step-item.active::before,
        .step-item.completed::before {
            background: #0d6efd;
        }

        .step-item.completed::after {
            background: #0d6efd;
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

                    <div id="left-normal">
                        @include($viewPath.'::frontend.forms._left_panel_1')
                    </div>
                    <div class="d-none" id="left-review">
                        @include($viewPath.'::frontend.forms._left_panel')
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
                    <form id="stepForm" novalidate>
                        <div class="step-panel active">
                            {{-- @include($viewPath.'::frontend.forms._step_questions') --}}
                            @include($viewPath.'::frontend.forms._step_questions')
                        </div>

                        <div class="step-panel">
                            @include($viewPath.'::frontend.forms._step_calendar')
                        </div>

                        <div class="step-panel">
                            @include($viewPath.'::frontend.forms._step_contact')
                        </div>

                        <div class="step-panel">
                            @include($viewPath.'::frontend.forms._step_address')
                        </div>

                        <div class="step-panel">

                            @include($viewPath.'::frontend.forms._step_review')

                        </div>

                </div>

                {{-- </div> --}}
                <div class="d-flex align-items-center justify-content-around mt-4">
                    <button class="next-btn d-none" id="nextStep" type="button">Next</button>
                </div>
                </form>
            </div>
        </div>
    </div>
    </div>


    <!-- jQuery & Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>

    <script>
        window.service_categories = @json($service_categories);

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



        $(document).ready(function() {

            $('.phone_no').inputmask('(999) 999-9999');




            // Use the data from window.service_categories
            const serviceData = window.service_categories || [];
            let stepStack = [];
            let currentOptions = serviceData;
            let selected_answers = [];

            function renderStep(options, questionText, inputName = 'service_categories') {
                $("#stepTitle").text(questionText);
                $("#stepOptions").empty();
                $(".question-error-msg").text("");

                options.forEach(opt => {
                    $("#stepOptions").append(`
                        <div class="q-box__question">
                            <input class="form-check-input question__input" type="radio" name="${inputName}" id="opt_${opt.id}" value="${opt.id}">
                            <label class="form-check-label question__label" for="opt_${opt.id}">${opt.name}</label>
                        </div>
                    `);
                });


                $("#btnBack").toggleClass('d-none', stepStack.length === 0);
            }

            // Initial render
            renderStep(serviceData, "Select a Service Category");

            $("#btnNext").on("click", function() {
                const selectedId = $("input[type=radio]:checked").val();
                if (!selectedId) {
                    $(".question-error-msg").text("Please select an option.");
                    return;
                }

                const selected = currentOptions.find(opt => opt.id == selectedId);
                selected_answers.push(selected);

                // Drill down to next level if exists
                if (selected.services && selected.services.length) {
                    stepStack.push(currentOptions);
                    currentOptions = selected.services;
                    renderStep(currentOptions, selected.question_text || "Select a service", "service_id");
                } else if (selected.children && selected.children.length) {
                    stepStack.push(currentOptions);
                    currentOptions = selected.children;
                    renderStep(currentOptions, selected.question_text || "Choose options", "service_id");
                } else {
                    $("#nextStep").removeClass('d-none')
                    currentStep++;
                    updateStepView();
                }
            });

            $("#btnBack").on("click", function() {
                if (stepStack.length === 0) return;
                currentOptions = stepStack.pop();
                selected_answers.pop();

                const last = selected_answers[selected_answers.length - 1];
                const label = last?.question_text || "Select a Service Category";
                // Detect input name based on if the options have 'services' key
                const inputName = currentOptions[0]?.services ? "service_categories" : "service_id";
                renderStep(currentOptions, label, inputName);
            });




            function validateContactInfo() {
                let isValid = true;

                // Clear all previous errors
                $(".error-msg").text("");

                $('#stepContactInfo input').each(function() {
                    const input = $(this);
                    const name = input.attr('name');
                    const value = input.val().trim();
                    const errorElement = $(`.error-msg[data-error-for="${name}"]`);

                    if (!value) {
                        errorElement.text(`${input.prev('label').text().replace(':', '')} is required.`);
                        isValid = false;
                    } else {
                        // Additional field-specific validation
                        if (name === "email") {
                            const emailRegex = /^\S+@\S+\.\S+$/;
                            if (!emailRegex.test(value)) {
                                errorElement.text("Invalid email format.");
                                isValid = false;
                            }
                        }

                        if (name === "phone_no") {
                            // Remove all non-digit characters (e.g., +, -, space, etc.)
                            const digitsOnly = value.replace(/\D/g, '');

                            if (digitsOnly.length !== 10) {
                                errorElement.text("Enter valid phone number");
                                isValid = false;
                            }
                        }

                    }
                });

                return isValid;
            }


            const allowedNames = ['fname', 'lname', 'email', 'phone_no', 'add1', 'add2', 'city', 'state', 'zip'];
            $("input").on("input", function() {
                const name = $(this).attr("name");

                if (allowedNames.includes(name)) {
                    const value = $(this).val();
                    $("#rev-" + name).text(value);
                } else {
                    $("#rev-" + name).text("");
                }
            });


            // service address step

            function validateAddressForm() {
                let isValid = true;

                $(".stepAddressInfo input").each(function() {
                    const $input = $(this);
                    const name = $input.attr("name");
                    let value = $input.val().trim();
                    const errorElement = $input.siblings(".error-msg");

                    errorElement.text(""); // Clear previous error

                    // Required fields
                    const requiredFields = ["add1", "city", "state", "zip"];
                    if (requiredFields.includes(name) && value === "") {
                        errorElement.text("Required.");
                        isValid = false;
                        return; // continue to next input
                    }

                    // ZIP code validation
                    if (name === "zip") {
                        value = value.replace(/\D/g, ''); // Remove all non-digit chars
                        if (value.length < 4 || value.length > 10) {
                            errorElement.text("Invalid ZIP");
                            isValid = false;
                        }
                        $input.val(value);
                    }

                    // Sanitize other text fields
                    if (name !== "zip") {
                        value = value.replace(/[^\w\s\-.,]/g, ''); // Remove special characters
                        $input.val(value);
                    }
                });

                return isValid;
            }


            function updateProgress(value) {
                $(".progress-bar").first().css("width", `${value}%`);
            }

            $(document).off('click', '#submit-btn-review').on('click', '#submit-btn-review', function() {
                // Collect all data to submit

                const $btn = $(this);

                // Disable button and add spinner
                $btn
                    .attr('disabled', true)
                    .html(`<i class="fas fa-spinner fa-spin me-1"></i> Submitting...`);


                let formatSelected = selected_answers.map((item) => {
                    return {
                        id: item.id,
                        name: item.name,
                        service_category_id: item.service_category_id ? item.service_category_id :
                            item.id,
                        price: item.price,
                        question_text: item.question_text,
                        type: item.service_category_id ? "service" : "service_category"
                    }
                })

                const formData = {
                    contact: {
                        fname: $("#fname").val(),
                        lname: $("#lname").val(),
                        email: $("#email").val(),
                        phone_no: $("#phone_no").val()
                    },
                    address: {
                        add1: $("#add1").val(),
                        add2: $("#add2").val(),
                        city: $("#city").val(),
                        state: $("#state").val(),
                        zip: $("#zip").val()
                    },
                    service_selected: formatSelected,
                    preferred_date: $("#preferred_date").val(),
                    preferred_time: $("#preferred_time").val(),
                };

                sendAjax({
                    url: "/free-estimate",
                    method: 'POST',
                    data: formData,
                }, function(response) {
                    $("#form-container").empty().append(response.temp);
                    $("#left-normal").removeClass("d-none");
                    $("#left-review").addClass("d-none");
                }, function(xhr, status, error) {
                    console.error("Error submitting form:", error);
                    $btn
                        .attr('disabled', false)
                        .html(`Submit`);
                    // Handle error (e.g., show an error message)
                });
            });


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



            function validateDateTime() { // Date & time
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


            // form ian steps

            let currentStep = 0;
            const $panels = $(".step-panel");
            const $indicators = $(".step-item");

            function updateStepView() {
                $panels.removeClass("active").eq(currentStep).addClass("active");

                $indicators.removeClass("active completed");
                $indicators.each(function(index) {
                    if (index < currentStep) {
                        $(this).addClass("completed");
                    } else if (index === currentStep) {
                        $(this).addClass("active");
                    }
                });

                updateProgress((100 / 5) * (currentStep + 1));

                $("#prevStep").toggle(currentStep !== 0);
                $("#nextStep").text(currentStep === $panels.length - 1 ? "Submit" : "Next");
            }

            $("#nextStep").click(function() {
                switch (currentStep) {
                    case 0:
                        currentStep++;
                        updateStepView();
                        break;
                    case 1:
                        if (!validateDateTime()) {
                            return;
                        } else {
                            currentStep++;
                            updateStepView();
                        }
                        break;
                    case 2:
                        if (!validateContactInfo()) {
                            return;
                        } else {
                            $("#nextStep").removeClass('d-none');
                            currentStep++;
                            updateStepView();
                        }
                        break;
                    case 3:
                        if (!validateAddressForm()) {
                            return;
                        } else {

                            $("#nextStep").addClass('d-none');
                            let questionTemplate = ``;

                            selected_answers.forEach((element, key) => {
                                questionTemplate += `
                                    <li>
                                    <div class="p-3 d-flex">
                                        <div>
                                            <p>${key+1}.</p>
                                        </div>
                                        <div class="ps-3">
                                            <p style="font-weight: 300; font-size:smaller">${element.question_text}</p>
                                            <p class="ps-1">${element.name}</p>
                                        </div>
                                    </div>
                                </li>
                                    `;
                            });
                            $("#review-questions").html(questionTemplate);

                            $('#left-review').removeClass('d-none');
                            $('#left-normal').addClass('d-none');
                            appendDateTimeToReview();
                            currentStep++;
                            updateStepView();
                        }
                        break;
                    default:
                        break;
                }

            });

            function appendDateTimeToReview() {
                const rawDate = $("#preferred_date").val(); // format: YYYY-MM-DD
                const rawTime = $("#preferred_time").val(); // format: HH:MM (24-hour)

                // Format Date: MM/DD/YYYY
                const formattedDate = new Date(rawDate).toLocaleDateString('en-US');

                // Format Time: hh:mm AM/PM
                const timeParts = rawTime.split(":");
                let hours = parseInt(timeParts[0]);
                const minutes = timeParts[1];
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12 || 12; // Convert 0 to 12 for 12 AM
                const formattedTime = `${hours}:${minutes} ${ampm}`;

                // Set to review elements
                $("#rev-date").text(formattedDate);
                $("#rev-time").text(formattedTime);

            }

            $(".prevStep").click(function() {

                if (currentStep > 0) {
                    $("#nextStep").removeClass('d-none')
                    currentStep--;
                    updateStepView();
                }
                if (currentStep <= 1) {
                    $("#nextStep").addClass('d-none')
                }
            });

            updateStepView();





            function showErrorMessage(msg) {
                $(".question-error-msg").text(msg);
            }

            function clearErrorMessage() {
                $(".question-error-msg").text('');
            }


            $(document).off('click', '#inner-next-btn').on('click', '#inner-next-btn', function(e) {
                e.preventDefault();
                clearErrorMessage();
                const $form = $("#stepQuestion");

                let groupName = null;
                let $inputs = null;

                // Step 1: Detect which group is present
                if ($form.find("input[name='service_categories']").length > 0) {
                    groupName = 'service_categories';
                    $inputs = $form.find("input[name='service_categories']");


                    const categoryId = $inputs.filter(":checked").val();

                    if (!categoryId) {
                        showErrorMessage("Please select option");
                        return;

                    } else {
                        let selectedAnswer = stepQuestions.find(cat => cat.id == categoryId);
                        $("#btnPrev").removeClass('d-none');

                        selected_answers.push({
                            "service_category_id": categoryId,
                            "category_name": selectedAnswer?.name,
                            "question_id": selectedAnswer?.id,
                            "question_text": selectedAnswer?.question || "Service Category",
                            "type": groupName,
                            "id": categoryId,
                        });

                        stepQuestions = stepQuestions.find(cat => cat.id == categoryId)?.children || [];

                        $("#question").text(selectedAnswer.question);

                        $("#answerOptions").empty();
                        stepQuestions.forEach(q => {
                            $("#answerOptions").append(`
                            <div class="q-box__question">
                            <input class="form-check-input question__input" id="q_${q.id}" data-price="${q.price}"
                                name="service_id" type="radio" value="${q.id}">
                            <label class="form-check-label question__label"
                                    for="q_${q.id}">${q.name}</label>
                            </div>
                        `);
                        })

                        answerIndex++;
                        questionIndex++;
                    }

                } else if ($form.find("input[name='service_id']").length > 0) {
                    groupName = 'service_id';
                    $inputs = $form.find("input[name='service_id']");

                    const serviceId = $inputs.filter(":checked").val();
                    if (!serviceId) {
                        showErrorMessage("Please select option");
                        return;
                    }

                    let selectedAnswer = stepQuestions.find(service => service.id == serviceId);

                    selected_answers.push({
                        "service_id": serviceId,
                        "service_name": selectedAnswer?.name,
                        "question_id": selectedAnswer?.id,
                        "question_text": selectedAnswer?.question_text || "No question provided",
                        "type": "service",
                        "id": serviceId,
                    });

                    stepQuestions = stepQuestions.find(service => service.id == serviceId)?.children || [];

                    if (stepQuestions.length > 0) {
                        $("#question").text(selectedAnswer.question_text);
                        $("#answerOptions").empty();
                        stepQuestions.forEach(q => {
                            $("#answerOptions").append(`
                            <div class="q-box__question">
                                <input class="form-check-input question__input" id="q_${q.id}" data-price="${q.price}"
                                name="service_id" type="radio" value="${q.id}">
                                <label class="form-check-label question__label"
                                for="q_${q.id}">${q.name}</label>
                                </div>
                                `);
                        })
                    } else {
                        $("#inner-next-btn").addClass('d-none');
                        $("#nextStep").removeClass('d-none');
                        currentStep++;
                        updateStepView();
                    }

                    answerIndex++;
                    questionIndex++;
                } else {
                    // No recognizable input group found
                    console.warn("No question group found.");
                    $form.find(".question-error-msg").text("No question options available.");
                    return;
                }


            });


            $(".back-step").on('click',function(e){
                e.preventDefault();
                console.log($(this).attr('data-step'));
                currentStep = $(this).attr('data-step') - 1;
                if(currentStep>0){
                    $("#nextStep").removeClass('d-none');
                }
                updateStepView();
            })
        });
    </script>


</body>

</html>
