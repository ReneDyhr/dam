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
use Filament\Tables\Table;

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
                                    ->acceptedFileTypes(['application/pdf'])
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
                //
            ])
            ->actions([
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