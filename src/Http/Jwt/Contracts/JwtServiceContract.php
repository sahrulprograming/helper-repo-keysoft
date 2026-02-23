<?php

namespace Keysoft\HelperLibrary\Http\Jwt\Contracts;

interface JwtServiceContract
{
    public function generate(array $payload): string;

    public function validate(string $token): array;

    public function refresh(string $token): string;

    public function blacklist(string $token): void;
}