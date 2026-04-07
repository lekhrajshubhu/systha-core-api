 <div class="stepContactInfo" id="stepContactInfo">
      <div class="mb-5 d-flex align-items-center">
         <button type="button" class="btn btn-primary circle me-3 prevStep"
             style="height: 40px; width:40px">
             <i class="fa-solid fa-arrow-left"></i>
         </button>

         <h4 style="font-weight: 800">Contact Information</h4>
     </div>
     <div class="row">
         <div class="col-12 col-md-6">
             <div class="mb-4">
                 <label class="form-label" for="fname">First Name:</label>
                 <input class="form-control" id="fname" name="fname" type="text">
                 <small class="text-danger error-msg" data-error-for="fname"></small>
             </div>
         </div>
         <div class="col-12 col-md-6">
             <div class="mb-4">
                 <label class="form-label" for="lname">Last Name:</label>
                 <input class="form-control" id="lname" name="lname" type="text">
                 <small class="text-danger error-msg" data-error-for="lname"></small>
             </div>
         </div>
         <div class="col-12">
             <div class="mb-4">
                 <label class="form-label" for="email">Email:</label>
                 <input class="form-control" id="email" name="email" type="email">
                 <small class="text-danger error-msg" data-error-for="email"></small>
             </div>
         </div>
         <div class="col-12">
             <div class="mb-4">
                 <label class="form-label" for="phone_no">Phone Number:</label>
                 <input class="form-control phone_no" id="phone_no" name="phone_no" type="text">
                 <small class="text-danger error-msg" data-error-for="phone_no"></small>
             </div>
         </div>
     </div>

     {{-- <div class="d-flex align-items-center justify-content-around mt-4">
         <button id="prev-btn-contact" class="prev-btn" type="button">Previous</button>
         <button id="next-btn-contact" class="next-btn" type="button">Next</button>
         <button id="submit-btn" type="submit">Submit</button>
     </div> --}}


 </div>
