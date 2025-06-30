<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">Payment Successful</div>

                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fa fa-check-circle text-success" style="font-size: 64px;"></i>
                        <h4 class="mt-3">Your payment has been processed successfully!</h4>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                            <tr>
                                <th>Transaction ID:</th>
                                <td>{{ $transaction_id }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>{{ $status }}</td>
                            </tr>
                            <tr>
                                <th>Amount:</th>
                                <td>{{ $amount }} {{ $currency }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <a href="{{ route('payment.form') }}" class="btn btn-primary">Make Another Payment</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
