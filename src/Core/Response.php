<?php

namespace GreyPanel\Core;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response
{
    protected SymfonyResponse $response;

    public function __construct(?string $content = '', int $status = 200, array $headers = [])
    {
        $this->response = new SymfonyResponse($content, $status, $headers);
    }

    public function send(): void
    {
        $this->response->send();
    }

    public function setContent(string $content): self
    {
        $this->response->setContent($content);
        return $this;
    }

    public function setStatusCode(int $code): self
    {
        $this->response->setStatusCode($code);
        return $this;
    }

    public function header(string $name, string $value): self
    {
        $this->response->headers->set($name, $value);
        return $this;
    }
}