<?php

namespace App\Filament\Clusters\Products\Resources\BrandResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use Parfaitementweb\FilamentCountryField\Forms\Components\Country;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $recordTitleAttribute = 'full_address';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('street'),
                Forms\Components\TextInput::make('zip'),
                Forms\Components\TextInput::make('city'),
                Forms\Components\TextInput::make('state'),

                Country::make('country')
->exclude(['NL'])
->add(['MA' =>'Mars'])
->map(['GB' => 'UK', 'GF' => 'FR'])

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // ->recordTitleAttribute('full_address')
            ->columns([
                Tables\Columns\TextColumn::make('street'),
                Tables\Columns\TextColumn::make('zip'),
                Tables\Columns\TextColumn::make('city'),
                Tables\Columns\TextColumn::make('country')
                // ->formatStateUsing(fn ($state): ?string => Country::find($state)?->name ?? null),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
