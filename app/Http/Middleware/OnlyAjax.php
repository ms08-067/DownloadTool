<?php

namespace App\Http\Middleware;

use Closure;

class OnlyAjax
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
    	if ( !$request->ajax()){
    		/**return response('Forbidden.', 403);*/
    		/**return response()->view('errors.403', [], 403);*/
    		abort(403, 'Unauthorized action.');
    	}
    	return $next($request);
    }
}
