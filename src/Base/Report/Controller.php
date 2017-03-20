<?php
namespace MCMIS\Foundation\Base\Report;


use MCMIS\Contracts\WorkFlow;
use MCMIS\Contracts\Report\ModelFiltration;
use MCMIS\Contracts\ExporterExtenders\ReportExporterExtender;
use MCMIS\Foundation\BaseController;
use MCMIS\Foundation\Traits\Report\ExportTrait;
use MCMIS\Foundation\Traits\Report\FiltersTrait;
use MCMIS\Foundation\Traits\Report\StatsTrait;

class Controller extends BaseController
{
    use FiltersTrait, StatsTrait, ExportTrait;

    protected $workflow, $filtered_model, $exporter;

    public function __construct(WorkFlow $workFlow, ModelFiltration $filtered_model, ReportExporterExtender $exporter)
    {
        $this->workflow = $workFlow;
        $this->filtered_model = $filtered_model;
        $this->exporter = $exporter;
    }
}
