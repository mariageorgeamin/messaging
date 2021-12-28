<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ThreadResource extends JsonResource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);
        $return['id'] = $data['id'];
        $return['subject'] = $data['subject'];
        $return['messages'] = MessageResource::collection($this->messages);
        $return['participant'] = ParticapntResource::collection($this->participants);


        return $return;
    }
}
