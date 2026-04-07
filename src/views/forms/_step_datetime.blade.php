<div class="stepDescription d-none" id="stepDescription">
    <div class="mb-5 d-flex align-items-center">
        <button type="button" class="btn btn-primary prev circle me-3" id="prev-btn-description"
            style="height: 40px; width:40px">
            <i class="fa-solid fa-arrow-left"></i>
        </button>

        <h4 style="font-weight: 800">Preferred Date & Time</h4>
    </div>
    <div class="row">

        {{-- <div class="col-12 col-md-6">
            <div class="mb-4">
                <label class="form-label" for="preferred_date">Preferred Date:</label>
                <input class="form-control" id="preferred_date" name="preferred_date" type="date">
                <small class="text-danger error-msg" data-error-for="preferred_date"></small>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="mb-4">
                <label class="form-label" for="preferred_time">Preferred Time:</label>
                <input class="form-control" id="preferred_time" name="preferred_time" type="time">
                <small class="text-danger error-msg" data-error-for="preferred_time"></small>
            </div>
        </div> --}}
        @include($viewPath.'::frontend.forms._step_calendar')

        <div class="col-12">
            <div class="mb-4">
                <label class="form-label" for="description">Describe your cleaning needs:</label>
                <textarea class="form-control" id="description" name="description" rows="4"
                    placeholder="E.g., 2-bedroom apartment, kitchen deep clean, pet hair removal..."></textarea>
                <small class="text-danger error-msg" data-error-for="description"></small>
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-around mt-4">
        {{-- <button id="prev-btn-description" class="prev-btn" type="button">Previous</button> --}}
        <button id="next-btn-description" class="next-btn" type="button">Next</button>
    </div>
</div>
