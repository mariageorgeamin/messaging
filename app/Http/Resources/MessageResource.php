<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);
        $return['id'] = $data['id'];
        $return['message'] = $data['body'];
        $return['user'] = $this->user->name;
        $return['date'] = Carbon::createFromDate($data['updated_at'])->toDateTimeString();

        return $return;
    }
}
