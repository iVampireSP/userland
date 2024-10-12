<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Killbill\Client\KillbillClient;
use Killbill\Client\Swagger\ApiException;
use Killbill\Client\Swagger\Model\Tenant;
use Laravel\Passport\Client as PassportClient;

class Client extends PassportClient
{
    protected $fillable = [
        'tenant_id'
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


    /**
     * @throws ApiException
     */
    public function enableTenant(): true
    {
        // 检测是否有创建
        if ($this->tenant_id) {
            return true;
        }

        $tenant_api = app(KillbillClient::class)->getTenantApi();

        try {
            $tenant = $tenant_api->getTenantByApiKey($this->id);

            $this->update([
                'tenant_id' => $tenant->getTenantId()
            ]);

            return true;
        } catch (ApiException $e) {
            // if not 404
            if ($e->getCode() !== 404) {
                throw $e;
            }
        }
        $tenant = new Tenant();
        $tenant->setApiKey($this->id);
        $tenant->setApiSecret($this->secret);

        $tenant = $tenant_api->createTenant($tenant, config('app.name'));

        $this->update([
            'tenant_id' => $tenant->getTenantId()
        ]);

        return true;
    }

    public function disableTenant(): true
    {
        $this->update([
            'tenant_id' => null
        ]);

        return true;
    }
}
