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
