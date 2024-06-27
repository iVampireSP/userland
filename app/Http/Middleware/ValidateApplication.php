<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApplication
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 不能使用下面的方法，因为 Client 类继承 Passport
        //        $application = $request->user('application');

        $bearerToken = $request->bearerToken();

        if (! $bearerToken) {
            return $this->unauthorized();
        }
        $tokens = explode('|', $request->bearerToken());

        if (count($tokens) !== 2) {
            return $this->forbidden('The token is invalid.');
        }

        $client_id = $tokens[0];
        $client_secret = $tokens[1];
        $application = Client::whereId($client_id)->whereSecret($client_secret)->first();

        if (! $application) {
            return $this->forbidden('The application does not exist.');
        }

        if ($application->revoked) {
            return $this->forbidden('The application has been revoked.');
        }

        if (! $application->trusted) {
            return $this->forbidden('The application is not our official application.');
        }

        // 将 client_id 混入 request
        $request->merge(['client_id' => $application->id]);

        return $next($request);
    }
}
