<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ResponseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

     public $data;
     public $status;
 
     public function __construct($data, $status)
     {
 
         $this->data = $data;
         $this->status = $status;
     }
 
    public function toArray($request)
    {
        return [

            'data' => $this->data,
            'status' => $this->status
        ];
    }
}
