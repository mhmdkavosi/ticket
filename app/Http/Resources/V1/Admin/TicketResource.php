<?php

namespace App\Http\Resources\V1\Admin;

use App\Http\Resources\V1\Admin\CategoryResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'message' => $this->message,
            'state' => $this->state,
            'department' => $this->department,
            'category' => new CategoryResource($this->category)
        ];
    }
}
