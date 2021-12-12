<?php

namespace App\Entity;

class ResultJson
{
    public
        $status = 0,
        $status_msg = '';

    public function __construct(int $status, string $status_msg)
    {
        $this->status = $status;
        $this->status_msg = $status_msg;
    }
}