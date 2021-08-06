<?php


namespace App\Repositories\ItemType;


use App\Models\ItemType;

class ItemTypeEloquentRepository implements ItemTypeRepository
{

    public function getAll(){
        return ItemType::select(['id', 'type_id', 'name'])->orderby('type_id', 'asc')->get();
    }

    public function getName(){
        return ItemType::select(['name'])->orderby('type_id', 'asc')->get();
    }
}
