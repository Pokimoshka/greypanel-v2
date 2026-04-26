<?php

declare(strict_types=1);

namespace GreyPanel\Core;

class RedirectResponse extends Response
{
    public function __construct(string $url, int $status = 302)
    {
        parent::__construct('', $status, ['Location' => $url]);
    }

    public function getTargetUrl(): string
    {
        return $this->response->headers->get('Location');
    }
}
