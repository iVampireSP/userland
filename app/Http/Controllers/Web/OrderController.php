<?php

namespace App\Http\Controllers\Web;

use App\Events\OrderPlaced;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Package;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $orders = Order::with('package', 'upgradeToPackage')->
        where('user_id', $request->user('web')->id)->latest()->paginate();

        return view('orders.index', compact('orders'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:package,package_renewal', // subscription,recharge,
            'payment_method' => 'required|in:alipay,wxpay',
        ]);

        // 检测 billing_cycle 是否在枚举列表中。
        $request->validate([
            'billing_cycle' => 'required|in:day,week,month,year',
        ]);

        //  如果不是 forever，则必须要有 quantity
        if ($request->input('billing_cycle') != 'forever') {
            $request->validate([
                'cycle_quantity' => 'required|integer|min:1',
            ]);
        }

        $quantity = $request->input('cycle_quantity', 1);

        // 如果是周，最多可以购买 4 周，如果是年，最多可以购买 5 年，如果是月，最多可以购买 6 个月
        if ($request->input('billing_cycle') == 'week') {
            if ($quantity > 4) {
                return back()->withErrors(['cycle_quantity' => '最多可以购买 4 周']);
            }
        } elseif ($request->input('billing_cycle') == 'year') {
            if ($quantity > 5) {
                return back()->withErrors(['cycle_quantity' => '最多可以购买 5 年']);
            }
        } elseif ($request->input('billing_cycle') == 'month') {
            if ($quantity > 6) {
                return back()->withErrors(['cycle_quantity' => '最多可以购买 6 个月']);
            }
        }

        // 如果 type 是 package，则需要验证 package_id
        $package_types = ['package', 'package_upgrade', 'package_renewal'];
        if (in_array($request->input('type'), $package_types)) {
            $request->validate([
                'package_id' => 'required|exists:packages,id',
            ]);

            $package = Package::findOrFail($request->input('package_id'));

            // 如果不是 package_renewal，检测用户是否购买了这个 package
            if ($request->input('type') != 'package_renewal') {
                $user_package = $request->user('web')->packages()->where('package_id', $package->id)->first();
                if ($user_package) {
                    return back()->withErrors(['package_id' => '用户已购买过该套餐']);
                }
            }

            // 检测是否有同类型但是还没有付款的订单
            if ($request->user('web')->orders()->where('type', $request->input('type'))->where('package_id', $package->id)->where('status', 'unpaid')->exists()) {
                return back()->withErrors(['package_id' => '该套餐有未付款订单，请先处理']);
            }

            // 检测是否启用相应的计费周期
            $billing_cycle_enabled = $package->{'enable_' . $request->input('billing_cycle')};
            if (!$billing_cycle_enabled) {
                return back()->withErrors(['billing_cycle' => '该套餐未启用该计费周期']);
            }

            // 检测价格是否存在
            $price = $package->{'price_' . $request->input('billing_cycle')};
            if (!$price) {
                return back()->withErrors(['price' => '价格不存在']);
            }

            // bcmath 计算价格 quantity * price
            $total_price = bcmul($price, $quantity);

            $order = Order::create([
                'user_id' => $request->user('web')->id,
                'type' => $request->input('type'),
                'payment_method' => $request->input('payment_method'),
                'package_id' => $package->id,
                'quantity' => $quantity,
                'amount' => $total_price,
                'billing_cycle' => $request->input('billing_cycle'),
                'status' => 'unpaid',
                'expired_at' => now()->addDay(),
            ]);

            return redirect()->route('orders.show', $order);
        }

        return back()->with('error', '暂不支持的类型');
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        // mark as paid
        if ($order->isUnpaid()) {
            $order->markAsPaid();
            event(new OrderPlaced($order));

            return redirect()->route('orders.index')->with('success', '订单已支付');
        }

        return back()->with('error', '订单不是 unpaid 状态');
    }
}
