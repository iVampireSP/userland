<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.display_name') }} - 授权</title>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body style="height: 100vh">
<div class="d-flex justify-content-center align-items-center" style="height:100%">
    <div>
        @if (!$client->trusted)
            <div class="container">
                <div class="row">
                    <div>

                        <h1>授权请求</h1>
                        <p><strong>{{ $client->name }}</strong> 正在申请访问您的账户。</p>

                        <!-- Scope List -->
                        @if (count($scopes) > 0)
                            <div class="scopes">
                                <p><strong>此应用程序将被允许: </strong></p>

                                <ul>
                                    @foreach ($scopes as $scope)
                                        <li>{{ $scope->description }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="buttons">
                            <button class="btn btn-success btn-approve" onclick="accept()">授权</button>

                            <button class="btn btn-danger" onclick="deny()">取消</button>

                        </div>
                    </div>
                </div>
            </div>
    </div>
    @else

        <h1>正在继续...</h1>

        <div style="width: 100%;" class="text-center mt-5">
            <!-- spin -->
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">正在继续</span>
            </div>
        </div>

        <script>
            setTimeout(() => {
                accept()
            }, 100)
        </script>

    @endif

</div>


<!-- Authorize Button -->
<form class="d-none" method="post" action="{{ route('passport.authorizations.approve') }}" id="authorize-form">
    @csrf

    <input type="hidden" name="state" value="{{ $request->state }}">
    <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
    <input type="hidden" name="auth_token" value="{{ $authToken }}">


</form>

<!-- Cancel Button -->
<form class="d-none" method="post" action="{{ route('passport.authorizations.deny') }}" id="cancel-form">
    @csrf
    @method('DELETE')

    <input type="hidden" name="state" value="{{ $request->state }}">
    <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
    <input type="hidden" name="auth_token" value="{{ $authToken }}">
</form>

<script>
    function accept() {
        document.getElementById('authorize-form').submit();
    }

    function deny() {
        document.getElementById('cancel-form').submit();
    }
</script>

</body>

</html>
