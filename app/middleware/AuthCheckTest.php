<?php

namespace app\middleware;

use ReflectionClass;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class AuthCheckTest implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        if (session('user')) {
            // Already logged in, continue with the request
            return $handler($request);
        }

        // Get the methods that do not require login using reflection
        $controller = new ReflectionClass($request->controller);
        $noNeedLogin = $controller->getDefaultProperties()['noNeedLogin'] ?? [];

        // Method requires login
        if (!in_array($request->action, $noNeedLogin)) {
            // Intercept the request and return a redirect response, stopping the request from continuing
            return redirect('/user/login');
        }

        // Method does not require login, continue with the request
        return $handler($request);
    }
}
