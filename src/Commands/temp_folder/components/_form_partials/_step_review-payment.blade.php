<style>
    ul#review-questions li p {
        font-size: smaller
    }
</style>
<div class="stepReview" id="stepReview">
    <div class="mb-5 d-flex align-items-center">
        <button type="button" class="btn btn-primary prev circle me-3" id="btnBackReview" style="height: 40px; width:40px">
            <i class="fa-solid fa-arrow-left"></i>
        </button>

        <h4 style="font-weight: 800">Review Details</h4>
    </div>



    <div class="row">
        <!-- Contact Info -->
        <div class="col-md-12 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h6 class="fw-bold mb-0">Contact Information</h6>
                <button class="btn btn-sm btn-outline-secondary back-step" data-step="3">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
            </div>
            <div class="bg-light p-3 rounded" id="contact-info">
                <p><span id="rev-fname"></span> <span id="rev-lname"></span></p>
                <p><span id="rev-email"></span></p>
                <p><span id="rev-phone_no"></span></p>
            </div>
        </div>

        <!-- Address Info -->
        <div class="col-md-12 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h6 class="fw-bold mb-0">Service Address</h6>
                <button class="btn btn-sm btn-outline-secondary back-step" data-step="4">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
            </div>
            <div class="bg-light p-3 rounded" id="address-info">
                <p><span id="rev-add1"></span></p>
                <p><span id="rev-add2"></span></p>
                <p><span id="rev-city"></span>, <span id="rev-state"></span> <span id="rev-zip"></span></p>
            </div>
        </div>

        <!-- Emergency Service -->
        <div class="col-md-12 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h6 class="fw-bold mb-0">Is Emergency ?</h6>
                <button class="btn btn-sm btn-outline-secondary back-step" data-step="5">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
            </div>
            <div class="bg-light p-3 rounded" id="rev-emergency">Yes</p>
            </div>
        </div>
        <!-- Preferred Date & Time -->
        <div class="col-md-12 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h6 class="fw-bold mb-0">Preferred Date & Time</h6>
                <button class="btn btn-sm btn-outline-secondary back-step" data-step="6">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
            </div>
            <div class="bg-light p-3 rounded" id="rev-datetime">
                <p>09/08/2025 10:45 AM</p>
            </div>
        </div>


        <!-- Questions & Answers -->
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h6 class="fw-bold mb-0">Selected Services</h6>
                <button class="btn btn-sm btn-outline-secondary back-step" data-step="2">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
            </div>
            <div class="bg-light p-3 rounded">
                <div>
                    <span id="selected_category"></span>
                </div>
                <ul id="review-questions">
                    <!-- Populated dynamically -->
                    <li>
                        <div class="d-flex">
                            <div style="width:14px;">
                                <p>1. </p>
                            </div>
                            <div>
                                <p> Lorem ipsum dolor sit.</p>

                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="d-flex">
                            <div style="width:14px;">
                                <p>2. </p>
                            </div>
                            <div>
                                <p> Lorem ipsum dolor sit.</p>

                            </div>
                        </div>
                    </li>

                </ul>
            </div>
        </div>
        <!-- Invoice Total -->
        <div class="col-12 mb-4">
            <p class="mb-2" style="font-weight: 500">Payment</p>
            <div class="bg-light p-3 rounded">
                <div class="" id="invoiceTotal">

                    <div class="d-flex justify-content-between">
                        <span class="">Sub Total</span>
                        <span class="" id="amountSubTotal">$135.60</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="">Emergency</span>
                        <span class="" id="amountEmergency">$0.00</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="">Tax</span>
                        <span class="" id="amountTax">$135.60</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Invoice</span>
                        <span class="fw-bold" id="amountTotal">$135.60</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 mb-4">
            <p class="mb-2" style="font-weight: 500">Payment Method</p>
            <div class="">
                <form id="payment-form">
                    <div class="bg-light p-3 rounded">
                        <div id="card-element"><!-- Stripe Card Element will go here --></div>
                        <div id="card-errors" role="alert" style="color:red; margin-top:10px;"></div>
                    </div>
                    <div class="d-flex align-items-center justify-content-around">
                        {{-- <button id="submit-btn" class="next-btn btn btn-success" type="submit">Pay</button> --}}
                        <button id="submit-btn" class="next-btn btn btn-success" type="submit">Proceed</button>
                    </div>
                    <p class="text-danger" style="text-align: center;padding: 20px;font-size: 14px;" id="payment-error"></p>
                </form>

            </div>
        </div>

    </div>
</div>
<script>
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
        $('#stepContactInfo input').each(function() {
            const $field = $(this);
            const name = $field.attr('name');
            if (!name) return;

            contactData[name] = $field.val();
        });

        $('#stepAddressInfo input').each(function() {
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
            $("#submit-btn").html("Pay").prop("disabled", false);
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

            handleScheduleService({
                payment_method_id: paymentMethod.id,
                stripe_customer: response.data.stripe_customer,
                contactData,
                addressData,
            })
        }, (error) => {
            console.log({
                error
            })
        })
    });

    function handleScheduleService(params) {
        let addr = params.addressData,
            contact = params.contactData;

        sendAjax({
            url: `/schedule-service`,
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
                is_emergency: $('input[name="is_emergency"]:checked').val(),
                service_selected: window.selected_services,
            }
        }, (response) => {
            $("#submit-btn").html("Submit").prop("disabled", false);
            if (response.requires_action) {
                confirmPayment(response.client_secret, params.payment_method_id, response.appointment);
            } else {
                console.log("subscription successful");
            }
        }, (error) => {
            console.log({
                error
            })
        })
    }

    async function confirmPayment(clientSecret, paymentMethodId, appointment) {


        if (!clientSecret || !paymentMethodId) return;


        try {
            const result = await stripe.confirmCardPayment(clientSecret, {
                payment_method: paymentMethodId
            });

            if (result.error) {
                $("#payment-error").text(result.error.message);
                $("#submit-btn").html("Pay").prop("disabled", false);
            } else if (result.paymentIntent.status === 'succeeded') {
                // ✅ Payment succeeded
                savePayment(result.paymentIntent, appointment);
                console.log("Payment successful!");
            }
        } catch (err) {
            console.error("Unexpected error:", err);
            $("#payment-error").text("Something went wrong: " + err.message);
            $("#submit-btn").html("Pay").prop("disabled", false);
        }
    }

    function savePayment(paymentIntent, appointment) {
        try {
            sendAjax({
                url: `/appointments/${appointment.id}/store-card-payment`,
                method: 'POST',
                data: {
                    payment_intent_id: paymentIntent.id,
                    appointment_id: appointment.id,
                    payment_method_id: paymentIntent.payment_method,
                    amount: paymentIntent.amount / 100,
                }
            }, (response) => {
                $("#form-container").empty().append(response.temp);

            }, (error) => {
                this.loading = false;
                $("#payment-error").text("Error processing payment: " + error.responseJSON.message || error.statusText);
                $("#submit-btn").html("Pay").prop("disabled", false);
                console.error("Error processing payment:", error.responseJSON.message || error.statusText);
            });
        } catch (error) {
            this.loading = false
            console.log({
                error
            })
        }
    }

</script>
