<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QaChecklistResource\Pages;
use App\Models\QaChecklist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QaChecklistResource extends Resource
{
    protected static ?string $model = QaChecklist::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'QA Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Checklist Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'archived' => 'Archived',
                            ])
                            ->required()
                            ->default('draft'),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description')
                                    ->maxLength(65535),
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
                            ]),
                        Forms\Components\DateTimePicker::make('due_date'),
                        Forms\Components\Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                            ]),
                        Forms\Components\TagsInput::make('tags')
                            ->separator(',')
                            ->suggestions([
                                'bug',
                                'feature',
                                'enhancement',
                                'documentation',
                                'testing',
                                'security',
                                'performance',
                                'ui/ux',
                                'backend',
                                'frontend'
                            ]),
                        Forms\Components\Hidden::make('created_by')
                            ->default(fn () => auth()->id()),
                        Forms\Components\Hidden::make('updated_by')
                            ->default(fn () => auth()->id()),
                    ]),
                Forms\Components\Section::make('Checklist Items')
                    ->description('Add, reorder, and manage your checklist items.')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('item_text')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Forms\Components\Select::make('item_type')
                                    ->options([
                                        'checkbox' => 'Checkbox',
                                        'radio' => 'Radio',
                                        'text' => 'Text',
                                    ])
                                    ->required()
                                    ->default('checkbox'),
                                Forms\Components\Toggle::make('is_required')
                                    ->required()
                                    ->default(false),
                                Forms\Components\Hidden::make('order_number')
                                    ->default(function ($livewire) {
                                        $items = $livewire->getRecord()?->items ?? collect();
                                        return $items->count() + 1;
                                    }),
                            ])
                            ->columns(1)
                            ->defaultItems(1)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string =>
                                isset($state['item_text'])
                                    ? "Item " . ($state['order_number'] ?? '') . ": " . $state['item_text']
                                    : null
                            )
                            ->addActionLabel('Add Checklist Item')
                            ->columnSpanFull()
                            ->cloneable()
                            ->collapsed(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'active' => 'success',
                        'archived' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('category')
                    ->searchable(),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray',
                        'medium' => 'warning',
                        'high' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items'),
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
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'archived' => 'Archived',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                    ]),
                Tables\Filters\SelectFilter::make('creator')
                    ->relationship('creator', 'name'),
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
            'index' => Pages\ListQaChecklists::route('/'),
            'create' => Pages\CreateQaChecklist::route('/create'),
            'edit' => Pages\EditQaChecklist::route('/{record}/edit'),
        ];
    }
}
