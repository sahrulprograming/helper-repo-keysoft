<?php

namespace Keysoft\HelperLibrary\Enums;

enum StatusEnum: int
{
    case INACTIVE = 0;
    case ACTIVE = 1;
    case DRAFT = 2;

    public function label(): string
    {
        return match ($this) {
            self::INACTIVE => 'Tidak Aktif',
            self::ACTIVE   => 'Aktif',
            self::DRAFT    => 'Draft',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::INACTIVE => 'gray',
            self::ACTIVE   => 'success',
            self::DRAFT    => 'warning',
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
