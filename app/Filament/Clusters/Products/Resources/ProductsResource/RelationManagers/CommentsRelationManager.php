<?php

namespace App\Filament\Clusters\Products\Resources\ProductsResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required(),

                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->required(),

                Toggle::make('is_visible')
                    ->label('Approved for public')
                    ->default(true),

                MarkdownEditor::make('content')
                    ->required()
                    ->label('Content'),
            ]);
    }
    
    public function infolist(Infolist $infolist): Infolist{
        return $infolist
            ->columns(1)
            ->schema([
                TextEntry::make('title'),
                TextEntry::make('customer.name'),
                TextEntry::make('is_visible')
                    ->label('Visibility'),
                TextEntry::make('content')
                    ->markdown(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_visible')
                    ->label('Visibility')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->after(function ($record) {
                        $user = auth()->user();

                        Notification::make()
                            ->title('New Comment')
                            ->icon('heroicon-o-chat-bubble-bottom-center-text')
                            ->body("**{$record->customer->name} commented on product ({$record->commentable->name}).**")
                            ->sendToDatabase($user);
                    })
            ])
            ->actions([
                ViewAction::make(),
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
