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

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->request->query->get($key, $default);
    }

    public function post(string $key, mixed $default = null): mixed
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

    public function getClientIp(): string
    {
        return $this->request->getClientIp();
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->request->headers->get($key, $default);
    }

    public function postInt(string $key, int $default = 0): int
    {
        return (int)($this->request->request->get($key, $default));
    }
    public function postString(string $key, string $default = ''): string
    {
        return (string)($this->request->request->get($key, $default));
    }

    public function postBool(string $key, bool $default = false): bool
    {
        return (bool)($this->request->request->get($key, $default));
    }

    public function getInt(string $key, int $default = 0): int
    {
        return (int)($this->request->query->get($key, $default));
    }
    public function getString(string $key, string $default = ''): string
    {
        return (string)($this->request->query->get($key, $default));
    }
}
