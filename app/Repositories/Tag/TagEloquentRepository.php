<?php


namespace App\Repositories\Tag;


use App\Models\Tag;

class TagEloquentRepository implements TagRepository
{

    public function getAll()
    {
        return Tag::select(['id', 'name'])->orderby('id', 'asc')->get();
    }
}
