<?php

namespace App\Http\Resources\Item;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemPaginateResource extends ItemResource
{
    public function toArray($request) {
        return $this->getCollection()->transform([$this, 'map']);
    }

    public function map($work) {
        return parent::getData($work);
    }
}
