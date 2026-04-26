<?php

declare(strict_types=1);

namespace GreyPanel\Core;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request
{
    private SymfonyRequest $request;

    public function __construct()
    {
        $this->request = SymfonyRequest::createFromGlobals();
    }

    public function getMethod(): string
    {
        return $this->request->getMethod();
    }

    public function getPath(): string
    {
        return $this->request->getPathInfo();
    }

    public function get(string $key, $default = null)
    {
        return $this->request->query->get($key, $default);
    }

    public function post(string $key, $default = null)
    {
        return $this->request->request->get($key, $default);
    }

    public function all(): array
    {
        return array_merge($this->request->query->all(), $this->request->request->all());
    }

    public function files(): array
    {
        return $this->request->files->all();
    }

    public function getSession(): \Symfony\Component\HttpFoundation\Session\SessionInterface
    {
        return $this->request->getSession();
    }

    public function isAjax(): bool
    {
        return $this->request->isXmlHttpRequest();
    }

    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    public function getRequest(): SymfonyRequest
    {
        return $this->request;
    }

    public function postArray(string $key, array $default = []): array
    {
        $value = $this->request->request->all()[$key] ?? $default;
        return is_array($value) ? $value : $default;
    }
}
