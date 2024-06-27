@extends('layouts.app')

@section('content')
    <h2>封禁列表</h2>
    <p>只有受信任的应用程序才能向您施加封禁。</p>


    <div class="list-group" class="mt-3">
        @foreach($bans as $b)
            <div class="list-group-item list-group-item-action">
                {{ $b->code }}


            </div>

        @endforeach

    </div>

@endsection
