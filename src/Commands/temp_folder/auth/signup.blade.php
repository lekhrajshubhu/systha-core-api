<form id="registerForm">
     <div class="mb-4">
        <label for="email">Enter your email</label>
        <input type="text" class="form-control" id="authEmail" value="{{$email}}">
        <div id="emailError" style="font-size: 14px" class="text-danger mt-1"></div>
    </div>
    <div>
        <div class="">
            <label for="">Create Password</label>
            <input type="password" name="password" class="form-control">
        </div>
    </div>
    <div class="mt-4 text-center">
        <button class="btn btn-primary" id="btnSignupContinue">Continue</button>
    </div>
</form>
