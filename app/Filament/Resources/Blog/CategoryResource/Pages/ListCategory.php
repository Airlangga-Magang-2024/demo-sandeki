<?php

namespace App\Filament\Resources\Blog\CategoryResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use EightyNine\ExcelImport\ExcelImportAction;
use App\Filament\Resources\Blog\CategoryResource;

class ListCategory extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ExcelImportAction::make()
                ->color('primary')
                // ->mutateBeforeValidationUsing(function(array $data): array{
                //     return $data;
                // })
                // ->validateUsing([
                //     'name' => 'required',
                //     'slug' => 'requires',
                //     'description' => 'min|2'
                // ])
                // ->mutateAfterValidationUsing(function(array $data): array{
                //     // $data['date'] = $data['date']->format('Y-m-d');
                //     return $data;
                // }),
        ];
    }
}
