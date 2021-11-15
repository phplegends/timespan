# PHPLegends\Timespan Library

This is a library to work only with time durations in PHP.

With it you can work with the time very simply:


```php
use PHPLegends\Timespan\Timespan;

$time = new Timespan(0, 0, 10);

echo $time->format(); // '00:00:10'

$time->addSeconds(30);

echo $time->format(); // '00:00:40'

$time->addSeconds(-50);

echo $time->format(); // '-00:00:10'

$time->addMinutes(2);

echo $time->format('%i minutes %s seconds');  // '1 minutes 50 seconds'

```

An example of time duration:

```php
$time = Timespan::createFromFormat(
    Timespan::DEFAULT_FORMAT, 
    '26:00:00'
);

echo $time->format(); // '26:00:00'
```


For create time duration from DateTime Diff, you can use `Timespan::createFromDateDiff`.

```php
$timespan = Timespan::createFromDateDiff(
    new DateTime('2021-01-01 23:00:00'),
    new DateTime('2021-01-03 02:00:00')
);

echo $timespan->format(); // '27:00:00'
```
