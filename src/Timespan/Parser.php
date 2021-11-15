<?php

namespace PHPLegends\Timespan;

/**
 * @author Wallace de Souza Vizerra <wallacemaxters@gmail.com>
*/
class Parser
{
    public const TIME_FORMAT_MEMBERS = [
        'HOURS'         => '%h',
        'MINUTES'       => '%i',
        'SECONDS'       => '%s',
        'TOTAL_MINUTES' => '%I',
        'SIGN'          => '%R',
        'NEGATIVE_SIGN' => '%r',
    ];

    public const TIME_REPLACEMENTS = [
        '%h' => '(?<h>\d+)',
        '%i' => '(?<i>[0-5][0-9])',
        '%s' => '(?<s>[0-5][0-9])',
        '%R' => '(?<r>[\+\-]{1})',
        '%r' => '(?<r>\-{1})?',
    ];

    public static function createTimespanFromFormat(string $format, string $value): Timespan
    {
        $units = static::createUnitsFromFormat($format, $value);

        $time = new Timespan($units['h'], $units['i'], $units['s']);

        if (static::isNegativeSign($units['r'])) {
            $time->negative();
        }

        return $time;
    }

    public static function replacementsFromTimestamp(Timespan $time): array
    {
        $units      = $time->getUnits();
        $isNegative = $time->isNegative();

        $formats = static::TIME_FORMAT_MEMBERS;

        return [
            $formats['HOURS']         => sprintf('%02d', $units['hours']),
            $formats['MINUTES']       => sprintf('%02d', $units['minutes']),
            $formats['SECONDS']       => sprintf('%02d', $units['seconds']),
            $formats['TOTAL_MINUTES'] => sprintf('%02d', $units['total_minutes']),
            $formats['SIGN']          => $isNegative ? '-' : '+',
            $formats['NEGATIVE_SIGN'] => $isNegative ? '-' : null,
        ];
    }

    public static function createUnitsFromFormat(string $format, string $value): array
    {
        if (! preg_match(static::createRegexFromFormat($format), $value, $units)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid string format for "%s"',
                $value
            ));
        }

        return $units + [
            'h' => 0,
            'i' => 0,
            's' => 0,
            'r' => '+'
        ];
    }

    public static function isValidFormat(string $format, string $value): bool
    {
        return preg_match(static::createRegexFromFormat($format), $value) > 0;
    }

    protected static function isNegativeSign($sign): bool
    {
        return $sign === '-';
    }

    public static function createRegexFromFormat(string $format): string
    {
        $regex = strtr($format, static::TIME_REPLACEMENTS);

        return "/^$regex$/";
    }
}
