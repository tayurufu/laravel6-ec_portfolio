<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ItemPhoto extends Model
{
    //
    protected $guarded = ['create_at', 'update_at',];

    protected $appends = [
        'url',
    ];

    public function getUrlAttribute()
    {
        $path = route('item.photo.get', $this->attributes['filename']);
        return $path;
    }


    /**
     * 保存するファイル名を作成する
     * @param $itemId
     * @param string $extension
     * @return string
     */
    public static function createRandomFileName($itemId, $extension = 'jpg'): string
    {
        return $itemId . '_' . uniqid(rand()) . '.' .$extension;
    }
}
