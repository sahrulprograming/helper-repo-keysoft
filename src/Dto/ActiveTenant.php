<?php

namespace Keysoft\HelperLibrary\Dto;

use Illuminate\Support\Facades\Session;

class ActiveTenant
{
    public function __construct(
        public int $id,
        public string $name,
        public string $code,
    ) {}

    public static function fromSession(): ?self
    {
        $data = Session::get('active_tenant');

        if (!$data) {
            return null;
        }

        return new self(
            $data['id'],
            $data['name'],
            $data['code'],
        );
    }
}