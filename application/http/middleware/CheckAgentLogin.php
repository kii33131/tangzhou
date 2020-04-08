<?php

namespace app\http\middleware;

class CheckAgentLogin
{
    public function handle($request, \Closure $next)
    {
    	if (!$request->session('agent')) {
    		return redirect(url('agent/login/login'));
	    }

	    return $next($request);
    }
}
