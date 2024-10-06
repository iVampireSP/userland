@extends('layouts.admin')

@section('title', "套餐包")

@section('content')
    <h2>套餐包</h2>
    <p></p>

    <div class="mb-3">
        {{--        <a href="{{route('admin.packages.create')}}">新建套餐包</a>--}}
    </div>

    <div>
        @foreach($packages as $package)
            <div class="card" style="width: 18rem;">
                <div class="card-body">
                    <h5 class="card-title">{{$package->title}}</h5>
                    <h6 class="card-subtitle mb-2 text-muted">Card subtitle</h6>
                    <p class="card-text">{{ $package->description }}</p>
                    <a href="#" class="card-link">Card link</a>
                </div>
            </div>
        @endforeach

    </div>


    {{ $packages->links() }}

@endsection
