<?php
namespace MCMIS\Foundation\Traits\Report;


trait ExportTrait
{

    public function doExport($data, $chart = null)
    {
        if ($chart) {
            $this->extender->enableChart();
        }

        $this->extender->export($data);
    }

}