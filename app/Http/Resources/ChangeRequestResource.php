<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ChangeRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'data' => json_decode($this->data),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'user' => ($this->user)? new UserResource($this->user) : null,
            'requested_by' => new AdminResource($this->admin),
        ];
    }
}
