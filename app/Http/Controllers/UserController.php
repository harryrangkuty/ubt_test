<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Unirest\Request as Unirest;

class UserController extends Controller
{
    public function __invoke(Request $request)
    {
        if ($request->req == 'open') {
            $data = User::with(['unit', 'roles'])->findOrFail($request->id);

            $title = $data->name;
            $vue = "<user-page-detail :title='" . json_encode($title) . "' :data='" . json_encode($data) . "'/>";
        } else {
            $constant = [
                'ROLE' => Role::select('id', 'name', 'display_name')->get(),
                'UNIT' => Unit::all()
            ];

            $title = 'Manajemen Pengguna';
            $vue = "<user-page :title='" . json_encode($title) . "' :constant='" . json_encode($constant) . "' />";
        }
        return response()->view('layouts.antd', compact('vue', 'title'));
    }

    public function read(Request $request)
    {
        if ($request->req == 'table') {
            $data = User::with('unit:id,name')
                ->with(['roles' => function ($q) use ($request) {
                    if ($request->roles) {
                        $q->whereIn('id', $request->roles);
                    }
                }])
                ->withTrashed()
                ->where(function ($q) use ($request) {
                    if ($request->search)
                        $q->where('users.name', 'like', "%{$request->search}%")
                            ->orWhere('email', 'like', "%$request->search%");
                })
                ->where(function ($q) use ($request) {
                    if ($request->status == 'aktif') {
                        $q->whereNull('deleted_at');
                    } else {
                        $q->whereNotNull('deleted_at');
                    }
                })
                ->when($request->roles, function ($q) use ($request) {
                    $q->whereHas('roles', function ($query) use ($request) {
                        $query->whereIn('id', $request->roles);
                    });
                })
                ->paginate($this->per_page());

            return response()->json(['models' => $data]);
        } elseif ($request->req == 'api') {

            $search = urldecode($request->name);

            $token = $_COOKIE['si-dara-token'] ?? null;

            if (!$token) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $headers = [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ];

            $response = Unirest::get("https://sidara.ubtsu.ac.id/api/users/search?query={$search}", $headers);

            // UNTUK SELECT GROUP
            $grouped = collect(data_get($response, 'body.data'))->groupBy('unit_name');
            return response()->json($grouped);
        } elseif ($request->req == 'sync') {

            $token = $_COOKIE['si-dara-token'] ?? null;

            if (!$token) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $response = Http::withToken($token)
                ->get("https://sidara.ubtsu.ac.id/api/users");

            if (!$response->successful()) {
                return response()->json(['success' => false, 'message' => 'Gagal ambil data API'], 500);
            }

            $apiUsers = $response->json('data');

            $localSsoIds = DB::table('users')->pluck('sso_id')->toArray();

            foreach ($apiUsers as $u) {
                // hanya update jika sso_id ada di DB
                if (in_array($u['id'], $localSsoIds)) {
                    DB::table('users')->where('sso_id', $u['id'])->update([
                        'name'       => $u['name'],
                        'email'      => $u['email'],
                        'unit_id'    => $u['unit_id'] ?? null,
                    ]);
                }
            }

            return response()->json(['success' => true, 'message' => 'Sinkronisasi selesai']);
        }
    }

    public function write(Request $request)
    {
        if ($request->req == 'write') {
            $this->validate(
                $request,
                [
                    'sso_id' => 'required',
                    'roles' => 'required',
                    'name' => 'required',
                    // 'email' => 'required',
                    'unit_id' => 'required',
                ],
                [
                    'sso_id.required' => 'Pilih pengguna dari daftar pegawai.',
                    'roles.required' => 'Wajib pilih Role User',
                ],
            );

            DB::transaction(function () use ($request) {
                $data = User::find($request->id) ?? new User();

                $data->sso_id = $request->sso_id;
                $data->name = $request->name;
                $data->email = $request->email;
                $data->photo = $request->photo;
                $data->unit_id = $request->unit_id;
                $data->save();

                $data->syncRoles($request->roles);
            });

            return response()->json(true);
        } elseif ($request->req == 'delete') {
            $data = User::find($request->id);
            $data->delete();
            return response()->json($data);
        } elseif ($request->req == 'restore') {
            $data = User::onlyTrashed()->where('id', $request->id);
            $data->restore();
            return response()->json($data);
        }
    }
}
