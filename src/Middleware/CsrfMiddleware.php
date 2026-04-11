<?php

namespace GreyPanel\Middleware;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Service\SessionServiceInterface;

class CsrfMiddleware
{
    private SessionServiceInterface $session;

    public function __construct(SessionServiceInterface $session)
    {
        $this->session = $session;
    }

    public function handle(Request $request, callable $next): Response
    {
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $token = $request->post('csrf_token') ?? $request->get('csrf_token');
            if (!$this->session->validateCsrfToken($token)) {
                return new Response('CSRF token mismatch', 403);
            }
        }
        return $next($request);
    }
}