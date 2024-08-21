<?php

namespace App\Filament\Resources\Shop;

use Filament\Forms;
use Filament\Tables;
use App\Models\Address;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Shop\Customer;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\CustomerResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CustomerResource\RelationManagers;
// use Parfaitementweb\FilamentCountryField\Forms\Components\Country;
use App\Filament\Resources\Shop\CustomerResource\Pages\EditCustomer;
use App\Filament\Resources\Shop\CustomerResource\Pages\ListCustomers;
use App\Filament\Resources\Shop\CustomerResource\Pages\CreateCustomer;
use Parfaitementweb\FilamentCountryField\Tables\Columns\CountryColumn;
use App\Filament\Resources\Shop\CustomerResource\RelationManagers\AddressesRelationManager;
use Squire\Models\Country;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $slug = 'shop/customers';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 2;

    protected static string $relationship = 'addresses';

    protected static ?string $recordTitleAttribute = 'full_address';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                ->schema([
                    TextInput::make('name')
                        ->maxLength(255)
                        ->required(),

                    TextInput::make('email')
                        ->label('Email address')
                        ->required()
                        ->email()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    TextInput::make('phone')
                        ->maxLength(255),

                    DatePicker::make('birthday')
                        ->maxDate('today'),
                ])
                ->columns(2)
                ->columnSpan(['lg' => fn (?Customer $record) => $record === null ? 3 : 2]),

            Section::make()
                ->schema([
                    Placeholder::make('created_at')
                        ->label('Created at')
                        ->content(fn (Customer $record): ?string => $record->created_at?->diffForHumans()),

                    Placeholder::make('updated_at')
                        ->label('Last modified at')
                        ->content(fn (Customer $record): ?string => $record->updated_at?->diffForHumans()),
                ])
                ->columnSpan(['lg' => 1])
                ->hidden(fn (?Customer $record) => $record === null),
        ])
        ->columns(3);
            
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email address')
                    // ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),
                // Tables\Columns\TextColumn::make('country'),
                // CountryColumn::make('country')
                // ->getStateUsing(f/n ($record): ?string => Address::find($record->addresses->first()?->country)?->name ?? null),
                // ->formatStateUsing(fn ($state): ?string => Country::find($state)?->name ?? null),
                

                // TextColumn::make('country'),
                TextColumn::make('country')
                    ->getStateUsing(fn ($record): ?string =>Country::find($record->addresses->first()?->country)?->name ?? null),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('addresses')->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getRelations(): array
    {
        return [
            AddressesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

}
