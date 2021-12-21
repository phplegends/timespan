<?php

namespace PHPLegends\Timespan;

use DateInterval;
use DateTimeInterface;
use JsonSerializable;

/**
 * The Timespan class
 *
 * @author Wallace de Souza Vizerra <wallacemaxters@gmail.com>
 * */
class Timespan implements JsonSerializable
{
    public const DEFAULT_FORMAT        = '%r%h:%i:%s';
    public const TIME_WITH_SIGN_FORMAT = '%R%h:%i:%s';

    protected $seconds = 0;


    /**
     * Create Timespan
     *
     * @param integer $hours
     * @param integer $minutes
     * @param integer $seconds
     */
    public function __construct(int $hours = 0, int $minutes = 0, int $seconds = 0)
    {
        $this->setTime($hours, $minutes, $seconds);
    }

    /**
     * Defines the time
     *
     * @param integer $hours
     * @param integer $minutes
     * @param integer $seconds
     * @return self
     */
    public function setTime(int $hours = 0, int $minutes = 0, int $seconds = 0): self
    {
        return $this->setSeconds(
            ($hours * 3600) + (60 * $minutes) + $seconds
        );
    }

    public function setSeconds(int $seconds): self
    {
        $this->seconds = $seconds;

        return $this;
    }

    public function setMinutes(int $minutes): self
    {
        return $this->setTime(0, $minutes, 0);
    }

    public function setHours(int $hours): self
    {
        return $this->setTime($hours, 0, 0);
    }

    public function addSeconds(int $seconds): self
    {
        return $this->setTime(0, 0, $this->seconds + $seconds);
    }

    public function addMinutes(int $minutes): self
    {
        return $this->setTime(0, $minutes, $this->seconds);
    }

    public function addHours(int $hours): self
    {
        return $this->setTime($hours, 0, $this->seconds);
    }

    public function getSeconds(): int
    {
        return $this->seconds;
    }

    public function asMinutes(): float
    {
        return $this->getSeconds() / 60;
    }

    public function asHours(): float
    {
        return $this->getSeconds() / 3600;
    }

    public function negative()
    {
        return $this->setSeconds(-$this->getSeconds());
    }

    public function format(string $format = self::DEFAULT_FORMAT): string
    {
        return strtr($format, Parser::replacementsFromTimestamp($this));
    }

    public function __toString()
    {
        return $this->format();
    }

    public function jsonSerialize()
    {
        return $this->format();
    }

    public function diff(self $time, bool $absolute = true): self
    {
        $seconds = $time->getSeconds() - $this->getSeconds();

        return new static(0, 0, $absolute ? abs($seconds) : $seconds);
    }

    public function isNegative(): bool
    {
        return $this->seconds < 0;
    }

    public function add(int $hours = 0, int $minutes = 0, int $seconds = 0): self
    {
        return $this->addHours($hours)->addMinutes($minutes)->addSeconds($seconds);
    }

    public function getUnits(): array
    {
        $seconds = abs($this->getSeconds());

        $time['hours'] = floor($seconds / 3600);

        $time['minutes'] = floor(($seconds - ($time['hours'] * 3600)) / 60);

        $time['seconds'] = floor($seconds % 60);

        $time['total_minutes'] = ($time['hours'] * 60) + $time['minutes'];

        return $time;
    }

    public function isEmpty(): bool
    {
        return $this->getSeconds() == 0;
    }

    public function addFromString(string $strtime)
    {
        return $this->addSeconds(strtotime($strtime, 0));
    }

    public static function createFromString(string $strtime): self
    {
        return (new static())->addFromString($strtime);
    }

    public static function createFormatFormat(string $format, string $value): self
    {
        return Parser::createTimespanFromFormat($format, $value);
    }
    
    public static function createFromDateDiff(DateTimeInterface $date1, DateTimeInterface $date2)
    {
        $seconds = $date2->getTimestamp() - $date1->getTimestamp();

        return new static(0, 0, $seconds);
    }
}
