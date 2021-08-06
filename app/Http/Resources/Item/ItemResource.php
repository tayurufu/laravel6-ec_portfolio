<?php

namespace App\Http\Resources\Item;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ItemResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        //return parent::toArray($request);
        return $this->getData($this);
    }


    private function canEditItem(){
        $canEdit = false;

        $user = Auth::user();

        if($user && $user->can('edit_item')){
            $canEdit = true;
        }

        return $canEdit;
    }

    public function getData($item){


        $addData = [
            'id' => $item->id,
            'itemId' => $item->item_id,
            'price' => $item->price,
            'name' => $item->name,
            'typeId' => $item->type_id,
            'description' => $item->description,
            'thumbnail' => $item->thumbnail,
            'itemType' => $item->itemType->name,
            'tags' => $item->tags->map(function($t){return ['tagId' => $t->id, 'tagName' => $t->name] ;}),
            'photos' => $item->photos()->orderBy("order")->get()->map(function($p){return ['photoId' => $p->id, 'filename' => $p->filename, 'url'=> $p->url] ;}),
            'detailItemUrl' => route('item.detail', $item->id),
            'stock' => ['qty' => $item->stock ? $item->stock->stock : 0, 'location' => $item->stock ? $item->stock->location_id : ""]
        ];

        if($this->canEditItem()){
            $addData = array_merge($addData, ['editItemUrl' =>  route('item.edit', $item->id)]);
        }

         return $addData;
    }



}


