<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class CustomerAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        $customer = auth('customer')->user();
        if (!$customer || $customer->status !== 'active') {
            if ($request->isMethod('get') && $request->routeIs('customer.links.show')) {
                $request->session()->put('customer.intended',route('customer.links.show',['token'=>$request->route('token')],false));
            }
            return $request->expectsJson() ? response()->json(['message'=>'Silakan masuk.'], 401) : redirect()->route('customer.login');
        }
        return $next($request);
    }
}
