<?php

namespace App\Filament\Resources\FileResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
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
                    ->disk('s3')
                    ->acceptedFileTypes(['application/pdf']);
                break;
            case 'videos':
                $upload = Forms\Components\FileUpload::make('path')
                    ->directory('form-attachments')
                    ->disk('s3')
                    ->acceptedFileTypes(['video/*']);
                break;

            default:
                $upload = Forms\Components\FileUpload::make('path')
                    ->image()
                    ->disk('s3')
                    ->directory('form-attachments')
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
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'draft',
                        'success' => 'active',
                        'danger' => 'inactive',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->getStateUsing(fn($record) => $record->created_at->format('d-m/Y H:i'))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->createAnother(false),
            ])
            ->actions([
                CreateAction::make('changeStatus')
                    ->label('Change Status')
                    ->modalHeading('Change Status')
                    ->modalButton('Change')
                    ->disableCreateAnother()
                    ->action(function ($record, $data) {
                        // Update the status of the selected record
                        $record->status = $data['status'];

                        // If the status is 'active', set all other records to 'inactive'
                        if ($data['status'] === 'active') {
                            $record->newQuery()->where('status', 'active')->whereFileId($record->file_id)->update(['status' => 'inactive']);
                        }

                        $record->save();
                    })
                    ->form(function ($record) {
                        return [
                            \Filament\Forms\Components\Select::make('status')
                                ->options([
                                    'draft' => 'Draft',
                                    'active' => 'Active',
                                    'inactive' => 'Inactive',
                                ])
                                ->default($record->status)
                                ->required(),
                        ];
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
