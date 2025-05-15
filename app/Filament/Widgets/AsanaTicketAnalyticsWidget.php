<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\AsanaTicket;
use Illuminate\Support\Facades\DB;

class AsanaTicketAnalyticsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalTickets = AsanaTicket::count();
        $bugTickets = AsanaTicket::where('ticket_type', 'bug')->count();
        $qaTickets = AsanaTicket::where('ticket_type', 'qa_checklist')->count();

        // Get tickets by status
        $ticketsByStatus = AsanaTicket::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        return [
            Stat::make('Total Tickets', $totalTickets)
                ->description('All time tickets')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('gray'),

            Stat::make('Bug Tickets', $bugTickets)
                ->description('Bug-related tickets')
                ->descriptionIcon('heroicon-m-bug-ant')
                ->color('danger'),

            Stat::make('QA Tickets', $qaTickets)
                ->description('QA checklist tickets')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('success'),

            Stat::make('Active Tickets', $ticketsByStatus['open'] ?? 0)
                ->description('Currently open tickets')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
