<?php
namespace booosta\fullcalendar;

use \booosta\Framework as b;
b::init_module('fullcalendar');

class Fullcalendar extends \booosta\calendar\Calendar
{
  use moduletrait_fullcalendar;

  protected $bg_events;
  protected $eventClickCode, $dayClickCode, $dragDropCode, $resizeCode;
  protected $eventBackgroundColor, $defaultview;
  protected $hide_days, $minTime, $maxTime, $availableViews;
  protected $slotDuration;
  protected $id_prefix = 'fullcalendar';

  public function __construct($name = 'calendar', $events = null, $events_url = null)
  {
    parent::__construct($name, $events, $events_url);

    $this->bg_events = [];
    $this->defaultview = 'dayGridMonth';
    $this->availableViews = 'dayGridMonth,timeGridWeek,timeGridDay';
    $this->set_eventBackgroundColor = $this->$eventBackgroundColor ?? $this->config('calendarEventColor') ?? 'blue';
  }

  public function after_instanciation()
  {
    parent::after_instanciation();

    if(is_object($this->topobj) && is_a($this->topobj, "\\booosta\\webapp\\Webapp")):
      $this->topobj->moduleinfo['fullcalendar'] = true;
      if($this->topobj->moduleinfo['jquery']['use'] == '') $this->topobj->moduleinfo['jquery']['use'] = true;
    endif;
  }

  public function set_defaultview($defaultview) { $this->defaultview = $defaultview; }
  public function set_eventClickCode($code) { $this->eventClickCode = $code; }
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
    $b = $this->baseurl ?? '';
    $eventlist = '';
    ksort($this->events);

    foreach($this->events as $event):
      $d = $event->get_data();
      #\booosta\debug($d);

      if($d['enddate']):
        $enddate = "end: '{$d['enddate']}', ";
      elseif(strlen($d['startdate']) == 10):   // date without time is given = event is all day
        $enddate = '';
      else:
        $enddate = "end: '" . date('Y-m-d H:i:s', strtotime($d['startdate'] . ' +1 hour')) . "', ";
      endif;

      $extradata = '';
      if($d['link']) $extradata .= "url: '{$d['link']}', ";
      if($d['color']) $extradata .= "backgroundColor: '{$d['color']}', borderColor: '{$d['color']}', ";
      if($d['readonly']) $extradata .= 'editable: false, ';
      if($d['background']) $extradata .= "rendering: 'background', ";
      if($d['allday']) $extradata .= "allDay: true, ";

      $eventlist .= "{ id: {$d['id']}, title: '{$d['name']}', start: '{$d['startdate']}', $enddate $extradata}, ";
    endforeach;

    if($this->eventClickCode):
      $eventClickCode = $this->eventClickCode === true ? "window.location.href = '$b?action=edit&object_id=' + event_id;" : $this->eventClickCode;
      $eventClickCode = "var event_title = info.event.title; var act_view = info.view.name; var event_startdate = info.event.startStr;
        var event_enddate = info.event.end; var event_url = info.event.url; var event_id = info.event.id; $eventClickCode;
        return false;";
    endif;

    if($this->dayClickCode):
      $dayClickCode = $this->dayClickCode === true ? "window.location.href = '$b?action=new&startdate=' + encodeURIComponent(clicked_date);" : $this->dayClickCode;
      $dayClickCode = "var act_view = info.view.name; var clicked_date = info.dateStr; $dayClickCode";
    endif;

    if($this->dragDropCode):
      $dragDropCode = $this->dragDropCode === true ? "$.ajax('$b?action=calendar_move&object_id=' + event_id + '&startdate=' + encodeURIComponent(new_startdate));" : $this->dragDropCode;
      $dragDropCode = "var event_title = info.event.title; var new_startdate = info.event.startStr; var event_id = info.event.id;
                       $dragDropCode";
    endif;

    if($this->resizeCode):
      $resizeCode = $this->resizeCode === true ? "$.ajax('$b?action=calendar_resize&object_id=' + event_id + '&enddate=' + encodeURIComponent(new_enddate));" : $this->resizeCode;
      $resizeCode = "var event_title = info.event.title; var event_id = info.event.id; var new_enddate = info.event.endStr;
                     $resizeCode";
    endif;

    if($this->eventBackgroundColor) $extracode .= "eventColor: '$this->eventBackgroundColor', ";

    if($this->date):
      if($this->defaultview == null) $this->defaultview = 'agendaDay';
      $extracode .= "defaultDate: '$this->date', ";
    endif;

    if($this->slotDuration) $extracode .= "slotDuration: '$this->slotDuration', ";

    if($this->hide_days || $this->hide_days === '0') $extracode .= "hiddenDays: [ $this->hide_days ], ";
    if($this->minTime) $extracode .= "slotMinTime: '$this->minTime', ";
    if($this->maxTime) $extracode .= "slotMaxTime: '$this->maxTime', ";

    $code = "var $this->id = new FullCalendar.Calendar($('#$this->id')[0], {
      headerToolbar: { start: 'prev,next today', center: 'title', end: '$this->availableViews' },
      locale: '$this->lang', buttonIcons: false, weekNumbers: true, editable: true, eventDisplay: 'block',
      eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: false },
      initialView: '$this->defaultview', selectable: true, $extracode
      events: [ $eventlist ], eventClick: function(info) { $eventClickCode },
      dateClick: function(info) { $dayClickCode }, eventDrop: function(info) { $dragDropCode },
      eventResize: function(info) { $resizeCode },
      }); $this->id.render();
    ";

    return $this->get_ready_code($code);
  }
}
