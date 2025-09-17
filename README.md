# Calendar class for the Booosta PHP Framework

This module provides a calendar class. It uses fullcalendar from fullcalendar.io

It can be used with the Booosta PHP framework or as standalone PHP class.

Booosta allows to develop PHP web applications quick. It is mainly designed for small web applications. It does not provide a strict MVC distinction. Although the MVC concepts influence the framework. Templates, data objects can be seen as the Vs and Ms of MVC.

From version 4 on it resides on Github and is available from Packagist under buzanits/booosta-webapp .

## Installation with Booosta

```
composer require booosta/fullcalendar
```

## Instanciation with Booosta

```
$cal = $this->makeInstance('fullcalendar', $name, $events, $events_url);

# [...]

$this->TPL['calendar'] = $cal->get_html();
```
`$events` is an array of arrays in the form that is described in the "Usage" section beneath. `$events_url` is not used anymore and there for backwards compatibility.

## Installation as standalone object

```
# if you already have a composer.json in your directory, you can omit the first step!
composer require booosta/base
composer config repositories.asset-packagist composer https://asset-packagist.org
composer require booosta/fullcalendar
```

## Instanciation as standalone object

```
<?php
require_once __DIR__ . '/vendor/autoload.php';

use \booosta\fullcalendar\Fullcalendar;

$cal = new Fullcalendar($name, $events, $events_url);

# [...]

print $cal->loadHTML();
```

## Usage

```
$cal->set_lang('de');  # set language
$cal->set_availableViews('month,agendaWeek,agendaDay,listMonth');  # which views should be available?
$cal->set_defaultview($view);    # set default view
$cal->set_eventBackgroundColor('blue');   # background color of events
$cal->hide_days('0');  # do not display these days in agendaWeek view (0 = Sunday ... 6 = Saturday)
$cal->set_minTime('08:00');   # starting time of calendar in agendaWeek and agendaDay views
$cal->set_maxTime('19:00');   # end time of calendar in agendaWeek and agendaDay views
$cal->set_slotDuration('00:15:00');   # how long is one displayed time slot? (H:m:s)
$cal->set_dayClickCode('window.location.href="new.php?time=" + clicked_date;');   # set Javascript to execute at click on a day
$cal->set_dragDropCode('$.ajax("move.php?id=" + event_id + "&time=" + new_starttime);');   # set Javascript to execute when drag and drop an event
$cal->set_resizeCode('$.ajax("resize.php?&id=" + event_id + "&endtime=" + new_endtime);');  # set Javascript to execute when modifying the event length

$event = ['name' => 'New Years party',
          'id' => 1234,
          'startdate' => '2024-12-31 20:00:00',
          'enddate' => '2025-01-01 06:00:00',
          'description' => 'Party!',
          'color' => 'red',
          'readonly' => false,   # true = do not allow clicking on event
          'allday' => false,   # true = event lasts all day - no starttime and endtime
          'background' => false,   # true = show event in the background (optically)
          'link' => true,   # on click call '?action=edit&object_id=<id>' (where <id> is 1234 in this example) 
         ];
$cal->add_event($event);
```

