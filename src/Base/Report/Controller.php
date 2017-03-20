<?php
namespace MCMIS\Foundation\Base\Report;

use MCMIS\Foundation\BaseController;
use MCMIS\Foundation\Traits\Report\ExportTrait;
use MCMIS\Foundation\Traits\Report\FiltersTrait;
use MCMIS\Foundation\Traits\Report\StatsTrait;

class Controller extends BaseController
{
    use FiltersTrait, StatsTrait, ExportTrait;

    protected $workflow, $filtered_model, $exporter;

    /**
     *   Load dependencies on construction:
     *
     *`  MCMIS\Contracts\WorkFlow
     *   MCMIS\Contracts\Report\ModelFiltration
     *   MCMIS\Contracts\ExporterExtenders\ReportExporterExtender
    */

}
