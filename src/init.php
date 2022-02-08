<?php
namespace booosta\fullcalendar;

\booosta\Framework::add_module_trait('webapp', 'fullcalendar\webapp');

trait webapp
{
  protected function preparse_fullcalendar()
  {
    $libpath = 'vendor/npm-asset/fullcalendar';

    if($this->moduleinfo['fullcalendar'])
      $this->add_includes("
            <script type='text/javascript' src='{$this->base_dir}$libpath/main.min.js'></script>
            <link rel='stylesheet' type='text/css' href='{$this->base_dir}$libpath/main.min.css' />\n");
  }
}


trait actions
{
  protected function action_fullcalendar_move()
  {
    $newstart = date('Y-m-d H:i:s', strtotime($this->VAR['starttime']));
    #\booosta\debug("id: $this->id, newstart: $newstart");

    $obj = $this->get_dbobject();
    $oldstart = $obj->get('starttime');
    $oldend = $obj->get('endtime');
    $duration = strtotime($oldend) - strtotime($oldstart);
    if($duration <= 0) $duration = 3600;

    $obj->set('starttime', $newstart);
    $obj->set('endtime', date('Y-m-d H:i:s', strtotime($newstart) + $duration));
    $obj->update();

    booosta\ajax\Ajax::print_response(null, ['result' => '']);
    $this->no_output = true;
  }

  protected function action_fullcalendar_resize()
  {
    $newend = date('Y-m-d H:i:s', strtotime($this->VAR['endtime']));
    #\booosta\debug("id: $this->id, newend: $newend");

    $obj = $this->get_dbobject();
    $obj->set('endtime', $newend);
    $obj->update();

    booosta\ajax\Ajax::print_response(null, ['result' => '']);
    $this->no_output = true;
  }
}
