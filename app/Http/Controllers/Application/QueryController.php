<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\Ban;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class QueryController extends Controller
{
    public function user(User $user): JsonResponse
    {
        return $this->success($user);
    }

    public function bans(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'code' => 'nullable|string|max:255',
        ]);

        $bans = $user->bans()->whereClientId($request->client_id)->wherePardoned(false);

        if ($request->filled('code')) {
            $bans = $bans->whereCode($request->input('code'));
        }

        $bans = $bans->latest()->simplePaginate(20);

        return $this->success($bans);
    }

    public function ban(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'expired_at' => 'required|date',
            'code' => 'required|string|max:255',
        ]);

        $expired_at = Carbon::parse($request->input('expired_at'))->toDateTimeString();

        $ban = $user->bans()->whereCode($request->input('code'))->first();

        if ($ban && ! $ban->pardoned) {
            // 已经有了，不能再封禁
            return $this->error('The email with code already banned.');
        }

        $ban = $user->bans()->create([
            'client_id' => $request->client_id,
            'reason' => $request->input('reason'),
            'expired_at' => $expired_at,
            'code' => $request->input('code'),
        ]);

        return $this->success($ban->refresh());
    }

    public function unban(Request $request, User $user, Ban $ban): JsonResponse
    {
        $ban->pardon();

        return $this->noContent();
    }

    public function emailBan(Request $request): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'expired_at' => 'required|date',
            'code' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $expired_at = Carbon::parse($request->input('expired_at'))->toDateTimeString();

        $ban = Ban::whereEmail($request->input('email'))->whereCode($request->input('code'))->first();
        if ($ban && ! $ban->pardoned) {
            // 已经有了，不能再封禁
            return $this->error('The email with code already banned.');
        }

        $ban = Ban::create([
            'email' => $request->input('email'),
            'client_id' => $request->client_id,
            'reason' => $request->input('reason'),
            'expired_at' => $expired_at,
            'code' => $request->input('code'),
        ]);

        return $this->success($ban->refresh());
    }

    public function emailBans(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $bans = Ban::whereClientId($request->client_id)->whereEmail($request->input('email'))->wherePardoned(false);

        if ($request->filled('code')) {
            $bans = $bans->whereCode($request->input('code'));
        }

        $bans = $bans->latest()->simplePaginate(20);

        return $this->success($bans);
    }
}
