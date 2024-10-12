@extends('layouts.app')
@section('title', '创建订单')
@section('content')
    <div class="container">
        <h1>创建订单</h1>
        <p>您正在购买 {{ $package->title }}</p>

        <form action="{{ route('orders.store') }}" method="POST">
            @csrf

            <input type="hidden" name="type" value="package" />
            <input type="hidden" name="package_id" value="{{$package->id}}" />
            <input type="hidden" id="billing_cycle" name="billing_cycle" value="">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>选择计费周期:</label>
                        <div class="btn-group-vertical w-100" role="group" aria-label="Billing Cycle">
                            @php
                                $lowestPrice = min($package->price_day, $package->price_week, $package->price_month, $package->price_year, $package->price_forever);
                            @endphp
                            @if ($package->enable_day)
                                <button type="button" class="btn btn-outline-primary" data-cycle="day" data-price="{{ $package->price_day }}" onclick="selectCycle(this)">
                                    每日 (¥{{ $package->price_day }})
                                </button>
                            @endif
                            @if ($package->enable_week)
                                <button type="button" class="btn btn-outline-primary" data-cycle="week" data-price="{{ $package->price_week }}" onclick="selectCycle(this)">
                                    每周 (¥{{ $package->price_week }})
                                </button>
                            @endif
                            @if ($package->enable_month)
                                <button type="button" class="btn btn-outline-primary" data-cycle="month" data-price="{{ $package->price_month }}" onclick="selectCycle(this)">
                                    每月 (¥{{ $package->price_month }})
                                </button>
                            @endif
                            @if ($package->enable_year)
                                <button type="button" class="btn btn-outline-primary" data-cycle="year" data-price="{{ $package->price_year }}" onclick="selectCycle(this)">
                                    每年 (¥{{ $package->price_year }})
                                </button>
                            @endif
{{--                            @if ($package->enable_forever)--}}
{{--                                <button type="button" class="btn btn-outline-primary" data-cycle="forever" data-price="{{ $package->price_forever }}" onclick="selectCycle(this)">--}}
{{--                                    永久 (¥{{ $package->price_forever }})--}}
{{--                                </button>--}}
{{--                            @endif--}}
                        </div>
                    </div>

                    <div class="mt-3" id="price_comparison" style="display: none;">
                        <p id="price_difference" class="h6"></p>
                    </div>

                    <div class="form-group mt-3" id="cycle_quantity_group">
                        <label for="cycle_quantity">要买多久？</label>
                        <input type="number" id="cycle_quantity" name="cycle_quantity" class="form-control" min="1" value="1" onchange="updateTotalPrice()">
                    </div>

                    <div class="mt-3">
                        <label for="total_price">总价格:</label>
                        <p id="total_price" class="h5">¥0.00</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mt-3">
                        <label>选择支付方式:</label><br>
                        <div class="form-check">
                            <input type="radio" id="wechat" name="payment_method" value="wxpay" class="form-check-input" checked>
                            <label for="wechat" class="form-check-label">微信支付</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" id="alipay" name="payment_method" value="alipay" class="form-check-input">
                            <label for="alipay" class="form-check-label">支付宝</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">提交订单</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        const prices = {
            day: {{ $package->price_day }},
            week: {{ $package->price_week }},
            month: {{ $package->price_month }},
            year: {{ $package->price_year }},
            {{--forever: {{ $package->price_forever }},--}}
        };

        function selectCycle(button) {
            const cycle = button.getAttribute('data-cycle');
            const selectedPrice = parseFloat(button.getAttribute('data-price'));

            // 清除所有按钮的高亮
            const buttons = document.querySelectorAll('.btn-group-vertical .btn');
            buttons.forEach(btn => {
                btn.classList.remove('active');
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-primary');
            });

            // 设置当前按钮的高亮
            button.classList.add('active');
            button.classList.remove('btn-outline-primary');
            button.classList.add('btn-primary');

            document.getElementById('billing_cycle').value = cycle; // 设置隐藏输入框的值

            // 更新价格比较
            updatePriceComparison(selectedPrice);

            // 根据选择的周期更新周期数量输入框的显示
            const cycleQuantityGroup = document.getElementById('cycle_quantity_group');
            if (cycle === 'forever') {
                cycleQuantityGroup.style.display = 'none';
                document.getElementById('total_price').innerText = `¥${prices[cycle].toFixed(2)}`; // 永久价格
            } else {
                cycleQuantityGroup.style.display = 'block';
                updateTotalPrice(); // 更新总价格
            }
        }

        function updatePriceComparison(selectedPrice) {
            const lowestPrice = Math.min(...Object.values(prices));
            const priceDifferenceElement = document.getElementById('price_difference');
            const priceComparisonDiv = document.getElementById('price_comparison');

            if (lowestPrice > 0) {
                const difference = ((selectedPrice - lowestPrice) / lowestPrice * 100).toFixed(2);
                if (selectedPrice < lowestPrice) {
                    priceDifferenceElement.innerText = `该选项比最低价便宜 ${difference}%`;
                    priceDifferenceElement.classList.remove('text-danger');
                    priceDifferenceElement.classList.add('text-success');
                } else if (selectedPrice > lowestPrice) {
                    priceDifferenceElement.innerText = `该选项比最低价贵 ${difference}%`;
                    priceDifferenceElement.classList.remove('text-success');
                    priceDifferenceElement.classList.add('text-danger');
                } else {
                    priceDifferenceElement.innerText = `该选项与最低价相同`;
                    priceDifferenceElement.classList.remove('text-success', 'text-danger');
                }
                priceComparisonDiv.style.display = 'block';
            }
        }

        function updateTotalPrice() {
            const cycle = document.getElementById('billing_cycle').value;
            const quantity = document.getElementById('cycle_quantity').value;
            const pricePerCycle = prices[cycle];
            const totalPrice = pricePerCycle * quantity;
            document.getElementById('total_price').innerText = `¥${totalPrice.toFixed(2)}`; // 保留两位小数
        }

        function selectLowestPriceCycle() {
            const buttons = document.querySelectorAll('.btn-group-vertical .btn');
            let lowestPrice = Infinity;
            let lowestButton = null;

            buttons.forEach(button => {
                const price = parseFloat(button.getAttribute('data-price'));
                if (price < lowestPrice) {
                    lowestPrice = price;
                    lowestButton = button;
                }
            });

            if (lowestButton) {
                selectCycle(lowestButton); // 选中价格最低的周期
            }
        }

        // 页面加载时自动选择价格最低的周期
        document.addEventListener('DOMContentLoaded', selectLowestPriceCycle);
    </script>
@endsection
