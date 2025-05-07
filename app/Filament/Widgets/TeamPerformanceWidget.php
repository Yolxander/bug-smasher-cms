<?php

namespace App\Filament\Widgets;

use App\Models\Team;
use App\Models\Bug;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TeamPerformanceWidget extends BaseWidget
{
    protected static ?string $heading = 'Team Performance';
    protected static ?int $sort = 3;

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->query(
                Team::query()
                    ->withCount(['members', 'bugs'])
                    ->withCount(['bugs as resolved_bugs' => function (Builder $query) {
                        $query->where('status', 'resolved');
                    }])
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('members_count')
                    ->label('Team Size')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('bugs_count')
                    ->label('Total Bugs')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('resolved_bugs')
                    ->label('Resolved')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('resolution_rate')
                    ->label('Resolution Rate')
                    ->state(function ($record): string {
                        if ($record->bugs_count === 0) return '0%';
                        return round(($record->resolved_bugs / $record->bugs_count) * 100) . '%';
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("(SELECT COUNT(*) FROM bugs WHERE bugs.team_id = teams.id AND bugs.status = 'resolved') / NULLIF((SELECT COUNT(*) FROM bugs WHERE bugs.team_id = teams.id), 0) {$direction}");
                    }),
            ])
            ->defaultSort('resolved_bugs', 'desc');
    }
}
