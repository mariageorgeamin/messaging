<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ParticapntResource extends JsonResource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);
        $return['id'] = $data['id'];
        $return['user'] = $this->user;
        $return['last_read'] = Carbon::createFromDate($data['last_read'])->toDateTimeString();
        $return['is_read'] = $data['last_read'] ? '1' : '0';

        return $return;
    }
}
