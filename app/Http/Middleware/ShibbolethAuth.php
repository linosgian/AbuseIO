<?php

namespace AbuseIO\Http\Middleware;

use Auth;
use Closure;
use Log;

/**
 * Class CheckAccount.
 */
class ShibbolethAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //TODO: Grab this from the config file
        // Check if the user has the right entitlement
        if (!array_key_exists("REDIRECT_entitlement", $_SERVER)){
            abort(403, "Forbidden.");
        }
        if (strpos($_SERVER["REDIRECT_entitlement"], "grnet.gr:abuse") === false){
            abort(403, "Forbidden.");
        }
        if (!$request->session()->has('domain')){
            // Example persistent-id input: https://vho.grnet.gr/idp/shibboleth!https://snf-791594.vm.okeanos.grnet.gr/
            $idp_hostname = explode("!", $_SERVER["REDIRECT_persistent-id"])[0];
            $host = explode(".", parse_url($idp_hostname, PHP_URL_HOST));

            // Keep only the tld and the host, aka rip off the subdomains(e.g. grnet.gr)
            $origin = $host[count($host) - 2] . "." . $host[count($host) - 1];
            $request->session()->put('domain', $origin);
        }
        return $next($request);
    }
}

