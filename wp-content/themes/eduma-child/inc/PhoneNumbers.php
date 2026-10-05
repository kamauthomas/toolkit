<?php

namespace Toolkit\Contacts;

/** Shared, metadata-based formatting; this does not verify subscriber ownership. */
class PhoneNumbers
{
    public static function countries(): array
    {
        static $countries;
        return $countries ??= require __DIR__.'/PhoneCountries.php';
    }

    public static function normalize(mixed $value, mixed $country = 'KE'): ?string
    {
        if (!is_string($value) || !is_string($country) || strlen($value) > 40 || !preg_match('/^[+0-9\s()-]+$/D', $value)) {
            return null;
        }
        $country = strtoupper(trim($country));
        $metadata = self::countries()[$country] ?? null;
        if (!$metadata) return null;
        $compact = preg_replace('/[\s()-]/', '', $value);
        if (str_starts_with($compact, '+')) {
            if (!str_starts_with($compact, '+'.$metadata['code'])) return null;
            $national = substr($compact, strlen($metadata['code']) + 1);
            $candidates = [$national];
            if ($metadata['prefix'] !== '' && str_starts_with($national, $metadata['prefix'])) $candidates[] = substr($national, strlen($metadata['prefix']));
        } else {
            if (!ctype_digit($compact)) return null;
            $candidates = [$compact];
            foreach ([$metadata['prefix'], $metadata['code']] as $prefix) {
                if ($prefix !== '' && str_starts_with($compact, $prefix)) $candidates[] = substr($compact, strlen($prefix));
            }
        }
        // A typed calling code may include the local trunk prefix; strip it only if the country pattern then matches.
        if (!str_starts_with($compact, '+') && str_starts_with($compact, $metadata['code'])) {
            $national = substr($compact, strlen($metadata['code']));
            if ($metadata['prefix'] !== '' && str_starts_with($national, $metadata['prefix'])) $candidates[] = substr($national, strlen($metadata['prefix']));
        }
        foreach (array_unique($candidates) as $national) {
            $international = $metadata['code'].$national;
            if (strlen($international) <= 15 && preg_match('~^(?:'.$metadata['pattern'].')$~D', $national)) return '+'.$international;
        }
        return null;
    }

    public static function countryFor(mixed $value, string $preferred = 'KE'): ?string
    {
        if (self::normalize($value, $preferred)) return $preferred;
        if (!is_string($value) || strlen($value) > 40) return null;
        $digits = ltrim(preg_replace('/[\s()-]/', '', $value), '+');
        if (!ctype_digit($digits)) return null;
        $countries = self::countries();
        uasort($countries, fn($a, $b) => strlen($b['code']) <=> strlen($a['code']) ?: $b['main'] <=> $a['main']);
        foreach ($countries as $country => $metadata) {
            if (str_starts_with($digits, $metadata['code']) && self::normalize($value, $country)) return $country;
        }
        return null;
    }

    public static function display(mixed $value): ?string
    {
        $country = self::countryFor($value);
        return $country ? self::normalize($value, $country) : null;
    }
}
