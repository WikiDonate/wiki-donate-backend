<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'content' => $this->content,
            'versionNumber' => $this->version_number,
            'createdAt' => $this->created_at->format('d F, Y H:i'),
            'section' => [
                'uuid' => $this->section->uuid,
                'title' => $this->section->title,
                'content' => $this->section->content,
            ],
            'user' => [
                'uuid' => $this->user->uuid,
                'username' => $this->user->username,
            ],
        ];
    }
}
