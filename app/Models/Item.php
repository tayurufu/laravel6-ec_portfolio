<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    //

    protected $guarded = ['create_at', 'update_at',];

    protected $hidden = [
        self::CREATED_AT, self::UPDATED_AT,
    ];

    protected $appends = [
        'thumbnail',
    ];

    public function getThumbnailAttribute(){
        $firstPhoto = $this->photos()->orderBy('order', 'asc')->first();
        if($firstPhoto){
            return $firstPhoto->url;
        }
        return "/no_image.png";
    }

    public function itemType(){
        // belongsTo(結合クラス, 結合先テーブルのキー, 自テーブルのキー);
        return $this->belongsTo(ItemType::class, 'type_id', 'id');
    }

    public function stock(){
        // hasOne(結合クラス, 結合先テーブルのキー, 自テーブルのキー);
        return $this->hasOne(Stock::class, 'item_id', 'id');
    }

    public function tags(){
        // belongsToMany(結合クラス, 中間テーブル名, 中間テーブル内のキー, 中間テーブル内の結合先のキー, 自テーブルのキー, 結合先のキー);
        return $this->belongsToMany(Tag::class, 'item_tag', 'item_id', 'tag_id', 'id', 'id');
    }

    public function photos(){
        // hasMany(結合クラス, 結合先テーブルのキー, 自テーブルのキー);
        return $this->hasMany(ItemPhoto::class, 'item_id', 'id');
    }



    public function addWhereThis($item, $key, $value){
        return $item->where($key, $value);
    }
    public static function addWhere($key, $value, $like = false){
        if($like){
            return Item::where($key, 'LIKE', $value);
        } else {
            return Item::where($key, $value);
        }

    }
}
