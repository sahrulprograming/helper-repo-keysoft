<?php

namespace App\Support;

use Illuminate\Support\Str;

class LocationCodeGenerator
{
    public function makeCode(
        string $name,
        int $singleWordLimit = 3,
        int $multiWordLimit = 8,
        string $fallback = 'LOC',
    ): string {
        $normalized = trim((string) preg_replace('/[^A-Za-z0-9\s]+/', ' ', Str::ascii($name)));
        $parts = array_values(array_filter(preg_split('/\s+/', strtoupper($normalized)) ?: []));

        if ($parts === []) {
            return '';
        }

        if (count($parts) === 1) {
            return Str::limit($parts[0], $singleWordLimit, '');
        }

        $code = '';

        foreach ($parts as $part) {
            $code .= $part[0] ?? '';
        }

        $code = Str::limit($code, $multiWordLimit, '');

        if ($code !== '') {
            return $code;
        }

        return Str::upper(trim($fallback));
    }

    /**
     * @param  array<int, string>  $usedCodes
     * @param  callable(string): bool  $existsConflict
     */
    public function resolveUniqueCode(
        string $baseCode,
        array &$usedCodes,
        callable $existsConflict,
        int $maxLength = 128,
        string $fallback = 'LOC',
    ): string {
        $baseCode = Str::upper(trim($baseCode));

        if ($baseCode === '') {
            $baseCode = Str::upper(trim($fallback));
        }

        if ($baseCode === '') {
            $baseCode = 'LOC';
        }

        $candidate = $baseCode;
        $counter = 1;

        while (in_array($candidate, $usedCodes, true) || $existsConflict($candidate)) {
            $suffix = (string) $counter;
            $candidate = Str::limit($baseCode, $maxLength - strlen($suffix), '') . $suffix;
            $counter++;
        }

        $usedCodes[] = $candidate;

        return $candidate;
    }
}
