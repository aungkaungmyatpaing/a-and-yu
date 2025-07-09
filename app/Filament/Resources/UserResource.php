<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $modelLabel = 'Customer';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Customer')
                    ->required()
                    ->maxLength(255),
                TextInput::make('school_name')
                    ->nullable()
                    ->hint('Optional')
                    ->maxLength(255),
                TextInput::make('email')
                    ->nullable()
                    ->email()
                    ->hint('Optional')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->nullable()
                    ->hint('Optional')
                    ->unique(ignoreRecord: true)
                    ->rule(function () {
                        return function ($attribute, $value, $fail) {
                            if ((str_starts_with($value, '095') || str_starts_with($value, '094')) && strlen($value) !== 9) {
                                $fail($attribute . ' must be 9 characters long if it starts with "095" or "094".');
                            } elseif (!str_starts_with($value, '095') && !str_starts_with($value, '094') &&  strlen($value) !== 11) {
                                $fail($attribute . ' must be 11 characters long if it does not start with "095" or "094".');
                            }
                        };
                    }),
                TextInput::make('address')
                    ->nullable()
                    ->hint('Optional'),
                SpatieMediaLibraryFileUpload::make('image')
                        ->collection('image')
                        ->conversion('thumb')
                        ->nullable()
                        ->columnSpanFull(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('Image')
                    ->collection('image')
                    ->defaultImageUrl(asset('assets/images/default.png'))
                    ->square()
                    ->limit(1)
                    ->size(80)
                    ->toggleable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('school_name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->toggleable(),
                TextColumn::make('phone')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
