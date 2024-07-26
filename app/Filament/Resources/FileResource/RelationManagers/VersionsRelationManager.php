<?php

namespace App\Filament\Resources\FileResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'versions';

    public function form(Form $form): Form
    {
        $type = strtolower($this->ownerRecord->type->name);
        switch ($type) {
            case 'pdf':
                $upload = Forms\Components\FileUpload::make('path')
                    ->directory('form-attachments')
                    ->acceptedFileTypes(['application/pdf'])
                    ->visibility('private');
                break;

            default:
                $upload = Forms\Components\FileUpload::make('path')
                    ->image()
                    ->directory('form-attachments')
                    ->visibility('private')
                    ->imageEditor();
                break;
        }
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                $upload
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
