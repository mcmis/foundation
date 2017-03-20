<?php
namespace MCMIS\Foundation\Traits\Report;


trait ExportTrait
{

    public function doExport($data, $chart = null)
    {
        $extender = sys('MCMIS\Contracts\ExporterExtenders\ReportExporterExtender');
        if ($chart) {
            $extender->enableChart();
        }

        $extender->export($data);
    }

}