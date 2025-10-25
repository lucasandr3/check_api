<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'order' => $this->resource->order,
            'secao' => $this->resource->secao,
            'label' => $this->resource->label,
            'icon' => $this->resource->icone,
            'url' => $this->resource->url,
            'identificador' => $this->resource->identificador,
            'submenus' => $this->whenLoaded('submenus', function () {
                return MenuResource::collection($this->submenus);
            }, []),
            'rotas_ativas' => $this->resource->rotas_ativas,
        ];
    }
}
