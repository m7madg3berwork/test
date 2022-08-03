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
                    Congratulations, I have received a reward from the romooz website
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <strong>An amount of {{ $data['amount'] ?? '0'.' SAR'  }} has been added to your wallet on the Rumooz website</strong>
                        <p>Your current balance is {{ $data['balance'] ?? ''.' SAR' }}</p>
                    </div>
                    <div class="col-12 mb-3">
                        <strong>{{ $data['comment'] ?? '' }}</strong>
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
