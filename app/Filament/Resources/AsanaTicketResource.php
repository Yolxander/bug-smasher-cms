<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsanaTicketResource\Pages;
use App\Models\AsanaTicket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Radio;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Builder;

class AsanaTicketResource extends Resource
{
    protected static ?string $model = AsanaTicket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Bug Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Ticket Information')
                    ->schema([
                        Radio::make('ticket_type')
                            ->label('Ticket Type')
                            ->options([
                                'bug' => 'Bug',
                                'qa_checklist' => 'QA Checklist',
                            ])
                            ->required()
                            ->live(),
                        Select::make('bug_id')
                            ->relationship('bug', 'title')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get): bool => $get('ticket_type') === 'bug')
                            ->required(fn (Get $get): bool => $get('ticket_type') === 'bug'),
                        Select::make('qa_checklist_id')
                            ->relationship('qaChecklist', 'title')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get): bool => $get('ticket_type') === 'qa_checklist')
                            ->required(fn (Get $get): bool => $get('ticket_type') === 'qa_checklist')
                            ->label('QA Checklist'),
                        Select::make('status')
                            ->options([
                                'open' => 'Open',
                                'in_progress' => 'In Progress',
                                'resolved' => 'Resolved',
                                'closed' => 'Closed',
                            ])
                            ->required(),
                        Textarea::make('notes')
                            ->label('Additional Notes')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bug.title')
                    ->searchable()
                    ->sortable()
                    ->visible(fn (?AsanaTicket $record): bool => $record && $record->bug_id !== null),
                Tables\Columns\TextColumn::make('qaChecklistItem.item_text')
                    ->searchable()
                    ->sortable()
                    ->visible(fn (?AsanaTicket $record): bool => $record && $record->qa_checklist_item_id !== null),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'open' => 'danger',
                        'in_progress' => 'warning',
                        'resolved' => 'success',
                        'closed' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAsanaTickets::route('/'),
            'create' => Pages\CreateAsanaTicket::route('/create'),
            'edit' => Pages\EditAsanaTicket::route('/{record}/edit'),
        ];
    }
}
