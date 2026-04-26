<?php

declare(strict_types=1);

namespace GreyPanel\Core;

class JsonResponse extends Response
{
    public function __construct($data, int $status = 200, array $headers = [])
    {
        $headers['Content-Type'] = 'application/json';
        parent::__construct(json_encode($data, JSON_UNESCAPED_UNICODE), $status, $headers);
    }
}
