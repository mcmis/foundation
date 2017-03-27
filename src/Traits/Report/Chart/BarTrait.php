<?php

namespace MCMIS\Foundation\Traits\Report\Chart;


trait BarTrait
{
    public function barchart($stats)
    {

        $barchart_dataTable = \Lava::DataTable();

        $barchart_dataTable->addStringColumn('Status');

        //reindex to arrays
        $barchart_cols = array_values($stats['raw']['title']['status']);
        $barchart_rows = array_map(function ($value) {
            return array_values($value);
        }, $stats['output']['category']);

        //adding column dynamically to barchart
        foreach ($barchart_cols as $col) $barchart_dataTable->addNumberColumn($col);
        foreach ($barchart_rows as $value) $barchart_dataTable->addRow($value);

        \Lava::BarChart('Category', $barchart_dataTable, [
            'legend' => ['position' => 'top'],
            'orientation' => 'horizontal',
            'chartArea' => ['width' => '92%', 'height' => '70%'],
            'isStacked' => true
        ]);

        $linechart_dataTable = \Lava::DataTable();

        $linechart_dataTable->addStringColumn('Status');

        //reindex to arrays
        $linechart_cols = array_values($stats['raw']['title']['category']);
        $linechart_rows = array_map(function ($value) {
            return array_values($value);
        }, $stats['output']['status']);

        //adding column dynamically to barchart
        foreach ($linechart_cols as $col) $linechart_dataTable->addNumberColumn($col);
        foreach ($linechart_rows as $value) $linechart_dataTable->addRow($value);

        \Lava::LineChart('Status', $linechart_dataTable, [
            'legend' => ['position' => 'top'],
            'orientation' => 'horizontal',
            'chartArea' => ['width' => '92%', 'height' => '70%'],
            'isStacked' => true
        ]);
        /* END LAVACHART */
    }
}
