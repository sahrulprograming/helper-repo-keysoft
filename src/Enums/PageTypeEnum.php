<?php

namespace Keysoft\HelperLibrary\Enums;

enum PageTypeEnum: string
{
    case STATIC = "STATIC";
    case DYNAMIC = "DYNAMIC";
    case CUSTOM = "CUSTOM";

    public function label(): string
    {
        return match ($this) {
            self::STATIC => 'Page Static',
            self::DYNAMIC   => 'Page Dynamic',
            self::CUSTOM    => 'Page Custom',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::STATIC => 'gray',
            self::DYNAMIC   => 'success',
            self::CUSTOM    => 'warning',
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

        $enum = self::tryFrom($value);

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
