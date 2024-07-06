<?php

namespace App\Http\Controllers\Public;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use EasyWeChat\OfficialAccount\Server;
use EasyWeChat\OfficialAccount\Application as OfficialAccount;

class WeChatController extends Controller
{
    protected OfficialAccount $officialAccount; 
    protected Server $server; 
    public function __construct()
    {
        $this->officialAccount = new OfficialAccount(config('wechat'));
        $this->server = $this->officialAccount->getServer();
    }
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        Log::info('request arrived.'); 

        $this->server->with(function($message){
            return "欢迎关注 overtrue！";
        });

        return $this->server->serve();
    }
}
