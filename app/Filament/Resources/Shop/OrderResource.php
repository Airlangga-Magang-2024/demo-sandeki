<?php

namespace App\Filament\Resources\Shop;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Enums\OrderStatus;
use App\Models\Shop\Order;
use Filament\Tables\Table;
use Squire\Models\Currency;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use App\Filament\Clusters\Products;
use Filament\Forms\Components\Group;
use App\Forms\Components\AddressForm;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ToggleButtons;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Shop\OrderResource\Pages;
use App\Filament\Clusters\Products\Resources\ProductsResource;
use App\Filament\Resources\Shop\OrderResource\RelationManagers;
use App\Filament\Resources\Shop\OrderResource\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\Shop\OrderResource\Widgets\OrderStats;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $slug = 'shop/orders';

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema(static::getDetailsFormSchema())
                            ->columns(2),

                        Section::make('Order items')
                            ->headerActions([
                                Action::make('reset')
                                    ->modalHeading('Are you sure?')
                                    ->modalDescription('All existing items will be removed from the order.')
                                    ->requiresConfirmation()
                                    ->color('danger')
                                    ->action(fn (Set $set) => $set('items', [])),
                            ])
                            ->schema([
                                static::getItemsRepeater(),
                            ]),
                    ])
                    ->columnSpan(['lg' => fn (?Order $record) => $record === null ? 3 : 2]),

                Section::make()
                    ->schema([
                        Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn (Order $record): ?string => $record->created_at?->diffForHumans()),

                        Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(fn (Order $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?Order $record) => $record === null),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('currency')
                    ->getStateUsing(fn ($record): ?string => Currency::find($record->currency)?->name ?? null)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->searchable()
                    ->sortable()
                    // ->formatStateUsing(fn($state) => number_format($state, 2, ',', '.'))
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                        // ->format(fn($value) => number_format($value, 2, ',', '.')),
                    ]),

                Tables\Columns\TextColumn::make('shipping_price')
                    ->label('Shipping Cost')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    // ->formatStateUsing(fn($state) => number_format($state, 2, ',', '.'))
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make('total_price')
                            ->money('IDR')
                        // ->format(fn($value) => number_format($value, 2, ',', '.')),
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->date()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->placeholder(fn ($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('created_until')
                            ->placeholder(fn ($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Order from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Order until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('created_at')
                    ->label('Order Date')
                    ->date()
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PaymentsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            OrderStats::class,
        ];   
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['number', 'customer.nama'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Customer' => optional($record -> customer)->name,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['customer', 'items']);
    }

    public static function getNavigationBadge(): ?string
    {
        $modelClass = static::$model;

        return (string) $modelClass::where('status' ,'new')->count();
    }

    public static function getDetailsFormSchema():array {
        return [
            TextInput::make('number')
                ->default('OR-' . random_int(100000, 999999))
                ->disabled()
                ->dehydrated()
                ->required()
                ->maxLength(32)
                ->unique(Order::class, 'number', ignoreRecord:true),

            Select::make('shop_customer_id')
                ->relationship('customer','name')
                ->searchable()
                ->required()
                ->createOptionForm([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('Email Address')
                        ->required()
                        ->email()
                        ->maxLength(255)
                        ->unique(),

                    TextInput::make('phone')
                        ->maxLength(255),

                    Select::make('gender')
                        ->placeholder('Select Gender')
                        ->options([
                            'male' => 'Male',
                            'female' => 'Female',
                        ])
                        ->required()
                        ->native(false),
                ])
                ->createOptionAction(function (Action $action){
                    return $action
                        ->modalHeading('Create Customer')
                        ->modalSubmitActionLabel('Create Customer')
                        ->modalWidth('lg');
                }),
            ToggleButtons::make('status')
                ->inline()
                ->options(OrderStatus::class)
                ->required(),

            Select::make('currency')
                ->searchable()
                ->getSearchResultsUsing(fn (string $query) => Currency::where('name','like',"%{$query}%")->pluck('name','id'))
                ->getOptionLabelsUsing(fn ($value): ?string => Currency::firstWhere('id', $value)?->getAttribute('name'))
                ->required(),
            
            AddressForm::make('address')
                ->columnSpan('full'),

            MarkdownEditor::make('notes')
                ->columnSpan('full'),
        ];
    }

    public static function getItemsRepeater(): Repeater{
        return Repeater::make('items')
        ->relationship()
        ->schema([
            Forms\Components\Select::make('shop_product_id')
                ->label('Product')
                ->options(Product::query()->pluck('name', 'id'))
                ->required()
                ->reactive()
                ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('unit_price', Product::find($state)?->price ?? 0))
                ->distinct()
                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                ->columnSpan([
                    'md' => 5,
                ])
                ->searchable(),

            Forms\Components\TextInput::make('qty')
                ->label('Quantity')
                ->numeric()
                ->default(1)
                ->columnSpan([
                    'md' => 2,
                ])
                ->required(),

            Forms\Components\TextInput::make('unit_price')
                ->label('Unit Price')
                ->disabled()
                ->dehydrated()
                ->numeric()
                ->required()
                ->columnSpan([
                    'md' => 3,
                ]),
        ])
        ->extraItemActions([
            Action::make('openProduct')
                ->tooltip('Open product')
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->url(function (array $arguments, Repeater $component): ?string {
                    $itemData = $component->getRawItemState($arguments['item']);

                    $product = Product::find($itemData['shop_product_id']);

                    if (! $product) {
                        return null;
                    }

                    return ProductsResource::getUrl('edit', ['record' => $product]);
                }, shouldOpenInNewTab: true)
                ->hidden(fn (array $arguments, Repeater $component): bool => blank($component->getRawItemState($arguments['item'])['shop_product_id'])),
        ])
        ->orderColumn('sort')
        ->defaultItems(1)
        ->hiddenLabel()
        ->columns([
            'md' => 10,
        ])
        ->required();
    }

    public static function updateTotals(Get $get, Set $set): void {
        $selectedProducts = collect($get('invoiceProducts'))->filter(fn($item) => !empty($item['shop_product_id']) && !empty($item['quantity']));

        $prices = Product::find($selectedProducts->pluck('shop_product_id'))->pluck('price', 'id');

        $total_price = $selectedProducts->reduce(function ($total_price, $product) use($prices){
            return $total_price + ($prices[$product['shop_product_id']] * $product['quantity']);
        }, 0);

        // $set ('total_price', number_format($total_price, 2 , '.', ''));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}

