<?php

namespace Keysoft\HelperLibrary\Dto;

use Illuminate\Support\Facades\Session;

class ActiveTenant
{
    private const SESSION_KEY = 'active_tenant';

    public function __construct(
        public int $id,
        public string $name,
        public string $code,
    ) {}

    public static function fromSession(): ?self
    {
        return self::fromArray(Session::get(self::SESSION_KEY));
    }

    public static function fromArray(mixed $data): ?self
    {
        if (!is_array($data)) {
            return null;
        }

        if (!isset($data['id'], $data['name'], $data['code'])) {
            return null;
        }

        return new self(
            (int) $data['id'],
            (string) $data['name'],
            (string) $data['code'],
        );
    }

    public static function fromPayload(array $payload): ?self
    {
        $extra = $payload['extra'] ?? null;

        if (is_object($extra)) {
            $extra = (array) $extra;
        }

        $tenant = is_array($extra) ? ($extra['tenant'] ?? null) : null;

        if (is_object($tenant)) {
            $tenant = (array) $tenant;
        }

        if ($dto = self::fromArray($tenant)) {
            return $dto;
        }

        return null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
        ];
    }

    public function toSession(): void
    {
        Session::put(self::SESSION_KEY, $this->toArray());
    }

    public static function forgetSession(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}
