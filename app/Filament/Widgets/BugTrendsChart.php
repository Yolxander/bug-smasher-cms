<?php

namespace App\Filament\Widgets;

use App\Models\Bug;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BugTrendsChart extends ChartWidget
{
    protected static ?string $heading = 'Bug Trends';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Bug::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "resolved" THEN 1 ELSE 0 END) as resolved')
        )
            ->groupBy('date')
            ->orderBy('date')
            ->limit(30)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Reported Bugs',
                    'data' => $data->pluck('total')->toArray(),
                    'borderColor' => '#F59E0B',
                ],
                [
                    'label' => 'Resolved Bugs',
                    'data' => $data->pluck('resolved')->toArray(),
                    'borderColor' => '#10B981',
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
