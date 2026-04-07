<style>
    ul#review-questions li p {
        font-size: smaller
    }
</style>
<div class="stepReview" id="stepReview">
    <div class="mb-5 d-flex align-items-center">
        <button type="button" class="btn btn-primary prev circle me-3 prevStep" id=""
            style="height: 40px; width:40px">
            <i class="fa-solid fa-arrow-left"></i>
        </button>

        <h4 style="font-weight: 800">Review Details</h4>
    </div>



    <div class="row">
        <!-- Contact Info -->
        <div class="col-md-12 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h6 class="fw-bold mb-0">Contact Information</h6>
                <button class="btn btn-sm btn-outline-secondary back-step" data-step="4">
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
                <button class="btn btn-sm btn-outline-secondary back-step" data-step="5">
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
                <button class="btn btn-sm btn-outline-secondary back-step" data-step="2">
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
                <button class="btn btn-sm btn-outline-secondary back-step" data-step="3">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
            </div>
            <div class="bg-light p-3 rounded" id="rev-datetime">
                <p>09/08/2025 10:45 AM</p>
            </div>
        </div>

        <!-- Address Info -->
        {{-- <div class="col-md-12 mb-4">
            <h6 class="fw-bold mb-3">Additional Note</h6>
            <div class="bg-light p-3 rounded" id="rev-description">
                <p class="">n/a</p>
            </div>
        </div> --}}

        <!-- Questions & Answers -->
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h6 class="fw-bold mb-0">Selected Services</h6>
                <button class="btn btn-sm btn-outline-secondary back-step" data-step="2">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
            </div>
            <div class="bg-light p-3 rounded">
                <div id="review-questions">
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

                </div>
            </div>
        </div>
        <!-- Invoice Total -->
        <div class="col-12 mb-4">
            <p class="mb-2" style="font-weight: 500">Payment</p>
            <div class="bg-light p-3 rounded">
                <div class="" id="invoiceTotal">

                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Service</span>
                        <span class="fw-bold" id="">$135.60</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Emergency</span>
                        <span class="fw-bold" id="">$135.60</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Tax</span>
                        <span class="fw-bold" id="">$135.60</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Invoice</span>
                        <span class="fw-bold" id="">$135.60</span>
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
                        <button id="submit-btn" class="next-btn btn btn-success" type="submit">Pay</button>
                    </div>
                </form>

            </div>
        </div>

    </div>
