<?php

use PHPLegends\Timespan\Timespan;
use PHPUnit\Framework\TestCase;

class TimespanTest extends TestCase
{
    public function testGetSeconds()
    {
        $this->assertEquals(90, (new Timespan(0, 1, 30))->getSeconds());
        $this->assertEquals(90, (new Timespan(0, 0, 90))->getSeconds());
        $this->assertEquals(3600, (new Timespan(1, 0, 0))->getSeconds());
    }

    public function testAsMinutes()
    {
        $timespan = new Timespan(0, 0, 30);

        $this->assertEquals(
            0.5,
            $timespan->asMinutes()
        );
    }


    public function testAsHours()
    {
        $timespan = new Timespan(0, 30, 0);

        $this->assertEquals(
            0.5,
            $timespan->asHours()
        );
    }

    public function testFormat()
    {
        $times = [
            ['00:01:10', [0, 1, 10]],
            ['00:01:11', [0, 0, 71]],
            ['01:00:00', [0, 0, 3600]],
            ['02:00:00', [0, 60, 3600]],
            ['01:05:00', [1, 5, 0]],
        ];

        foreach ($times as [$expected, $args]) {
            $timespan = new Timespan(...$args);
            $this->assertEquals($expected, $timespan->format());
        }

        $times_with_custom_formats = [
            ['01 minutes and 10 seconds', [0, 1, 10], '%i minutes and %s seconds'],
            ['+00:01:11', [0, 0, 71], '%R%h:%i:%s'],
            ['-01:00:00', [0, 0, -3600], Timespan::DEFAULT_FORMAT],
        ];

        foreach ($times_with_custom_formats as [$expected, $args, $format]) {
            $timespan = new Timespan(...$args);
            $this->assertEquals($expected, $timespan->format($format));
        }
    }

    public function testAdd()
    {
        $timespan = new Timespan();

        $this->assertEquals(0, $timespan->getSeconds());

        $this->assertEquals(59, $timespan->add(0, 0, 59)->getSeconds());

        $this->assertEquals(60 + 59, $timespan->add(0, 1)->getSeconds());

        $this->assertEquals(3600 + 60 + 59, $timespan->add(1)->getSeconds());

        $timespan = new Timespan();

        $this->assertEquals(-3600, $timespan->add(-1)->getSeconds());
    }

    public function testSetTime()
    {
        $timespan = new Timespan();

        $timespan->setTime(1, 1, 1);

        $this->assertEquals(3600 + 60 + 1, $timespan->getSeconds());
    }

    public function testIsEmpty()
    {
        foreach ([-1, 0, 1] as $minute) {
            $timespan = new Timespan(0, $minute, 0);

            $this->assertEquals($minute === 0, $timespan->isEmpty());
        }

        $this->assertTrue((new Timespan())->isEmpty());
    }

    public function testDiff()
    {
        $t1 = new Timespan(0, 2, 10);
        $t2 = new Timespan(0, 3, 30);

        $diff = $t1->diff($t2);

        $this->assertInstanceOf(Timespan::class, $diff);
        $this->assertEquals('00:01:20', $diff->format());
        $this->assertEquals(60 + 20, $diff->getSeconds());


        $diff = $t2->diff($t1, false);

        $this->assertTrue($diff->isNegative());
        $this->assertEquals('-00:01:20', $diff->format());
        $this->assertEquals(-60 -20, $diff->getSeconds());
    }


    public function testAddFromString()
    {
        $timespan = new TimeSpan(0, 1, 0);

        $this->assertEquals(60, $timespan->getSeconds());
        $this->assertEquals(60 + 30, $timespan->addFromString('+30 seconds')->getSeconds());
        $this->assertEquals(60 + 30 + 60, $timespan->addFromString('+1 minutes')->getSeconds());
        $this->assertEquals(60 + 30 + 60 + 3600, $timespan->addFromString('+1 hours')->getSeconds());
        $this->assertEquals(60 + 30 + 60 + 3600 - 1800, $timespan->addFromString('-30 minutes')->getSeconds());
    }

    public function testCreateFromString()
    {
        foreach ([
            '+2 days' => '48:00:00',
            '-3 days' => '-72:00:00',
            '+1 day +1 minutes +30 seconds' => '24:01:30',
        ] as $string => $expected) {
            $this->assertEquals($expected, Timespan::createFromString($string)->format());
        }
    }

    public function testCreateFormatFormat()
    {
        foreach ([
            120  => [Timespan::DEFAULT_FORMAT, '00:02:00'],
            90   => [Timespan::DEFAULT_FORMAT, '00:01:30'],
            15   => ['%s seconds', '15 seconds'],
            15   => ['%R%s seconds', '+15 seconds'],
            -15  => ['%R%s seconds', '-15 seconds'],
        ] as $expected => $args) {
            $this->assertEquals(
                $expected,
                Timespan::createFormatFormat(...$args)->getSeconds()
            );
        }
    }

    public function testAddMinutes()
    {
        $timespan = new Timespan(0, 1, 30);
        $this->assertEquals(90, $timespan->getSeconds());

        $timespan->addMinutes(2);
        $this->assertEquals(90 + 120, $timespan->getSeconds());
    }

    public function testAddHours()
    {
        $timespan = new Timespan(0, 0, 30);
        $this->assertEquals(30, $timespan->getSeconds());

        $timespan->addHours(2);
        $this->assertEquals(30 + 7200, $timespan->getSeconds());
    }

    public function testGetUnits()
    {
        $timespan = new Timespan(1, 2, 30);

        $units = $timespan->getUnits();

        foreach (['hours', 'minutes', 'seconds', 'total_minutes'] as $key) {
            $this->assertArrayHasKey($key, $units);
        }
    }



    public function testCreateFromDateInterval()
    {
        $d1 = new DateTime('2015-01-01 23:00:00');
        $d2 = new DateTime('2015-01-02 02:00:00');

        $interval = $d1->diff($d2);

        $timespan = Timespan::createFromDateInterval($interval);

        $this->assertEquals('03:00:00', $timespan->format());
    }

    public function testCreateFromDateDiff()
    {
        $timespan = Timespan::createFromDateDiff(
            new DateTime('2015-01-01 23:00:00'),
            new DateTime('2015-01-03 02:00:00')
        );

        $this->assertEquals('27:00:00', $timespan->format());

        $timespan = Timespan::createFromDateDiff(
            new DateTime('2015-01-03 02:00:00'),
            new DateTime('2015-01-01 23:00:00')
        );
        $this->assertEquals('-27:00:00', $timespan->format());
    }
}
