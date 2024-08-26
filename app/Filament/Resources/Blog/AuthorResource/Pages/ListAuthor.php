<?php

namespace App\Filament\Resources\Blog\AuthorCategory\Pages;

use Filament\Actions;
use pxlrbt\FilamentExcel\Columns\Column;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use App\Filament\Resources\Blog\AuthorResource;
use App\Filament\Resources\Blog\CategoryResource;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;

class ListAuthor extends ListRecords
{
    protected static string $resource = AuthorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make() 
                ->exports([
                    ExcelExport::make()
                        ->fromTable()
                        ->withFilename(fn ($resource) => $resource::getModelLabel() . '-' . date('Y-m-d'))
                        ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
                        ->withColumns([
                            Column::make('updated_at'),
                        ])
                ]), 
            Actions\CreateAction::make(),
        ];
    }
}
