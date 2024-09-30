<?php

namespace App\Filament\Resources\FileResource\Pages;

use App\Filament\Resources\FileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFile extends EditRecord
{
    protected static string $resource = FileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('Download')
                ->visible(fn($record) => $record->versions()->where('status', 'active')->count() === 1)
                ->label('Public URL')
                ->url(function ($record) {
                    return \env('PUBLIC_URL') . $record->slug;
                }, true),
        ];
    }
}
