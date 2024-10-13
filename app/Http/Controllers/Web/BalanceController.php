<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Balance;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BalanceController extends Controller
{
    public function index(Request $request): View
    {
        $balance = $request->user()->balance;

        $balances = (new Balance)->thisUser()->latest()->paginate(20);

        return view('balances.index', compact('balance', 'balances'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'amount' => 'required|numeric|min:0.1|max:10000',
            'payment' => 'required|in:wxpay,alipay',
        ]);

        $balance = Balance::create([
            'user_id' => auth('web')->id(),
            'amount' => $request->input('amount'),
            'payment' => $request->input('payment'),
        ]);

        return redirect()->route('balances.show', compact('balance'));
    }

    /**
     * 显示充值页面和状态(ajax)
     */
    public function show(Request $request, Balance $balance): RedirectResponse|JsonResponse|View
    {
        if ($balance->isPaid()) {
            if ($request->ajax()) {
                return $this->success($balance);
            }

            return view('balances.process', compact('balance'));
        } else {
            if ($request->ajax()) {
                return $this->success($balance);
            }
        }

        // 目前正在测试阶段，直接通过
        $balance->update([
            'paid_at' => now(),
        ]);

        if ($request->ajax()) {
            return $this->success($balance);
        }

        // 本地环境直接通过
        //        if (app()->environment('local')) {
        //            $balance->update([
        //                'paid_at' => now(),
        //            ]);
        //
        //            if ($request->ajax()) {
        //                return $this->success($balance);
        //            }
        //        }

        if ($balance->isOverdue()) {
            if (now()->diffInDays($balance->created_at) > 1) {
                if ($request->ajax()) {
                    return $this->forbidden($balance);
                }

                return redirect()->route('index')->with('error', '订单已逾期。');
            }
        }

        $balance->load('user');

        $subject = config('app.display_name').' 充值';

        $order = [
            'out_trade_no' => $balance->order_id,
        ];

        $code = QrCode::size(150);
        $qr_code = $code->generate(config('app.url'));
        //
        //        if ($balance->payment === 'wechat') {
        //            $pay = $this->xunhu_wechat($balance, $subject);
        //
        //            $qr_code = $code->generate($pay['url']);
        //        } else {
        //            $order['subject'] = $subject;
        //            $order['total_amount'] = $balance->amount;
        //
        //            $pay = Pay::alipay()->web($order);
        //
        //            return view('balances.alipay', ['html' => (string) $pay->getBody()]);
        //        }

        if (! isset($qr_code)) {
            return redirect()->route('index')->with('error', '支付方式错误。');
        }

        return view('balances.pay', compact('balance', 'qr_code'));
    }

    /**
     * @throws ValidationException
     */
    public function notify(
        Request $request, $payment
    ): View|JsonResponse {
        $is_paid = false;

        if ($payment === 'alipay') {
            $out_trade_no = $request->input('out_trade_no');
        } elseif ($payment === 'wechat') {
            $out_trade_no = $request->input('trade_order_id');
        } else {
            abort(400, '支付方式错误');
        }

        // 检测订单是否存在
        $balance = (new Balance)->where('order_id', $out_trade_no)->with('user')->first();
        if (! $balance) {
            abort(404, '找不到订单。');
        }

        // 检测订单是否已支付
        if ($balance->paid_at !== null) {
            if ($request->ajax()) {
                return $this->success($balance);
            }

            return view('balances.process', compact('balance'));
        }

        // 处理验证
        if ($payment === 'wechat') {
            if (! ($request->filled('hash') || $request->filled('trade_order_id'))) {
                return $this->error('参数错误。');
            }

            if ($request->filled('plugins') && $request->input('plugins') != config('app.name')) {
                return $this->error('插件不匹配。');
            }

            $hash = $this->xunhu_hash($request->toArray());
            if ($request->input('hash') != $hash) {
                Log::debug('hash error', $request->toArray());
            }

            if ($request->input('status') === 'OD') {
                $is_paid = true;
            }
        }

        if ($is_paid) {
            // $balance->user->charge($balance->amount, $balance->payment, $balance->order_id);
            $balance->update([
                'paid_at' => now(),
            ]);
        }

        if ($request->ajax()) {
            return $this->success($balance);
        }

        return view('balances.process', compact('balance'));
    }

    /**
     * 获取交易记录
     *
     * @param  mixed  $request
     */
    //    public function transactions(
    //        Request $request
    //    ): View {
    //        $modules = Module::all();
    //
    //        $transactions = (new Transaction)->where('user_id', auth('web')->id())->where('payment', '!=', 'module_balance');
    //
    //        if ($request->has('type')) {
    //            $transactions = $transactions->where('type', $request->type);
    //        }
    //
    //        if ($request->has('payment')) {
    //            $transactions = $transactions->where('payment', $request->payment);
    //        }
    //
    //        $transactions = $transactions->latest()->paginate(100)->withQueryString();
    //
    //        return view('balances.transactions', compact('transactions', 'modules'));
    //    }
}
