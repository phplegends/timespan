<?php

namespace PHPLegends\Timespan;

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

    /**
     * @var int
     */
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
     * Sets the time
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

    /**
     * Set Seconds
     *
     * @param integer $seconds
     * @return self
     */
    public function setSeconds(int $seconds): self
    {
        $this->seconds = $seconds;

        return $this;
    }

    /**
     * Set minutes
     *
     * @param integer $minutes
     * @return self
     */
    public function setMinutes(int $minutes): self
    {
        return $this->setTime(0, $minutes, 0);
    }

    /**
     * Add hours
     *
     * @param integer $hours
     * @return self
     */
    public function setHours(int $hours): self
    {
        return $this->setTime($hours, 0, 0);
    }

    /**
     * Add seconds
     *
     * @param integer $seconds
     * @return self
     */
    public function addSeconds(int $seconds): self
    {
        return $this->setTime(0, 0, $this->seconds + $seconds);
    }

    /**
     * Add minutes
     *
     * @param integer $minutes
     * @return self
     */
    public function addMinutes(int $minutes): self
    {
        return $this->setTime(0, $minutes, $this->seconds);
    }

    /**
     * Add hours
     *
     * @param integer $hours
     * @return self
     */
    public function addHours(int $hours): self
    {
        return $this->setTime($hours, 0, $this->seconds);
    }

    /**
     * Gets total seconds of time
     *
     * @return integer
     */
    public function getSeconds(): int
    {
        return $this->seconds;
    }

    /**
     * Gets the time as minutes
     *
     * @return float
     */
    public function asMinutes(): float
    {
        return $this->getSeconds() / 60;
    }

    /**
     * Get as hours
     *
     * @return float
     */
    public function asHours(): float
    {
        return $this->getSeconds() / 3600;
    }

    /**
     * Turns the time into negative
     *
     * @return void
     */
    public function negative()
    {
        return $this->setSeconds(-$this->getSeconds());
    }

    /**
     * Gets a formatted time 
     *
     * @param string $format
     * @return string
     */
    public function format(string $format = self::DEFAULT_FORMAT): string
    {
        return strtr($format, Parser::replacementsFromTimestamp($this));
    }

    /**
     * Returns a string from the default Timespan format 
     *
     * @return string
     */
    public function __toString()
    {
        return $this->format();
    }

    /**
     * JsonSerialize interface implementation
     *
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->format();
    }

    /**
     * Creates a new time based on diff with another Timespan
     *
     * @param self $time
     * @param boolean $absolute
     * @return self
     */
    public function diff(self $time, bool $absolute = true): self
    {
        $seconds = $time->getSeconds() - $this->getSeconds();

        return new static(0, 0, $absolute ? abs($seconds) : $seconds);
    }

    /**
     * Returns if the time is negative
     *
     * @return boolean
     */
    public function isNegative(): bool
    {
        return $this->seconds < 0;
    }

    /**
     * Sums time into timespan
     *
     * @param integer $hours
     * @param integer $minutes
     * @param integer $seconds
     * @return self
     */
    public function add(int $hours = 0, int $minutes = 0, int $seconds = 0): self
    {
        return $this->addHours($hours)->addMinutes($minutes)->addSeconds($seconds);
    }

    /**
     * Get all units of time as array
     *
     * @return array
     */
    public function getUnits(): array
    {
        $seconds = abs($this->getSeconds());

        $time['hours'] = floor($seconds / 3600);

        $time['minutes'] = floor(($seconds - ($time['hours'] * 3600)) / 60);

        $time['seconds'] = floor($seconds % 60);

        $time['total_minutes'] = ($time['hours'] * 60) + $time['minutes'];

        return $time;
    }

    /**
     * Checks if is a zero time
     *
     * @return boolean
     */
    public function isEmpty(): bool
    {
        return $this->getSeconds() == 0;
    }

    /**
     * Add amount of time to Timespan from a strtotime string
     *
     * @param string $strtime
     * @return self
     */
    public function addFromString(string $strtime): self
    {
        return $this->addSeconds(strtotime($strtime, 0));
    }

    /**
     * Creates a Timespan instance from a strtotime string
     *
     * @static
     * @param string $strtime
     * @return self
     */
    public static function createFromString(string $strtime): self
    {
        return (new static)->addFromString($strtime);
    }

    /**
     * Create a Timespan from a specific format
     *
     * @param string $format
     * @param string $value
     * @return self
     */
    public static function createFromFormat(string $format, string $value): self
    {
        return Parser::createTimespanFromFormat($format, $value);
    }
    
    /**
     * Creates Timespan from  a diff of DateTimes
     *
     * @param DateTimeInterface $date1
     * @param DateTimeInterface $date2
     * @return self
     */
    public static function createFromDateDiff(DateTimeInterface $date1, DateTimeInterface $date2): self
    {
        $seconds = $date2->getTimestamp() - $date1->getTimestamp();

        return new static(0, 0, $seconds);
    }
}
