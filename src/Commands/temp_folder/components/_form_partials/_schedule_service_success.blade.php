<div class="bg-white p-5 text-center">
  <div class="mb-4" style="margin: 0 auto; width:max-content">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#3ec182" class="bi bi-check-circle-fill"
            viewBox="0 0 16 16">
            <path
                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM6.97 10.03a.75.75 0 0 0 1.08.02l3.992-3.992a.75.75 0 1 0-1.06-1.06L7.5 8.44 5.53 6.47a.75.75 0 0 0-1.06 1.06l2.5 2.5z" />
        </svg>
    </div>

    <h4 class="fw-bold text-success mb-3">Appointment Successfully Booked!</h4>

    <p class="text-muted" style="line-height: 2">
        Appointment No: <strong>#{{ $appointment->appointment_no }}</strong><br>
        Scheduled Date: <strong>{{ viewBladeDate($appointment->start_date) }}</strong><br>
        Scheduled Time: <strong>{{ formatTime($appointment->start_time) }}</strong>
    </p>

    {{-- <div class="table-responsive mt-4">
        <table class="table table-bordered text-start mx-auto">
            <thead class="table-light">
                <tr>
                    <th>S.N.</th>
                    <th>Service</th>
                    <th class="text-end">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($appointment->services as $index => $service)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $service['service_name'] }}</td>
                        <td class="text-end">Rs. {{ number_format($service['price'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2" class="text-end">Subtotal</th>
                    <th class="text-end">Rs. {{ number_format($appointment->total_amount, 2) }}</th>
                </tr>
                <tr>
                    <th colspan="2" class="text-end">Tax</th>
                    <th class="text-end">Rs. {{ number_format($appointment->total_tax, 2) }}</th>
                </tr>
                <tr>
                    <th colspan="2" class="text-end">Total</th>
                    <th class="text-end">Rs. {{ number_format($appointment->total_amount, 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div> --}}

    {{-- <p class="fw-medium text-dark mb-4 mt-4">
        To complete your booking, please proceed to payment.
    </p> --}}

    {{-- <a href="/payment" class="btn btn-success px-4">
        Proceed to Payment
    </a> --}}

    <div class="mt-3">
         <a href="/" class="btn btn-outline-success mt-2">Return to Homepage</a>
    </div>
</div>
