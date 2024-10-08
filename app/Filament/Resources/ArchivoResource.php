<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Area;
use Filament\Tables;
use App\Models\Archivo;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Spatie\Permission\Guard;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ArchivoResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerInput;
use App\Filament\Resources\ArchivoResource\RelationManagers;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class ArchivoResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Archivo::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?string $navigationGroup = 'Contentido';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información general')
                ->description('Coloca la información del archivo aquí.')
                ->schema([
                    Forms\Components\TextInput::make('titulo')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('descripcion')
                        ->required()
                        ->maxLength(255),
                ])->columns(2),
                Forms\Components\Select::make('categoria_id')
                    ->relationship(name: 'categoria', titleAttribute: 'nombre')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('area_id')
                    ->relationship(name: 'area', titleAttribute: 'nombre')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('fecha')
                    ->required(),
                FileUpload::make('ruta')
                    ->required()
                    ->preserveFileNames()
                    ->maxSize(204800)
                    ->disk('public')
                    ->directory('uploads')
                    ->visibility('public')
                    // ->downloadable()
                    ->columnSpanFull(),
            ]);
    }

    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titulo')
                    ->label('TITULO')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('DESCRIPCION')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('categoria.nombre')
                    ->label('CATEGORIA')
                    ->sortable(),
                Tables\Columns\TextColumn::make('area.nombre')
                    ->label('AREA')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ruta')
                    ->label('DOCUMENTO')
                    ->sortable()    
                    ->searchable()
                    ->formatStateUsing(fn ($state) => basename($state)),
                Tables\Columns\TextColumn::make('fecha')
                    ->label('FECHA')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('Categorias')
                    ->relationship('categoria', 'nombre')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('download')
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-tray') // Icono para el botón
                    ->url(fn (Archivo $record) => route('documentos.download', $record->id)) // Genera la URL de descarga
                    ->openUrlInNewTab() // (Opcional) Abre la URL en una nueva pestaña
                    ->visible(auth()->user()->can('download_archivo')), 
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArchivos::route('/'),
            'create' => Pages\CreateArchivo::route('/create'),
            'view' => Pages\ViewArchivo::route('/{record}'),
            'edit' => Pages\EditArchivo::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'download',
        ];
    }
}
