<?php

use Illuminate\Support\Str;

if (!function_exists('check_user_permissions')) {

    function check_user_permissions($user, $path, $exclude = [], $request = null)
    {
        list($path) = explode('/read', $path);

        if ($user && $user?->activeRole?->hasPermission('sudo')) {
            return true;
        }

        if ($user && $user?->activeRole?->hasPermission($path)) {
            return true;
        }

        if (Str::startsWith($path, $exclude)) {
            return true;
        }

        return false;
    }
}

if (!function_exists('menu_list')) {
    function menu_list($user = null)
    {
        $menu = [
            ['key' => 'post', 'icon' => 'mdi:file-document-edit-outline', 'title' => 'Manajemen Postingan', 'url' => 'post'],
            ['key' => 'my_post', 'icon' => 'bi:person', 'title' => 'Postingan Saya', 'url' => 'my_post'],
            ['key' => null, 'title' => 'Administrator', "icon" => null, "url" => null],
            ['key' => 'user', 'icon' => 'bi:person-gear', 'title' => 'Manajemen User', 'url' => 'user'],
            ['key' => 'unit', 'icon' => 'bi:building-gear', 'title' => 'Manajemen Kategori', 'url' => 'unit'],
            [
                'key' => 'hak-akses',
                'icon' => 'bi:people',
                'title' => 'Hak Akses',
                'url' => '#',
                "submenu" => [
                    ['key' => 'role', 'icon' => 'bi:people', 'title' => 'Manajemen Role', 'url' => 'role'],
                    ['key' => 'permission', 'icon' => 'bi:eye', 'title' => 'Manajemen Permission', 'url' => 'permission'],
                ],
            ],
            [
                'key' => 'report',
                'icon' => 'lsicon:report-outline',
                'title' => 'Laporan',
                'url' => 'report',
                "submenu" => [
                    [
                        "key" => "laporan/neraca",
                        "icon" => "line-md:text-box",
                        "title" => "Laporan Neraca",
                        "url" => "laporan/neraca",
                    ],
                    [
                        "key" => "laporan/kinerja",
                        "icon" => "line-md:text-box",
                        "title" => "Laporan Kinerja",
                        "url" => "laporan/kinerja",
                    ],
                ],
            ],
        ];
        // filter menu berdasarkan permission user
        if ($user) {
            $menu = collect($menu)->map(function ($item) use ($user) {
                // SUBMENU
                if (isset($item['submenu'])) {
                    $item['submenu'] = collect($item['submenu'])->filter(fn($child) => check_user_permissions($user, $child['url']))->values()->all();
                    return count($item['submenu']) ? $item : null;
                }
                // MENU
                if (isset($item['url']) && $item['url']) {
                    return check_user_permissions($user, $item['url']) ? $item : null;
                }

                return $item;
            })->filter()->values();

            // hapus header yg tidak ada item
            $menu = $menu->filter(function ($item, $i) use ($menu) {
                if (is_null($item['key'])) {
                    // kalau setelahnya ga ada item non-header, buang
                    $next = $menu->slice($i + 1)->firstWhere('key', '!=', null);
                    return $next !== null;
                }
                return true;
            })->values()->all();
        }

        return $menu;
    }
}

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {

        if (isset($value)) {
            return $value;
        }

        if ($default)
            return $default;

        return config('setting.' . $key);
    }
}

if (!function_exists('stringToArray')) {
    function stringToArray($string)
    {
        // Remove whole spaces
        $string = str_replace(' ', '', $string);

        // Split string by comma (,)
        $array_by_comma = explode(',', $string);

        // Set $array as the return array
        $array = $array_by_comma;

        // Each elements in array $array
        foreach ($array as $key => $value) {

            // Check if contains minus (-)
            if (str_contains($value, '-')) {

                // Remove elements by $value from array $array
                $array = array_diff($array, [$value]);

                // Split string from $value by minus (-)
                $array_by_minus = explode('-', $value);

                // If first element greater than second element then return
                if ($array_by_minus[0] > $array_by_minus[1]) {
                    abort(403, "$value (Parameter kedua lebih kecil dari parameter pertama)");
                }

                // Create array with elements from $array_by_minus[0] to $array_by_minus[1]
                $range = range($array_by_minus[0], $array_by_minus[1]);

                // Merge array in $range to $array_by_comma
                $array = array_merge($array, $range);
            }
        }

        // Convert elements in $array from string to integer
        $array = array_map('intval', $array);

        // Return converted string to array
        return $array;
    }
}

if (!function_exists('checkDiff')) {
    function checkDiff($array_id, $response)
    {
        // Array add to collection
        $array_id = collect($array_id);

        // Keys of collection $response become value of ids
        $response_id = $response->keyBy('id')->keys();

        // Elements from $array_id those not inside $response_id
        $diff = $array_id->diff($response_id);

        // If elements from $array_id those not inside $response_id exist
        if ($diff->isNotEmpty()) {

            // Convert collection to array then to string
            $diff = implode(', ', $diff->toArray());
            abort(403, "$diff tidak terdaftar");
        } else {

            // Return $response if all ids exist
            return response()->json(response()->json($response));
        }
    }
}

if (!function_exists('idCurrency')) {
    function idCurrency($number)
    {
        if (!is_numeric($number))
            return $number;

        $number = str_replace('.', '#', $number);
        $number = preg_replace('/(\d)(?=(\d{3})+(?!\d))/', '$1.', $number);
        $number = str_replace('#', ',', $number);
        return $number;
    }
}
