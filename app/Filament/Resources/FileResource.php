<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FileResource\Pages;
use App\Filament\Resources\FileResource\RelationManagers;
use App\Models\File;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class FileResource extends Resource
{
    protected static ?string $model = File::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $creating = $form->getOperation() === 'create';
        return $form
            ->schema([
                Section::make()
                    ->columns([
                        'default' => 2,
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type_id')
                            ->required()
                            ->name('Type')
                            ->disabled(fn($record) => !is_null($record))
                            ->relationship(name: "type", titleAttribute: "name")
                            ->preload()
                            ->live(),
                        Forms\Components\Select::make('extension')
                            ->name('Extension')
                            ->disabled(fn($record) => !is_null($record))
                            ->placeholder('Select an extension')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                            ])
                            ->createOptionUsing(function (array $data) {
                                $newOption = strtolower($data['name']); // Lowercase for the value
                                $label = ucfirst($data['name']);       // Capitalize the label
                    
                                // Store new options persistently
                                $options = Cache::get('extensions', [
                                    "pdf" => "PDF",
                                    "jpg" => "JPG",
                                    "png" => "PNG",
                                    "mp4" => "MP4",
                                    "zip" => "ZIP",
                                    "VTT" => "VTT",
                                ]);

                                $options[$newOption] = $label;

                                Cache::put('extensions', $options); // Save updated options to cache
                    
                                return [
                                    'value' => $newOption,
                                    'label' => $label,
                                ];
                            })
                            ->options(fn() => Cache::get('extensions', [
                                "pdf" => "PDF",
                                "jpg" => "JPG",
                                "png" => "PNG",
                                "mp4" => "MP4",
                                "zip" => "ZIP",
                                "VTT" => "VTT",
                            ])) // Dynamically fetch options
                            ->live(),
                        Forms\Components\Select::make('cache_time')
                            ->name('Cache Time')
                            ->placeholder('Select a cache time')
                            ->required()
                            ->options([
                                "86400" => "1 day",
                                "172800" => "2 days",
                                "604800" => "1 week",
                                "1209600" => "2 weeks",
                                "2592000" => "1 month",
                            ])
                            ->live(),
                        Forms\Components\Select::make('category_id')
                            ->required()
                            ->name('Category')
                            ->relationship(name: "category", titleAttribute: "name")
                            ->preload()
                        // ->visible(
                        //     fn($record, $get) => $get('type_id') !== 1
                        // )
                        ,



                    ]),
                $creating ? Section::make()
                    ->schema([
                        Repeater::make('versions')
                            ->visible(fn($record, $get) => $get('type_id') == 2)
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\FileUpload::make('path')
                                    ->image()
                                    ->disk('s3')
                                    ->directory('form-attachments')
                                    ->imageEditor(),
                            ])
                            ->addable(false)
                            ->deletable(false),
                        Repeater::make('versions')
                            ->visible(fn($record, $get) => $get('type_id') == 1)
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\FileUpload::make('path')
                                    ->disk('s3')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->directory('form-attachments'),
                            ])
                            ->addable(false)
                            ->deletable(false),
                        Repeater::make('versions')
                            ->visible(fn($record, $get) => $get('type_id') == 3)
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\FileUpload::make('path')
                                    ->disk('s3')
                                    ->acceptedFileTypes(['video/*'])
                                    ->directory('form-attachments'),
                            ])
                            ->addable(false)
                            ->deletable(false),
                        Repeater::make('versions')
                            ->visible(fn($record, $get) => $get('type_id') == 4)
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\FileUpload::make('path')
                                    ->disk('s3')
                                    ->directory('form-attachments'),
                            ])
                            ->addable(false)
                            ->deletable(false),
                    ]) : Forms\Components\Hidden::make(""),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->multiple()
                    ->relationship('category', 'name')
                    ->preload()
                    ->label('Categories'),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\CreateAction::make('open')
                    ->visible(fn($record) => $record->versions()->where('status', 'active')->count() === 1)
                    ->label('Public URL')
                    ->url(function ($record) {
                        return \env('PUBLIC_URL') . $record->slug . ($record->extension ? '.' . $record->extension : '');
                    }, true),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\VersionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFiles::route('/'),
            'create' => Pages\CreateFile::route('/create'),
            'edit' => Pages\EditFile::route('/{record}/edit'),
        ];
    }
}
