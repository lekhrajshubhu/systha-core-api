<form id="formOTP">
    <div class="mb-4">
        <label for="email">Your email</label>
        <input type="text" class="form-control" name="email" id="authEmail" value="{{ $email }}">
        <div id="emailError" style="font-size: 14px" class="text-danger mt-1"></div>
    </div>
    <div>
        <div class="">
            <label for="">Email verification code (OTP)</label>
            <input type="text" name="otp" type="number" maxlength="10" class="form-control">
            <div id="otpError" style="font-size: 14px" class="text-danger mt-1"></div>
        </div>
    </div>
    <div class="mt-4 text-center">
        <button class="btn btn-primary" id="btnVerifyOTP">Submit</button>
    </div>
</form>
