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
        Log::info('Usuario: ' . $user->name . ' - Permiso view_management_archivo: ' . ($user->can('management_area') ? 'Sí' : 'No'));
    
        $areas = [
            'scale_area' => 'Balanzas',
            'assistant_area' => 'Asistente de gerencia',
            'management_area' => 'Gerencia',
            'treasury_area' => 'Tesoreria',
            'accounting_area' => 'Contabilidad',
            'resources_area' => 'Recursos humanos',
            'maintenance_area' => 'Mantenimiento',
            'sanitation_area' => 'Calidad y Saneamiento',
            'hotel_area' => 'Hotel',
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
