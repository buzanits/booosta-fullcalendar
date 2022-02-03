<?php
namespace booosta\fullcalendar;

use \booosta\Framework as b;
b::init_module('fullcalendar');

class Fullcalendar extends \booosta\calendar\Calendar
{
  use moduletrait_fullcalendar;

  protected $bg_events;
  protected $eventClickCode, $eventRightClickCode, $dayClickCode, $dragDropCode, $resizeCode;
  protected $eventBackgroundColor, $defaultview;
  protected $hide_days, $minTime, $maxTime, $availableViews;
  protected $slotDuration;
  protected $id_prefix = 'fullcalendar';

  public function __construct($name = null, $events = null, $events_url = null)
  {
    parent::__construct($name, $events, $events_url);

    $this->bg_events = [];
    $this->defaultview = 'month';
    $this->availableViews = 'month,agendaWeek,agendaDay';
  }

  public function after_instanciation()
  {
    parent::after_instanciation();

    if(is_object($this->topobj) && is_a($this->topobj, "\\booosta\\webapp\\Webapp")):
      $this->topobj->moduleinfo['fullcalendar'] = true;
      if($this->topobj->moduleinfo['jquery']['use'] == '') $this->topobj->moduleinfo['jquery']['use'] = true;
    endif;
  }

  public function set_enddate($date) { $this->date = date('Y-m-d', strtotime($date)); }
  public function set_defaultview($defaultview) { $this->defaultview = $defaultview; }
  public function set_eventClickCode($code) { $this->eventClickCode = $code; }
  public function set_eventRightClickCode($code) { $this->eventRightClickCode = $code; }
  public function set_dayClickCode($code) { $this->dayClickCode = $code; }
  public function set_dragDropCode($code) { $this->dragDropCode = $code; }
  public function set_resizeCode($code) { $this->resizeCode = $code; }
  public function set_eventBackgroundColor($code) { $this->eventBackgroundColor = $code; }
  public function hide_days($days) { $this->hide_days = $days; }
  public function set_minTime($code) { $this->minTime = $code; }
  public function set_maxTime($code) { $this->maxTime = $code; }
  public function set_slotDuration($code) { $this->slotDuration = $code; }
  public function set_availableViews($code) { $this->availableViews = $code; }
  public function add_bg_event($event) { $this->add_event($event, true); }

  protected function str2time($date)
  {
    if(is_numeric($date)) return $date;
    return strtotime($date);
  }

  public function get_htmlonly() { 
    return "<div id='$this->id'></div>";
  }

  public function get_js()
  {
    $eventlist = '';
    ksort($this->events);
    foreach($this->events as $event):
      $d = $event->get_data();
      #\booosta\debug($d);

      if($d['enddate']):
        $enddate = "end: '{$d['enddate']}', ";
      elseif(strlen($d['date']) == 10):   // date without time is given = event is all day
        $enddate = '';
      else:
        $enddate = "end: '" . date('Y-m-d H:i:s', strtotime($d['date'] . ' +1 hour')) . "', ";
      endif;

      $extradata = '';
      if($d['link']) $extradata .= "url: '{$d['link']}', ";
      if($d['color']) $extradata .= "backgroundColor: '{$d['color']}', borderColor: '{$d['color']}', ";
      if($d['readonly']) $extradata .= 'editable: false, ';
      if($d['background']) $extradata .= "rendering: 'background', ";

      $eventlist .= "{ id: {$d['id']}, title: '{$d['name']}', start: '{$d['date']}', $enddate $extradata}, ";
      #$eventlist .= "{ id: {$d['id']}, title: '{$d['name']}', start: '{$d['date']}', $url$enddate$color$editable}, ";
    endforeach;

    if($this->eventClickCode)
      $eventClickCode = "var event_title = calEvent.title; var act_view = view.name; var event_start = calEvent.start;
        var event_end = calEvent.end; var event_url = calEvent.url; var event_id = calEvent.id; $this->eventClickCode;
        return false;";

    if($this->eventRightClickCode)
      $eventRightClickCode = "var event_title = calEvent.title; var act_view = view.name; var event_start = calEvent.start;
        var event_end = calEvent.end; var event_url = calEvent.url; var event_id = calEvent.id; $this->eventRightClickCode;
        return false;";

    if($this->dayClickCode)
      $dayClickCode = "var act_view = view.name; var clicked_date = date.format(); $this->dayClickCode";

    if($this->dragDropCode)
      $dragDropCode = "var event_title = event.title; var new_starttime = event.start.format(); var event_id = event.id;
                       $this->dragDropCode";

    if($this->resizeCode)
      $resizeCode = "var event_title = event.title; var event_id = event.id; var new_endtime = event.end.format();
                     $this->resizeCode";

    if($this->eventBackgroundColor) $extracode .= "eventBackgroundColor: '$this->eventBackgroundColor', eventBorderColor: '$this->eventBackgroundColor', ";

    if($this->date):
      if($this->defaultview == null) $this->defaultview = 'agendaDay';
      $extracode .= "defaultDate: '$this->date', ";
    endif;

    if($this->slotDuration) $extracode .= "slotDuration: '$this->slotDuration', ";

    if($this->hide_days || $this->hide_days === '0') $extracode .= "hiddenDays: [ $this->hide_days ], ";
    if($this->minTime) $extracode .= "minTime: '$this->minTime', ";
    if($this->maxTime) $extracode .= "maxTime: '$this->maxTime', ";

    $code = "$('#$this->id').fullCalendar({
      header: { left: 'prev,next today', center: 'title', right: '$this->availableViews' },
      locale: '$this->lang', buttonIcons: false, weekNumbers: true, editable: true, eventLimit: true, timeFormat: 'H:mm',
      slotLabelFormat: 'H:mm', defaultView: '$this->defaultview', $extracode
      events: [ $eventlist ], eventClick: function(calEvent, jsEvent, view) { $eventClickCode },
      eventRightclick: function(calEvent, jsEvent, view) { $eventRightClickCode },
      dayClick: function(date, jsEvent, view) { $dayClickCode }, eventDrop: function(event, delta, revertFunc) { $dragDropCode },
      eventResize: function(event, delta, revertFunc) { $resizeCode },
      }); 
    ";

    if(is_object($this->topobj) && is_a($this->topobj, "\\booosta\\webapp\\webapp")):
      $this->topobj->add_jquery_ready($code);
      return '';
    else:
      return "\$(document).ready(function(){ $code });";
    endif;
  }
}
