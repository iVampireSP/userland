@extends('layouts.admin')

@section('title', '首页')

@section('content')
    <h2>当前主机名</h2>
    <p>{{ gethostname() }}</p>
@endsection
