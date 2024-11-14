<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            'user_id' => $this->user_id,
            'edit_talk_page' => $this->edit_talk_page,
            'edit_user_page' => $this->edit_user_page,
            'page_review' => $this->page_review,
            'email_from_other' => $this->email_from_other,
            'successful_mention' => $this->successful_mention
        ];
    }
}