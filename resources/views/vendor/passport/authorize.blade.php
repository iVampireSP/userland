<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.display_name') }} - 授权</title>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        .passport-authorize .container {
            margin-top: 30px;
        }

        .passport-authorize .scopes {
            margin-top: 20px;
        }

        .passport-authorize .buttons {
            margin-top: 25px;
            text-align: center;
        }

        .passport-authorize .btn {
            width: 125px;
        }

        .passport-authorize .btn-approve {
            margin-right: 15px;
        }

        .passport-authorize form {
            display: inline;
        }
    </style>
</head>
<body class="passport-authorize">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card card-default">
                    <div class="card-header">
                        授权请求
                    </div>
                    <div class="card-body">
                        <!-- Introduction -->
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
                            <!-- Authorize Button -->
                            <form method="post" action="{{ route('passport.authorizations.approve') }}">
                                @csrf

                                <input type="hidden" name="state" value="{{ $request->state }}">
                                <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                                <button type="submit" class="btn btn-success btn-approve">授权</button>
                            </form>

                            <!-- Cancel Button -->
                            <form method="post" action="{{ route('passport.authorizations.deny') }}">
                                @csrf
                                @method('DELETE')

                                <input type="hidden" name="state" value="{{ $request->state }}">
                                <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                                <button class="btn btn-danger">取消</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
