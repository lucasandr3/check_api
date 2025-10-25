<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuGroupedResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $agrupado = collect($this->resource)
            ->groupBy('secao')
            ->map(function ($menus, $secao) {
                return [
                    'secao' => $secao,
                    'menus' => $menus
                        ->sortBy('order')
                        ->map(fn($menu) => new MenuResource($menu))
                        ->values(),
                ];
            })->values();

        return $agrupado->toArray();
    }
}
