<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.display_name') }} - 授权</title>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>


<body>

@if ($client->trusted)
    @include('passport::trusted')
@else
    @include('passport::standard')
@endif

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
