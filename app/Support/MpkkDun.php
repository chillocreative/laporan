<?php

namespace App\Support;

class MpkkDun
{
    public const UNASSIGNED = 'Belum Ditetapkan';

    /** @var array<string, string>|null normalized MPKK name => DUN */
    protected static ?array $lookup = null;

    /**
     * Normalize an MPKK name so spelling variants compare equal
     * (KG/KAMPUNG, BARU/BAHARU, DATOK/DATUK, TOK BEDU/TO'BEDU, 3/TIGA,
     * word order).
     */
    public static function normalize(string $name): string
    {
        $name = strtoupper($name);
        $name = str_replace("'", '', $name);
        $name = preg_replace('/[^A-Z0-9 ]+/', ' ', $name);
        $name = preg_replace('/\bMPKK\b/', ' ', $name);

        $replacements = [
            '/\bKG\b/' => 'KAMPUNG',
            '/\bBARU\b/' => 'BAHARU',
            '/\bDATOK\b/' => 'DATUK',
            '/\bTO BEDU\b|\bTOK BEDU\b/' => 'TOBEDU',
            '/\b3\b/' => 'TIGA',
        ];
        $name = preg_replace(array_keys($replacements), array_values($replacements), $name);

        $tokens = array_unique(array_filter(explode(' ', $name)));
        sort($tokens);

        return implode(' ', $tokens);
    }

    public static function dunFor(string $mpkkName): string
    {
        if (self::$lookup === null) {
            self::$lookup = [];
            foreach (config('mpkk_dun', []) as $dun => $names) {
                foreach ($names as $name) {
                    self::$lookup[self::normalize($name)] = $dun;
                }
            }
        }

        return self::$lookup[self::normalize($mpkkName)] ?? self::UNASSIGNED;
    }

    /** DUN display order: configured order first, unassigned last. */
    public static function order(): array
    {
        return array_merge(array_keys(config('mpkk_dun', [])), [self::UNASSIGNED]);
    }
}
