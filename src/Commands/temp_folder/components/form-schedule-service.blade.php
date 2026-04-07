<!-- Main Content -->
<!-- Main Content -->
<div class="container d-flex justify-content-center py-3 my-3 custom-form">
    <div class="row w-100 overflow-hidden" style="max-width: 1000px;">
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

            <div class="">
                <div>
                    <div class="progress" role="progressbar" aria-label="Animated striped example" aria-valuenow="0"
                        style="border-radius: 0" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%;">
                        </div>
                    </div>
                </div>
                <div class="p-5">
                    <div id="stepForm" novalidate>
                        <div id="step1" class="step-box">
                            <h4>Enter your email</h4>

                            <div>
                                <input type="email" style="width: 270px" id="emailInput"
                                    placeholder="Enter your email" />

                                <button id="sendOtpBtn" class="next-btn" style="width: 126px">Send OTP</button>
                            </div>

                            <div>

                                <div id="otpSection" style="display:none;">
                                    <input type="text" style="width: 270px" id="otpInput" placeholder="Enter OTP"
                                        maxlength="6" />
                                    <button id="verifyOtpBtn" class="next-btn" style="width: 126px">Verify
                                        OTP</button>
                                </div>

                            </div>

                        </div>

                        <!-- Step 2: Service Selection -->
                        <div id="step2" class="step-box d-none">
                            <div class="mb-4">
                                <h4 id="stepTitle">Select a Service Category</h4>
                            </div>
                            <div id="stepOptions"></div>
                            <div style="width: 100%; display:flex; align-items:center; justify-content:space-between">
                                <button id="btnBack" class="d-none prev-btn">Back</button>
                                <button id="btnNext" class="next-btn">Next</button>
                            </div>
                        </div>

                        <!-- Step 3: Contact Info -->
                        <div id="step3" class="step-box d-none">

                            @include($viewPath . '::components._form_partials._step_contact')
                            <div class="text-center">
                                <button id="btnNextContact" class="next-btn">Next</button>
                            </div>
                        </div>

                        <!-- Step 4: Address -->
                        <div id="step4" class="step-box d-none">

                            @include($viewPath . '::components._form_partials._step_address')
                            <div class="text-center">
                                <button id="btnNextAddress" class="next-btn">Next</button>
                            </div>
                        </div>

                        <!-- Step 5: Is Emergency -->
                        <div id="step5" class="step-box d-none">
                            @include($viewPath . '::components._form_partials._step_emergency')
                            <div class="text-center">
                                <button id="btnNextEmergency" class="next-btn mt-3">Next</button>
                            </div>
                        </div>

                        <!-- Step 6: Date & Time -->
                        <div id="step6" class="step-box d-none">
                            @include($viewPath . '::components._form_partials._step_calendar')
                            <div class="text-center">
                                <button id="btnNextSchedule" class="next-btn">Next</button>
                            </div>
                        </div>

                        <!-- Step 7: Review & Submit -->
                        <div id="step7" class="step-box d-none">
                            <div>
                                @include($viewPath . '::components._form_partials._step_review-payment')
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
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


    function updateProgress(value) {
        $(".progress-bar").first().css("width", `${value}%`);
    }

    $(function() {

       
        let currentStep = 1;
        let serviceStepStack = []; // track nested category → service → children
        let currentOptions = []; // start with categories
         updateProgress((100 / 7) * (currentStep));
        getServiceCategories(function(serviceData) {
            currentOptions = serviceData;
        })
        let selectedAnswers = {
            email: '',
            category: null,
            service: null,
            child: null,
            firstName: '',
            lastName: '',
            contactEmail: '',
            phoneNo: '',
            addressLine1: '',
            city: '',
            emergency: 'no', // default no
            date: '',
            time: ''
        };
        let serviceStepType = 'category'; // category, service, child

        function updateNavigationButtons() {
            if (currentStep === 2) {
                $('#btnBack').toggleClass('d-none', serviceStepStack.length === 0);
                $('#btnNext').removeClass('d-none');
            }

        }

        function showStep(n) {
            $('.step-box').addClass('d-none');
            $(`#step${n}`).removeClass('d-none');
            currentStep = n;
            updateNavigationButtons();
            updateProgress((100 / 7) * (n));
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
                console.log({
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
                console.log('Please enter a valid 6-digit OTP.');
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
                showStep(2);
                selectedAnswers.email = email;
                loadStepOptions(window.service_categories);
            }, function(error) {

                console.log({
                    error
                });
            });
            // Call backend to verify OTP
        });

        // Load options in Step 2
        function loadStepOptions(options) {
            $('#stepOptions').empty();
            options.forEach(item => {
                const optionHtml = ` <div class="q-box__question">
                    <input class="form-check-input question__input" type="radio" name="serviceOption" id="opt${item.id}" value="${item.id}">
                    <label class="form-check-label question__label" for="opt${item.id}">${item.name}</label>
                </div>`;
                $('#stepOptions').append(optionHtml);
            });
            $('#btnBack').toggleClass('d-none', serviceStepStack.length === 0);
        }

        $('#btnNext').on('click', function() {
            const selectedId = $('input[name="serviceOption"]:checked').val();
            if (!selectedId) {
                alert('Please select an option.');
                return;
            }

            let selectedItem = currentOptions.find(o => o.id == selectedId);
            if (!selectedItem) {
                alert('Invalid selection.');
                return;
            }

            // Push current options to stack
            serviceStepStack.push({
                options: currentOptions,
                selectedId: selectedId,
                type: serviceStepType
            });

            // Initialize selected_services array if not already
            if (!Array.isArray(selectedAnswers.selected_services)) {
                selectedAnswers.selected_services = [];
            }

            // Decide next options based on selection
            if (serviceStepType === 'category') {
                selectedAnswers.category = selectedItem;
                if (selectedItem.services && selectedItem.services.length > 0) {
                    currentOptions = selectedItem.services;
                    serviceStepType = 'service';
                    loadStepOptions(currentOptions);
                    return;
                } else {
                    // No sub services, move on
                    showStep(3);
                    return;
                }
            } else if (serviceStepType === 'service') {
                selectedAnswers.service = selectedItem;
                selectedAnswers.selected_services.push(selectedItem); // <-- Add to array

                if (selectedItem.children && selectedItem.children.length > 0) {
                    currentOptions = selectedItem.children;
                    serviceStepType = 'child';
                    loadStepOptions(currentOptions);
                    return;
                } else {
                    showStep(3);
                    return;
                }
            } else if (serviceStepType === 'child') {
                selectedAnswers.child = selectedItem;
                selectedAnswers.selected_services.push(selectedItem); // <-- Add to array
                showStep(3);
                return;
            }
        });


        $('#btnBack').on('click', function() {
            if (serviceStepStack.length === 0) return;
            serviceStepStack.pop(); // remove current
            if (serviceStepStack.length === 0) {
                // back to categories
                currentOptions = window.service_categories;
                serviceStepType = 'category';
            } else {
                const prev = serviceStepStack[serviceStepStack.length - 1];
                currentOptions = prev.options;
                serviceStepType = prev.type;
            }
            loadStepOptions(currentOptions);
        });


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

        function validateContactInfo() {
            let isValid = true;

            // Clear all previous errors
            $(".error-msg").text("");

            $(document).find('#stepContactInfo input').each(function() {
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


        // Step 3: Contact info
        $('#btnNextContact').on('click', function() {
            if (!validateContactInfo()) return;
            showStep(4);
        });

        $('#btnBackContact').on('click', function() {
            showStep(2);
        });

        function validateAddressForm() {
            let isValid = true;

            $(document).find(".stepAddressInfo input").each(function() {
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

        // Step 4: Address
        $('#btnNextAddress').on('click', function() {

            if (!validateAddressForm()) return;
            showStep(5); // go to emergency step
        });

        $('#btnBackAddress').on('click', function() {
            showStep(3);
        });

        // Step 5: Emergency
        $('#btnNextEmergency').on('click', function() {
            const emergencyValue = $('input[name="emergencyOption"]:checked').val();
            selectedAnswers.emergency = emergencyValue;
            showStep(6); // go to schedule step
        });

        $('#btnBackEmergency').on('click', function() {
            showStep(4);
        });

        function showError(id, msg = "Required") {
            $(`#${id}`).addClass("is-invalid");
            $(`.error-msg[data-error-for="${id}"]`).text(msg);
        }

        function clearError(id) {
            $(`#${id}`).removeClass("is-invalid");
            $(`.error-msg[data-error-for="${id}"]`).text("");
        }


        function showErrorMessage(msg) {
            $(".question-error-msg").text(msg);
        }

        function clearErrorMessage() {
            $(".question-error-msg").text('');
        }

        function validateDateTime() { // Date & time
            let validateCalendar = true;
            if (!$("#preferred_date").val()) {
                showError("preferred_date");
                validateCalendar = false;
            } else {
                clearError("preferred_date");
            }
            if (!$("#preferred_time").val()) {
                showError("preferred_time");
                validateCalendar = false;
            } else {
                clearError("preferred_time");
            }
            return validateCalendar;
        }






        // Step 6: Schedule
        $('#btnNextSchedule').on('click', function() {

            if (!validateDateTime()) return;

            showStep(7);
            renderReview();
        });

        $('#btnBackSchedule').on('click', function() {
            showStep(5);
        });

        // Step 7: Review & Submit
        $('#btnBackReview').on('click', function() {
            showStep(6);
        });

        $('#submitBtn').on('click', function() {
            alert('Form submitted!\n\n' + JSON.stringify(selectedAnswers, null, 2));
            // Here you would send the data to your server with AJAX
        });

        function formatAmount(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2,
            }).format(amount);
        }

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
            $("#rev-datetime").html(`<p>${formattedDate} ${formattedTime}</p>`);
            // $("#rev-time").text(formattedTime);

            let isEmergency = $('input[name="is_emergency"]:checked').val();

            if (isEmergency == 1) {
                $("#rev-emergency").text("Yes");
            } else {
                $("#rev-emergency").text("No");
            }


        }

        // Render review summary
        function renderReview() {
            appendDateTimeToReview();

            let questionTemplate = '';
            let subTotal = 0;

            let formatted_selected_services = selectedAnswers.selected_services.map(service => {
                return {
                    type: 'service',
                    id: service.id,
                    name: service.name,
                    price: service.price
                };
            });

            window.selected_services = formatted_selected_services;

            selectedAnswers.selected_services.forEach((element, key) => {
                subTotal += element.price;
                questionTemplate += `
                   <li>
                        <div class="d-flex">
                            <div style="width:14px;">
                                <p>${key+1} </p>
                            </div>
                            <div class="ps-2">
                                <p>${element.name}</p>
                            </div>
                        </div>
                    </li>
                    `;
            });

            $("#selected_category").html(
                `<p><strong>Category:</strong> ${selectedAnswers.category ? selectedAnswers.category.name : ''}</p>`
            );
            $('#review-questions').html(questionTemplate);
            let taxRate = {{ $vendor->salesTax ? $vendor->salesTax->value : 0 }};
            let taxAmount = (taxRate / 100) * subTotal;

            let totalAmount = subTotal + taxAmount;

            $("#amountSubTotal").text(formatAmount(subTotal));
            $("#amountTax").text(formatAmount(taxAmount));
            $("#amountTotal").text(formatAmount(totalAmount));

        }

        // Initially hide all steps except step 1
        showStep(1);

        $(".back-step").on('click', function(e) {
            e.preventDefault();
            currentStep = $(this).attr('data-step');
            showStep(currentStep);
        })

    });
</script>
