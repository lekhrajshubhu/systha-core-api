 <div class="stepEmergency" id="stepEmergency">
     <div class="mb-5 d-flex align-items-center">
         <button type="button" class="btn btn-primary circle me-3 prevStep" style="height: 40px; width:40px">
             <i class="fa-solid fa-arrow-left"></i>
         </button>

         <div>
             <h4 style="font-weight: 800">Is Emergency ?</h4>
             <p>An additional charge of $25 will apply for emergency service.</p>
         </div>
     </div>

     <div>
         <div class="form-check ps-0">
             <div class="q-box__question">
                 <input class="form-check-input question__input" type="radio" name="is_emergency" id="yes"
                     value="1">
                 <label class="form-check-label question__label" for="yes">Yes</label>
             </div>
             <div class="q-box__question">
                 <input class="form-check-input question__input" type="radio" name="is_emergency" id="no"
                     checked value="0">
                 <label class="form-check-label question__label" for="no">No</label>
             </div>
         </div>
     </div>

     {{-- <div class="d-flex align-items-center justify-content-around mt-4">
         <button id="prev-btn-contact" class="prev-btn" type="button">Previous</button>
         <button id="next-btn-contact" class="next-btn" type="button">Next</button>
         <button id="submit-btn" type="submit">Submit</button>
     </div> --}}


 </div>
