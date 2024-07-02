<div class="d-flex justify-content-center align-items-center" style="height:98vh;width: 100%">
    <div>
        <section style="width: 80vh">
            <div class="text-center text-lg-start">
                <div class="container-fluid">
                    <div class="row gx-lg-5 align-items-center">
                        <div class="col-lg-6 mb-5 mb-lg-0">
                            <h1 class="my-5 display-3 fw-bold ls-tight">
                                应用授权 <br/>
                                <span class="text-primary">{{ $client->name }}</span>
                            </h1>
                            <div style="color: hsl(217, 10%, 50.8%);">
                                @if (!empty($client->description))
                                    {{ $client->description }}
                                    <br/>
                                @endif
                            </div>
                        </div>

                        <div class="col-lg-6 mb-5 mb-lg-0">
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
        </section>
        {{--            <div class="container">--}}
        {{--                <div class="row">--}}
        {{--                    <div>--}}

        {{--                        <h1>授权请求</h1>--}}
        {{--                        <p><strong>{{ $client->name }}</strong> 正在申请访问您的账户。</p>--}}

        {{--                        <!-- Scope List -->--}}
        {{--                        @if (count($scopes) > 0)--}}
        {{--                        <div class="scopes">--}}
        {{--                            <p><strong>此应用程序将被允许: </strong></p>--}}

        {{--                            <ul>--}}
        {{--                                @foreach ($scopes as $scope)--}}
        {{--                                <li>{{ $scope->description }}</li>--}}
        {{--                                @endforeach--}}
        {{--                            </ul>--}}
        {{--                        </div>--}}
        {{--                        @endif--}}

        {{--                        <div class="buttons">--}}
        {{--                            <button class="btn btn-success btn-approve" onclick="accept()">授权</button>--}}

        {{--                            <button class="btn btn-danger" onclick="deny()">取消</button>--}}

        {{--                        </div>--}}
        {{--                    </div>--}}
        {{--                </div>--}}
        {{--            </div>--}}
    </div>


</div>
