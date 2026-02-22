<?php

namespace Keysoft\HelperLibrary\Enums;

enum StatusAccessEnum: int
{
    case SUSPENDED = 0;
    case ACTIVE = 1;
    case EXPIRED = 2;

    public function label(): string
    {
        return match ($this) {
            self::SUSPENDED => 'Suspended',
            self::ACTIVE   => 'Aktif',
            self::EXPIRED    => 'Expired',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SUSPENDED => 'danger',
            self::ACTIVE   => 'success',
            self::EXPIRED    => 'warning',
        };
    }

    /**
     * Ambil label dari value (aman)
     */
    public static function getLabel(int|string|null $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $enum = self::tryFrom((int) $value);

        return $enum?->label();
    }

    /**
     * Untuk Select Filament
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => $case->label(),
            ])
            ->toArray();
    }

    /**
     * Lebih aman daripada from()
     */
    public static function fromValue(int|string $value): ?self
    {
        return self::tryFrom((int) $value);
    }
}
