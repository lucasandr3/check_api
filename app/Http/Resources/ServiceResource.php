<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'description' => $this->description,
            'status' => $this->status,
            'estimated_cost' => $this->estimated_cost,
            'final_cost' => $this->final_cost,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'observations' => $this->observations,
            'vehicle' => [
                'id' => $this->vehicle->id,
                'brand' => $this->vehicle->brand,
                'model' => $this->vehicle->model,
                'year' => $this->vehicle->year,
                'plate' => $this->vehicle->plate,
                'client' => [
                    'id' => $this->vehicle->client->id,
                    'name' => $this->vehicle->client->name,
                    'phone' => $this->vehicle->client->phone,
                ],
            ],
            'user' => $this->when($this->user, [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'office' => [
                'id' => $this->office->id,
                'name' => $this->office->name,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
