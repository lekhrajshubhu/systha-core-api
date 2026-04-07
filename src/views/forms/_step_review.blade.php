 <div class="stepReview" id="stepInquiryReview">
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
         <div class="col-md-12 mb-4">
             
             <div class="d-flex justify-content-between align-items-center mb-1">
                 <h6 class="fw-bold mb-3">Preferred Date & Time</h6>
                 <button class="btn btn-sm btn-outline-secondary back-step" data-step="2">
                     <i class="fa-solid fa-pen-to-square"></i>
                 </button>
             </div>
             <div class="bg-light p-3 rounded" id="contact-info">
                 <p><span id="rev-date"></span></p>
                 <p><span id="rev-time"></span></p>
             </div>
         </div>

         <!-- Address Info -->
         <div class="col-md-12 mb-4">
            
             <div class="d-flex justify-content-between align-items-center mb-1">
                  <h6 class="fw-bold mb-3">Service Address</h6>
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

         <!-- Questions & Answers -->
         <div class="col-12 mb-4">
             
             <div class="d-flex justify-content-between align-items-center mb-1">
                 <h6 class="fw-bold mb-3">Selected Services</h6>
                 <button class="btn btn-sm btn-outline-secondary back-step" data-step="1">
                     <i class="fa-solid fa-pen-to-square"></i>
                 </button>
             </div>
             <div class="bg-light p-3 rounded">
                 <ul id="review-questions">
                     <!-- Populated dynamically -->
                     <li>
                         <div class="p-3 d-flex">
                             <div>
                                 <p>1</p>
                             </div>
                             <div>
                                 <p style="font-weight: 300">N/A</p>
                                 {{-- <p class="ps-3">Lorem ipsum dolor sit.</p> --}}
                             </div>
                         </div>
                     </li>

                 </ul>
             </div>
         </div>


         <div class="d-flex align-items-center justify-content-around">

             <button id="submit-btn-review" type="button" class="btn btn-success next-btn">Submit</button>
         </div>
     </div>
