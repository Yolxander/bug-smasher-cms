<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BasePage;
use App\Filament\Widgets\BugStatsWidget;
use App\Filament\Widgets\BugTrendsChart;
use App\Filament\Widgets\QaChecklistStatsWidget;
use App\Filament\Widgets\TeamPerformanceWidget;

class Dashboard extends BasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Bug Smasher Dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            BugStatsWidget::class,
            QaChecklistStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            BugTrendsChart::class,
            TeamPerformanceWidget::class,
        ];
    }
}
