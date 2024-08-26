<?php

namespace App\Filament\Exports\Blog;

use App\Models\Blog\Author;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AuthorExporter extends Exporter
{
    protected static ?string $model = Author::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('name'),
            ExportColumn::make('email')
                ->label('Email Address'),
            ExportColumn::make('github_handle')
                ->label('Github'),
            ExportColumn::make('twitter_handle')
                ->label('Twitter'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updates_at'),
        ];
    }

     public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your author export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
