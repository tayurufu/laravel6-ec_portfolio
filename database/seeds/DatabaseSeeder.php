<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $this->call([

            StockLocationTableSeeder::class,
            ItemTypeTableSeeder::class,
            TagTableSeeder::class,
            ItemTableSeeder::class,
            ItemTagTableSeeder::class,
            ItemPhotoTableSeeder::class,
            StockTableSeeder::class,

        ]);

        if (Config::get('app.env') === 'local') {

            // php artisan cache:forget spatie.permission.cache
            Artisan::call('cache:forget spatie.permission.cache');

            $this->call([
                PermissionTableSeeder::class,
                RoleTableSeeder::class,
                AclUserTableSeeder::class,
                RoleHasPermissionTableSeeder::class,
                CustomerTableSeeder::class,
                OrderTableSeeder::class,
            ]);
        }
    }
}
