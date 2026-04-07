 {{-- <div class="stepQuestion" id="stepQuestion">
     <div class="mb-5 d-flex">
         <button type="button" id="btnPrev" class="btn btn-primary prev circle me-3 d-none preBtn" style="height: 40px; width:40px">
             <i class="fa-solid fa-arrow-left"></i>
         </button>

         <h4 style="font-weight: 800" id="question"></h4>
     </div>

     <div class="form-check ps-0 q-box" id="answerOptions">
  
     </div>
     <div> <small class="text-danger question-error-msg"></small></div>
     <div class="d-flex align-items-center justify-content-around mt-4" id="q-box__buttons">
         <button class="next-btn btn btn-primary" id="inner-next-btn" type="button">Next</button>
     </div>
 </div> --}}

 <div id="stepContainer">
    <div class="mb-4 d-flex align-items-center" style="margin-bottom: 20px">
        <button type="button" class="btn btn-info me-3 d-none" id="btnBack" style="height: 40px; width:40px">
            <i class="fa-solid fa-arrow-left"></i>
        </button>
        <h4 id="stepTitle" class="fw-bold pl-2">Select a Service Category</h4>
    </div>

    <div id="stepOptions" class="form-check ps-0"></div>
    <div><small class="text-danger question-error-msg"></small></div>

    <div class="mt-3 d-flex justify-content-around">
        <button type="button" class="btn btn-primary next-btn" id="btnNext">Next</button>
    </div>
</div>
