<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QaChecklistCategoryResource\Pages;
use App\Filament\Resources\QaChecklistCategoryResource\RelationManagers;
use App\Models\QaChecklistCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class QaChecklistCategoryResource extends Resource
{
    protected static ?string $model = QaChecklistCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'QA Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Category Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                                $operation === 'create' ? $set('slug', Str::slug($state)) : null
                            ),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\ColorPicker::make('color')
                            ->default('#6B7280'),
                        Forms\Components\Select::make('icon')
                            ->options([
                                'heroicon-o-academic-cap' => 'Academic Cap',
                                'heroicon-o-adjustments-horizontal' => 'Adjustments Horizontal',
                                'heroicon-o-adjustments-vertical' => 'Adjustments Vertical',
                                'heroicon-o-archive-box' => 'Archive Box',
                                'heroicon-o-arrow-down' => 'Arrow Down',
                                'heroicon-o-arrow-left' => 'Arrow Left',
                                'heroicon-o-arrow-right' => 'Arrow Right',
                                'heroicon-o-arrow-up' => 'Arrow Up',
                                'heroicon-o-bell' => 'Bell',
                                'heroicon-o-bookmark' => 'Bookmark',
                                'heroicon-o-briefcase' => 'Briefcase',
                                'heroicon-o-calendar' => 'Calendar',
                                'heroicon-o-chart-bar' => 'Chart Bar',
                                'heroicon-o-chart-pie' => 'Chart Pie',
                                'heroicon-o-check-circle' => 'Check Circle',
                                'heroicon-o-clipboard' => 'Clipboard',
                                'heroicon-o-clipboard-document' => 'Clipboard Document',
                                'heroicon-o-clipboard-document-check' => 'Clipboard Document Check',
                                'heroicon-o-clock' => 'Clock',
                                'heroicon-o-cloud' => 'Cloud',
                                'heroicon-o-code-bracket' => 'Code Bracket',
                                'heroicon-o-cog' => 'Cog',
                                'heroicon-o-command-line' => 'Command Line',
                                'heroicon-o-computer-desktop' => 'Computer Desktop',
                                'heroicon-o-cube' => 'Cube',
                                'heroicon-o-document' => 'Document',
                                'heroicon-o-document-text' => 'Document Text',
                                'heroicon-o-exclamation-circle' => 'Exclamation Circle',
                                'heroicon-o-exclamation-triangle' => 'Exclamation Triangle',
                                'heroicon-o-eye' => 'Eye',
                                'heroicon-o-flag' => 'Flag',
                                'heroicon-o-folder' => 'Folder',
                                'heroicon-o-globe' => 'Globe',
                                'heroicon-o-home' => 'Home',
                                'heroicon-o-information-circle' => 'Information Circle',
                                'heroicon-o-key' => 'Key',
                                'heroicon-o-light-bulb' => 'Light Bulb',
                                'heroicon-o-link' => 'Link',
                                'heroicon-o-magnifying-glass' => 'Magnifying Glass',
                                'heroicon-o-map' => 'Map',
                                'heroicon-o-megaphone' => 'Megaphone',
                                'heroicon-o-paper-airplane' => 'Paper Airplane',
                                'heroicon-o-pencil' => 'Pencil',
                                'heroicon-o-phone' => 'Phone',
                                'heroicon-o-photo' => 'Photo',
                                'heroicon-o-presentation-chart-line' => 'Presentation Chart Line',
                                'heroicon-o-puzzle-piece' => 'Puzzle Piece',
                                'heroicon-o-question-mark-circle' => 'Question Mark Circle',
                                'heroicon-o-rocket-launch' => 'Rocket Launch',
                                'heroicon-o-server' => 'Server',
                                'heroicon-o-shield-check' => 'Shield Check',
                                'heroicon-o-sparkles' => 'Sparkles',
                                'heroicon-o-star' => 'Star',
                                'heroicon-o-tag' => 'Tag',
                                'heroicon-o-truck' => 'Truck',
                                'heroicon-o-user' => 'User',
                                'heroicon-o-user-group' => 'User Group',
                                'heroicon-o-wrench' => 'Wrench',
                                'heroicon-o-x-circle' => 'X Circle',
                            ])
                            ->searchable()
                            ->preload()
                            ->helperText('Select a Heroicon to represent this category')
                            ->columnSpanFull()
                            ->getOptionLabelUsing(fn (string $value): string => view('components.icon-select-option', [
                                'icon' => $value,
                                'label' => match ($value) {
                                    'heroicon-o-academic-cap' => 'Academic Cap',
                                    'heroicon-o-adjustments-horizontal' => 'Adjustments Horizontal',
                                    'heroicon-o-adjustments-vertical' => 'Adjustments Vertical',
                                    'heroicon-o-archive-box' => 'Archive Box',
                                    'heroicon-o-arrow-down' => 'Arrow Down',
                                    'heroicon-o-arrow-left' => 'Arrow Left',
                                    'heroicon-o-arrow-right' => 'Arrow Right',
                                    'heroicon-o-arrow-up' => 'Arrow Up',
                                    'heroicon-o-bell' => 'Bell',
                                    'heroicon-o-bookmark' => 'Bookmark',
                                    'heroicon-o-briefcase' => 'Briefcase',
                                    'heroicon-o-calendar' => 'Calendar',
                                    'heroicon-o-chart-bar' => 'Chart Bar',
                                    'heroicon-o-chart-pie' => 'Chart Pie',
                                    'heroicon-o-check-circle' => 'Check Circle',
                                    'heroicon-o-clipboard' => 'Clipboard',
                                    'heroicon-o-clipboard-document' => 'Clipboard Document',
                                    'heroicon-o-clipboard-document-check' => 'Clipboard Document Check',
                                    'heroicon-o-clock' => 'Clock',
                                    'heroicon-o-cloud' => 'Cloud',
                                    'heroicon-o-code-bracket' => 'Code Bracket',
                                    'heroicon-o-cog' => 'Cog',
                                    'heroicon-o-command-line' => 'Command Line',
                                    'heroicon-o-computer-desktop' => 'Computer Desktop',
                                    'heroicon-o-cube' => 'Cube',
                                    'heroicon-o-document' => 'Document',
                                    'heroicon-o-document-text' => 'Document Text',
                                    'heroicon-o-exclamation-circle' => 'Exclamation Circle',
                                    'heroicon-o-exclamation-triangle' => 'Exclamation Triangle',
                                    'heroicon-o-eye' => 'Eye',
                                    'heroicon-o-flag' => 'Flag',
                                    'heroicon-o-folder' => 'Folder',
                                    'heroicon-o-globe' => 'Globe',
                                    'heroicon-o-home' => 'Home',
                                    'heroicon-o-information-circle' => 'Information Circle',
                                    'heroicon-o-key' => 'Key',
                                    'heroicon-o-light-bulb' => 'Light Bulb',
                                    'heroicon-o-link' => 'Link',
                                    'heroicon-o-magnifying-glass' => 'Magnifying Glass',
                                    'heroicon-o-map' => 'Map',
                                    'heroicon-o-megaphone' => 'Megaphone',
                                    'heroicon-o-paper-airplane' => 'Paper Airplane',
                                    'heroicon-o-pencil' => 'Pencil',
                                    'heroicon-o-phone' => 'Phone',
                                    'heroicon-o-photo' => 'Photo',
                                    'heroicon-o-presentation-chart-line' => 'Presentation Chart Line',
                                    'heroicon-o-puzzle-piece' => 'Puzzle Piece',
                                    'heroicon-o-question-mark-circle' => 'Question Mark Circle',
                                    'heroicon-o-rocket-launch' => 'Rocket Launch',
                                    'heroicon-o-server' => 'Server',
                                    'heroicon-o-shield-check' => 'Shield Check',
                                    'heroicon-o-sparkles' => 'Sparkles',
                                    'heroicon-o-star' => 'Star',
                                    'heroicon-o-tag' => 'Tag',
                                    'heroicon-o-truck' => 'Truck',
                                    'heroicon-o-user' => 'User',
                                    'heroicon-o-user-group' => 'User Group',
                                    'heroicon-o-wrench' => 'Wrench',
                                    'heroicon-o-x-circle' => 'X Circle',
                                    default => $value,
                                },
                            ])->render()),
                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->default(true),
                        Forms\Components\TextInput::make('order')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Forms\Components\Hidden::make('created_by')
                            ->default(fn () => auth()->id()),
                        Forms\Components\Hidden::make('updated_by')
                            ->default(fn () => auth()->id()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\ColorColumn::make('color'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('creator')
                    ->relationship('creator', 'name'),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc');
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
            'index' => Pages\ListQaChecklistCategories::route('/'),
            'create' => Pages\CreateQaChecklistCategory::route('/create'),
            'edit' => Pages\EditQaChecklistCategory::route('/{record}/edit'),
        ];
    }
}
