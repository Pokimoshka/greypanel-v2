<?php

declare(strict_types=1);

namespace GreyPanel\Core;

class JsonResponse extends Response
{
    public function __construct(mixed $data, int $status = 200, bool $alreadyEncoded = false)
    {
        $content = $alreadyEncoded ? $data : json_encode($data, JSON_UNESCAPED_UNICODE);
        parent::__construct($content, $status, ['Content-Type' => 'application/json']);
    }
}
