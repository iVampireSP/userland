@extends('layouts.app')

@section('content')
    <x-switch-account :type="\App\View\Components\SwitchAccount::TYPE_CONTAINER" />
@endsection
