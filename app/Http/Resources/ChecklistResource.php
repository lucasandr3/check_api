<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChecklistResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'office_id' => $this->office_id,
            'service_id' => $this->service_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'items' => $this->items,
            'observations' => $this->observations,
            'service' => [
                'id' => $this->service->id,
                'type' => $this->service->type,
                'description' => $this->service->description,
                'status' => $this->service->status,
                'vehicle' => [
                    'id' => $this->service->vehicle->id,
                    'brand' => $this->service->vehicle->brand,
                    'model' => $this->service->vehicle->model,
                    'plate' => $this->service->vehicle->plate,
                    'client' => [
                        'id' => $this->service->vehicle->client->id,
                        'name' => $this->service->vehicle->client->name,
                        'phone' => $this->service->vehicle->client->phone,
                    ],
                ],
            ],
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'office' => [
                'id' => $this->office->id,
                'name' => $this->office->name,
            ],
            'photos' => $this->when($this->photos, $this->photos->map(function ($photo) {
                return [
                    'id' => $photo->id,
                    'filename' => $photo->filename,
                    'url' => asset('storage/' . $photo->path),
                    'description' => $photo->description,
                ];
            })),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
