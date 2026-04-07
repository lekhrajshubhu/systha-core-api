<div class="bg-white p-5 text-center">
  <div class="mb-4" style="margin: 0 auto; width:max-content">
    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#3ec182" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
      <path
        d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM6.97 10.03a.75.75 0 0 0 1.08.02l3.992-3.992a.75.75 0 1 0-1.06-1.06L7.5 8.44 5.53 6.47a.75.75 0 0 0-1.06 1.06l2.5 2.5z" />
    </svg>
  </div>

  <h4 class="fw-bold text-success">Thank You!</h4>

  <p class="text-muted mb-3">
    Please check your email for further details.
  </p>
  <p class="text-muted mb-3">
    Your subscription ID is <strong>#{{ $packageSubscription->subs_no }}</strong>.<br>
    Plan: <strong>{{ $packageSubscription->package->package_name }}</strong><br>
    <strong>{{ $packageSubscription->packageType->description }} / {{(int)$packageSubscription->packageType->duration > 1 ? $packageSubscription->packageType->duration : ''}} {{ $packageSubscription->packageType->type_name }}</strong><br>
    {{-- Start Date: <strong>{{ \Carbon\Carbon::parse($packageSubscription->start_date)->format('F j, Y') }}</strong><br> --}}
    {{-- Billing Interval: <strong>{{ ucfirst($packageSubscription->billing_interval) }}</strong> --}}
  </p>


  <a href="/" class="btn btn-outline-success mt-2">Return to Homepage</a>
</div>
