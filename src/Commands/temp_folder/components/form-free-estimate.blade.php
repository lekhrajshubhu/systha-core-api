<!-- Main Content -->
<div class="inquiry-form custom-form container d-flex justify-content-center py-4">
    <div style="max-width: 900px;">
        <div class="row overflow-hidden">
            <!-- Left Panel -->
            <div class="col-md-4 bg-theme text-white p-4">
                <div class="h-100 d-flex justify-content-center">

                    <div id="left-normal">
                        @include($viewPath . '::components._form_partials._left_panel_1')
                    </div>
                    <div class="d-none" id="left-review">
                        @include($viewPath . '::components._form_partials._left_panel')
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
                            @include($viewPath . '::components._form_partials._step_questions')
                        </div>

                        <div class="step-panel">
                            @include($viewPath . '::components._form_partials._step_calendar')
                        </div>

                        <div class="step-panel">
                            @include($viewPath . '::components._form_partials._step_contact')
                        </div>

                        <div class="step-panel">
                            @include($viewPath . '::components._form_partials._step_address')
                        </div>

                        <div class="step-panel">

                            @include($viewPath . '::components._form_partials._step_review')

                        </div>

                        <div class="d-flex align-items-center justify-content-around mt-4">
                            <button class="next-btn d-none" id="nextStep" type="button">Next</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        // Global AJAX setup for CSRF (only once)
        if (typeof $._csrfInitialized === "undefined") {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $._csrfInitialized = true;
        }


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



        function getServiceCategories(callback) {
            sendAjax({
                url: "/form-service-categories",
                method: 'GET',
            }, function(response) {
                window.service_categories = response.data;

                if (typeof callback === 'function') {
                    callback(response.data); // pass data to callback
                }

            }, function(xhr, status, error) {
                console.log(xhr);
            });
        }



        $('.phone_no').inputmask('(999) 999-9999');


        // Use the data from window.service_categories
        const serviceData = window.service_categories || [];

        let stepStack = [];
        let selected_answers = [];
        let currentOptions = [];
        getServiceCategories(function(serviceData) {
            // Initial render
            currentOptions = serviceData;
            renderStep(serviceData, "Select a Service Category");
        })

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

        $('#btnBackSchedule').on('click', function() {

            currentStep = 0;
            $("#nextStep").addClass('d-none');
            updateStepView();
        });

        $('#btnBackContact').on('click', function() {
            currentStep = 1;
            updateStepView();
        });
        $('#btnBackAddress').on('click', function() {
            currentStep = 2;
            updateStepView();
        });



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


        $(".back-step").on('click', function(e) {
            e.preventDefault();
            console.log($(this).attr('data-step'));
            currentStep = $(this).attr('data-step') - 1;
            if (currentStep > 0) {
                $("#nextStep").removeClass('d-none');
            }
            updateStepView();
        })
    });
</script>
