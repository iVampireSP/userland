<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Passport\Client as PassportClient;

class Client extends PassportClient
{
    protected $fillable = [
        //        'tenant_id',
        'name',
        'redirect',
        'trusted',
        'password_client',
        'pkce_client',
        'description',
        'secret',
    ];

    /**
     * 确定客户端是否应跳过授权提示。
     */
    public function skipsAuthorization(): bool
    {
        return $this->trusted;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pushApps(): HasMany
    {
        return $this->hasMany(PushApp::class);
    }

    //    /**
    //     * @throws ApiException
    //     * @throws Exception
    //     */
    //    public function enableTenant(): RedirectResponse|bool
    //    {
    //        // 检测是否有创建
    //        if ($this->tenant_id) {
    //            return true;
    //        }
    //
    //        // 如果没有 secret，则不能创建
    //        if (! $this->secret) {
    //            return back()->with('error', '应用没有密钥，不能创建租户');
    //        }
    //
    //        $tenant_api = app(KillbillClient::class)->getTenantApi();
    //
    //        try {
    //            $tenant = $tenant_api->getTenantByApiKey($this->id);
    //
    //            $this->update([
    //                'tenant_id' => $tenant->getTenantId(),
    //            ]);
    //
    //            return true;
    //        } catch (ApiException $e) {
    //            $code = $e->getCode();
    //            // throw if not 404 or 500
    //            if ($code !== 404 && $code !== 500) {
    //                throw $e;
    //            }
    //        }
    //
    //        $tenant_create = new Tenant;
    //
    //        $uuid = Uuid::uuid4()->toString();
    //        $tenant_create->setApiKey($this->id);
    //        $tenant_create->setApiSecret($this->secret);
    //        $tenant_create->setExternalKey($this->id);
    //        $tenant_create->setTenantId($uuid);
    //
    //        // 我超，这样为什么能行？
    //        app(KillbillClient::class)->setApiKey($this->id);
    //        app(KillbillClient::class)->setApiSecret($this->secret);
    //
    //        try {
    //            app(KillbillClient::class)->getTenantApi()->createTenant($tenant_create, config('app.name'));
    //            $this->update([
    //                'tenant_id' => $uuid,
    //            ]);
    //
    //        } catch (ApiException $e) {
    //            return back()->with('error', $e->getMessage());
    //        }
    //
    //        return true;
    //    }
    //
    //    public function disableTenant(): true
    //    {
    //        $this->update([
    //            'tenant_id' => null,
    //        ]);
    //
    //        return true;
    //    }
}
