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
        <div class="row w-100 overflow-hidden" style="max-width: 1000px;">
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
                    <div>
                        <div class="progress" role="progressbar" aria-label="Animated striped example" aria-valuenow="0"
                            style="border-radius: 0" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%;">
                            </div>
                        </div>
                    </div>
                    <div class="p-5">
                        <div id="stepForm" novalidate>
                            <!-- Step 1: Email Verification with OTP -->
                            <div id="step1" class="step-box">
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

                            <!-- Step 2: Service Selection -->
                            <div id="step2" class="step-box d-none">
                                <h4 id="stepTitle">Select a Service Category</h4>
                                <div id="stepOptions"></div>
                                <div
                                    style="width: 100%; display:flex; align-items:center; justify-content:space-between">
                                    <button id="btnBack" class="d-none prev-btn">Back</button>
                                    <button id="btnNext" class="next-btn">Next</button>
                                </div>
                            </div>

                            <!-- Step 3: Contact Info -->
                            <div id="step3" class="step-box d-none">
                                {{-- <h4>Step 3: Contact Info</h4> --}}
                                {{-- <input type="text" id="firstName" placeholder="First Name" /><br /><br />
                                <input type="text" id="lastName" placeholder="Last Name" /><br /><br />
                                <input type="email" id="contactEmail" placeholder="Email" /><br /><br />
                                <input type="text" id="phoneNo" placeholder="Phone Number" /><br /><br /> --}}
                                @include($viewPath.'::frontend.forms._step_contact')
                                <button id="btnBackContact" class="prev-btn">Back</button>
                                <button id="btnNextContact" class="next-btn">Next</button>
                            </div>

                            <!-- Step 4: Address -->
                            <div id="step4" class="step-box d-none">
                                <h4>Step 4: Address</h4>
                                {{-- <input type="text" id="addressLine1" placeholder="Address Line 1" /><br /><br />
                                <input type="text" id="city" placeholder="City" /><br /><br /> --}}
                                @include($viewPath.'::frontend.forms._step_address')
                                <button id="btnBackAddress" class="prev-btn">Back</button>
                                <button id="btnNextAddress" class="next-btn">Next</button>
                            </div>

                            <!-- Step 5: Is Emergency -->
                            <div id="step5" class="step-box d-none">
                                {{-- <h4>Step 5: Is this an emergency?</h4>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="emergencyOption"
                                        id="emergencyYes" value="yes">
                                    <label class="form-check-label" for="emergencyYes">Yes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="emergencyOption"
                                        id="emergencyNo" value="no" checked>
                                    <label class="form-check-label" for="emergencyNo">No</label>
                                </div> --}}

                                @include($viewPath.'::frontend.forms._step_emergency')

                                <button id="btnBackEmergency" class="prev-btn mt-3">Back</button>
                                <button id="btnNextEmergency" class="next-btn mt-3">Next</button>
                            </div>

                            <!-- Step 6: Date & Time -->
                            <div id="step6" class="step-box d-none">
                                {{-- <h4>Step 6: Schedule</h4> --}}
                                {{-- <input type="date" id="appointmentDate" /><br /><br />
                                <input type="time" id="appointmentTime" /><br /><br /> --}}
                                @include($viewPath.'::frontend.forms._step_calendar')
                                <button id="btnBackSchedule" class="prev-btn">Back</button>
                                <button id="btnNextSchedule" class="next-btn">Next</button>
                            </div>

                            <!-- Step 7: Review & Submit -->
                            <div id="step7" class="step-box d-none">
                                <h4>Step 7: Review & Submit</h4>
                                <div id="reviewContainer"></div>
                                <div id="paymentCard"></div>
                                <button id="btnBackReview" class="prev-btn">Back</button>
                                <button id="submitBtn" class="next-btn">Submit</button>
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




        $(function() {
            // State variables
            let currentStep = 1;
            let serviceStepStack = []; // track nested category → service → children
            let currentOptions = window.service_categories; // start with categories
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
            }

            // Step 1: Email verification
            $('#sendOtpBtn').on('click', function() {
                const email = $('#emailInput').val().trim();
                if (!email || !email.includes('@')) {
                    alert('Please enter a valid email.');
                    return;
                }
                // setTimeout(() => {
                //     $('#otpSection').show();
                // }, 2000);
                // Call backend to send OTP
                sendAjax({
                    url: "/login-signup",
                    method: 'POST',
                    data: {
                        email
                    },
                }, function(response) {
                    console.log({
                        response
                    })
                    $('#otpSection').show();
                    // $("#authForm").empty().append(response.temp);
                    // $("#form-container").empty().append(response.temp);
                }, function(error) {
                    console.log({
                        error
                    });

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

            // Step 2 navigation buttons 
            // $('#btnNext').on('click', function() {
            //     const selectedId = $('input[name="serviceOption"]:checked').val();
            //     if (!selectedId) {
            //         alert('Please select an option.');
            //         return;
            //     }
            //     let selectedItem = currentOptions.find(o => o.id == selectedId);
            //     if (!selectedItem) {
            //         alert('Invalid selection.');
            //         return;
            //     }
            //     // Push current options to stack
            //     serviceStepStack.push({
            //         options: currentOptions,
            //         selectedId: selectedId,
            //         type: serviceStepType
            //     });

            //     // Decide next options based on selection
            //     if (serviceStepType === 'category') {
            //         selectedAnswers.category = selectedItem;
            //         if (selectedItem.services && selectedItem.services.length > 0) {
            //             currentOptions = selectedItem.services;
            //             serviceStepType = 'service';
            //             loadStepOptions(currentOptions);
            //             return;
            //         } else {
            //             // No sub services, move on
            //             showStep(3);
            //             return;
            //         }
            //     } else if (serviceStepType === 'service') {
            //         selectedAnswers.service = selectedItem;
            //         if (selectedItem.children && selectedItem.children.length > 0) {
            //             currentOptions = selectedItem.children;
            //             serviceStepType = 'child';
            //             loadStepOptions(currentOptions);
            //             return;
            //         } else {
            //             showStep(3);
            //             return;
            //         }
            //     } else if (serviceStepType === 'child') {
            //         selectedAnswers.child = selectedItem;
            //         showStep(3);
            //         return;
            //     }
            // });
            // Step 2 navigation buttons
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
                // const firstName = $('#firstName').val().trim();
                // const lastName = $('#lastName').val().trim();
                // const contactEmail = $('#contactEmail').val().trim();
                // const phoneNo = $('#phoneNo').val().trim();

                // if (!firstName || !lastName || !contactEmail || !phoneNo) {
                //     alert('Please fill in all fields.');
                //     return;
                // }

                // selectedAnswers.firstName = firstName;
                // selectedAnswers.lastName = lastName;
                // selectedAnswers.contactEmail = contactEmail;
                // selectedAnswers.phoneNo = phoneNo;
                validateContactInfo();

                showStep(4);
            });

            $('#btnBackContact').on('click', function() {
                showStep(2);
            });

            // Step 4: Address
            $('#btnNextAddress').on('click', function() {
                const addr1 = $('#addressLine1').val().trim();
                const city = $('#city').val().trim();
                if (!addr1 || !city) {
                    alert('Please enter address and city.');
                    return;
                }
                selectedAnswers.addressLine1 = addr1;
                selectedAnswers.city = city;
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

            // Step 6: Schedule
            $('#btnNextSchedule').on('click', function() {
                const date = $('#appointmentDate').val();
                const time = $('#appointmentTime').val();
                if (!date || !time) {
                    alert('Please select date and time.');
                    return;
                }
                selectedAnswers.date = date;
                selectedAnswers.time = time;
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

            // Render review summary
            function renderReview() {
                console.log({
                    selectedAnswers
                })
                let html = '';
                html += `<p><strong>Email:</strong> ${selectedAnswers.email}</p>`;
                html +=
                    `<p><strong>Category:</strong> ${selectedAnswers.category ? selectedAnswers.category.name : ''}</p>`;
                html +=
                    `<p><strong>Service:</strong> ${selectedAnswers.service ? selectedAnswers.service.name : ''}</p>`;
                html +=
                    `<p><strong>Child Service:</strong> ${selectedAnswers.child ? selectedAnswers.child.name : ''}</p>`;
                html += `<p><strong>Name:</strong> ${selectedAnswers.firstName} ${selectedAnswers.lastName}</p>`;
                html += `<p><strong>Contact Email:</strong> ${selectedAnswers.contactEmail}</p>`;
                html += `<p><strong>Phone Number:</strong> ${selectedAnswers.phoneNo}</p>`;
                html += `<p><strong>Address:</strong> ${selectedAnswers.addressLine1}, ${selectedAnswers.city}</p>`;
                html += `<p><strong>Emergency:</strong> ${selectedAnswers.emergency === 'yes' ? 'Yes' : 'No'}</p>`;
                html +=
                    `<p><strong>Appointment Date & Time:</strong> ${selectedAnswers.date} ${selectedAnswers.time}</p>`;

                $('#reviewContainer').html(html);
            }

            // Initially hide all steps except step 1
            showStep(1);
        });
    </script>
</body>

</html>
