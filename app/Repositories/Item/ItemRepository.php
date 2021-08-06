<?php


namespace App\Repositories\Item;

use App\Models\Item;
use App\Models\ItemPhoto;

interface ItemRepository
{
    public function findItem(int $id): Item;

    public function findItemByItemId(string $itemId): ?Item;

    public function findItems(): array;

    public function deleteItem(int $id): bool;

    public function updateItem(Item $item): bool;

    public function insertItem(Item $item): int;

    public function mergeItem(Item $item): Item;

    public function addTags(Item $item, array $array): bool;

    public function delTags(Item $item, array $array): bool;

    public function syncTags(Item $item, array $array): bool;

    public function addTagsById(int $id, array $array): bool;

    public function delTagsById(int $id, array $array): bool;

    public function syncTagsById(int $id, array $array): bool;

    public function addPhoto(Item $item, ItemPhoto $photo): int;

    public function delPhotos(Item $item, array $keys): int;

    public function getItemPhotoNames($item);

}
