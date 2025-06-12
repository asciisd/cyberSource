@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Payment Form</div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('payment.process') }}" id="payment-form">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="amount" class="form-label">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control" id="amount" name="amount" value="{{ old('amount') }}" required>
                                </div>
                            </div>
                        </div>

                        <h5 class="mt-4">Card Information</h5>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="card_number" class="form-label">Card Number</label>
                                <input type="text" class="form-control" id="card_number" name="card_number" value="{{ old('card_number') ?? '4111111111111111' }}" required>
                                <small class="text-muted">Use 4111111111111111 for testing Visa cards</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="expiration_month" class="form-label">Expiration Month</label>
                                <input type="text" class="form-control" id="expiration_month" name="expiration_month" placeholder="MM" value="{{ old('expiration_month') ?? '12' }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="expiration_year" class="form-label">Expiration Year</label>
                                <input type="text" class="form-control" id="expiration_year" name="expiration_year" placeholder="YYYY" value="{{ old('expiration_year') ?? '2025' }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="cvv" class="form-label">CVV</label>
                                <input type="text" class="form-control" id="cvv" name="cvv" value="{{ old('cvv') ?? '123' }}" required>
                            </div>
                        </div>

                        <h5 class="mt-4">Billing Information</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name') ?? 'John' }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name') ?? 'Doe' }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address" value="{{ old('address') ?? '1 Market St' }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-5">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="{{ old('city') ?? 'San Francisco' }}" required>
                            </div>
                            <div class="col-md-3">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state" value="{{ old('state') ?? 'CA' }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" value="{{ old('postal_code') ?? '94105' }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country" value="{{ old('country') ?? 'US' }}" required>
                                <small class="text-muted">2-letter country code</small>
                            </div>
                            <div class="col-md-4">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') ?? 'customer@example.com' }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') ?? '4158880000' }}" required>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Process Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
