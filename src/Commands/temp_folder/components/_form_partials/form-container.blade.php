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
             <div class="step-panel active">
                 @include($viewPath.'::frontend.forms._step_questions')
             </div>
             <div class="step-panel">
                 @include($viewPath.'::frontend.forms._step_emergency')
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
                 @include($viewPath.'::frontend.forms._step_review-payment')

             </div>

         </div>


         {{-- </div> --}}
         <div class="d-flex align-items-center justify-content-around mt-4">
             <button class="next-btn d-none" id="nextStep" type="button">Next</button>
         </div>
         </form>
     </div>
 </div>
