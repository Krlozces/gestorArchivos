<?php

namespace App\Filament\Resources\ArchivoResource\Pages;
use Illuminate\Support\Facades\Log;

use Filament\Actions;
use App\Models\Archivo;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ArchivoResource;
use Illuminate\Database\Eloquent\Builder;


class ListArchivos extends ListRecords
{
    protected static string $resource = ArchivoResource::class;

    protected function getTableQuery(): Builder
    {
        $query = Archivo::query();
        $user = auth()->user();
    
        // Logueamos el permiso de gerencia como ejemplo
        Log::info('Usuario: ' . $user->name . ' - Permiso view_management_archivo: ' . ($user->can('view_management_archivo') ? 'Sí' : 'No'));
    
        $areas = [
            'scale_archivo' => 'Balanzas',
            'assistant_archivo' => 'Asistente de gerencia',
            'management_archivo' => 'Gerencia',
            'treasury_archivo' => 'Tesoreria',
            'accounting_archivo' => 'Contabilidad',
            'resources_archivo' => 'Recursos humanos',
            'maintenance_archivo' => 'Mantenimiento',
            'sanitation_archivo' => 'Calidad y Saneamiento',
            'hotel_archivo' => 'Hotel',
        ];
    
        // Aplicamos los filtros basados en permisos
        $query->where(function (Builder $query) use ($user, $areas) {
            foreach ($areas as $permission => $areaName) {
                if ($user->can($permission)) {
                    $query->orWhereHas('area', function (Builder $query) use ($areaName) {
                        $query->where('nombre', $areaName);
                    });
                }
            }
        });
    
        return $query;
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
