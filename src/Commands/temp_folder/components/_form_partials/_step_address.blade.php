<div class="stepAddressInfo" id="stepAddressInfo">
    <div class="mb-5 d-flex align-items-center">
        <button type="button" class="btn btn-info circle me-3" 
        id="btnBackAddress"
        style="height: 40px; width:40px">
            <i class="fa-solid fa-arrow-left"></i>
        </button>

         <h4 style="font-weight: 800" class="pl-2">Service Address</h4>
    </div>
    <div class="row">
        <div class="col-12 col-md-12">
            <div class="mb-4">
                <label class="form-label">Address</label>
                <input class="form-control" id="add1" name="add1" type="text">
                <small class="text-danger error-msg"></small>
            </div>
        </div>

        <div class="col-12 col-md-12">
            <div class="mb-4">
                <label class="form-label">Address 2:</label>
                <input class="form-control" id="add2" name="add2" type="text">
                <small class="text-danger error-msg"></small>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="mb-4">
                <label class="form-label">City:</label>
                <input class="form-control" id="city" name="city" type="text">
                <small class="text-danger error-msg"></small>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="mb-4">
                <label class="form-label">State:</label>
                <input class="form-control" id="state" name="state" type="text">
                <small class="text-danger error-msg"></small>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="mb-4">
                <label class="form-label">ZIP:</label>
                <input class="form-control" id="zip" name="zip" type="text">
                <small class="text-danger error-msg"></small>
            </div>
        </div>
    </div>

    {{-- <div class="d-flex align-items-center justify-content-around mt-4" id="q-box__buttons"> --}}
        {{-- <button id="prev-btn-addr" class="prev-btn" type="button">Previous</button> --}}
        {{-- <button id="next-btn-addr" class="next-btn" type="button">Next</button> --}}
    {{-- </div> --}}
</div>
