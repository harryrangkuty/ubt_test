<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil data dari API
        $response = Http::get('https://sidara.ubtsu.ac.id/api/units');

        if ($response->successful()) {
            $data = $response->json('data');

            foreach ($data as $item) {
                DB::table('units')->updateOrInsert(
                    ['id' => $item['id']], // kalau ID sama, update
                    [
                        'parent'       => $item['parent'],
                        'code'         => $item['code'],
                        'name'         => $item['name'],
                        'type'         => $item['type'],
                        'nim_label'    => $item['nim_label'],
                        'level'        => $item['level'],
                        'is_active'    => $item['is_active'],
                        'is_satker'    => $item['is_satker'],
                        'is_fakultas'  => $item['is_fakultas'],
                        'is_prodi'     => $item['is_prodi'],
                        'created_at'   => $item['created_at'] ? Carbon::parse($item['created_at']) : now(),
                        'updated_at'   => $item['updated_at'] ? Carbon::parse($item['updated_at']) : now(),
                        'deleted_at'   => $item['deleted_at'] ? Carbon::parse($item['deleted_at']) : null,
                    ]
                );
            }
        }
    }
}
