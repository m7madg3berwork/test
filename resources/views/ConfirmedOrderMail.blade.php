<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <title>Document</title>
</head>
<body>
<div class="container">
    <div class="card text-center">
        <div class="card-header">
            <h4 class="card-title">
                Your order has been confirmed by romooz
            </h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 mb-3">
                    <strong>Your order has been confirmed </strong>
                    <br>
                    <strong> It will reach you at the following address :
                        <p>Shipping data is :</p>
                        @php
                            $shipping = json_decode($data['shipping_address']) ?? '';
                        @endphp
                        @if(is_object($shipping))
                            <ul>
                                <li>Customer Name : {{ $shipping->name ?? '' }}</li>
                                <li>Customer email : {{ $shipping->email ?? '' }}</li>
                                <li>Country : {{ $shipping->country ?? '' }}</li>
                                <li>state : {{ $shipping->state ?? '' }}</li>
                                <li>phone : {{ $shipping->phone ?? '' }}</li>
                                <li>Shipping address : {{ $shipping->address ?? '' }}</li>
                            </ul>
                        @endif
                    </strong>
                </div>
                <div class="col-12 mb-3">
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a target="_blank" href="{{ env("APP_URL") }}" class="btn btn-primary btn-sm">GO Now</a>
        </div>
    </div>
</div>
</body>
</html>
