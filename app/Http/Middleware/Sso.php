<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Http;

class Sso
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        $login = $this->setLogin();

        if (!$login) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            setcookie('si-dara-token', '', time() - 3600, '/', config('setting.domain'));
            return redirect('/');
        } else {
            $user = User::where('sso_id', $login->payload->id)->first();
            if (!$user) {
                if (config('sso.auto_create_user')) {
                    $user = $this->createUserFromSso($login->payload);
                }
            } else {
                if ($user->photo !== $login->payload->photo) {
                    $user->photo = $login->payload->photo;
                    $user->save();
                }
                if ($user->name !== $login->payload->name) {
                    $user->name = $login->payload->name;
                    $user->save();
                }
                Auth::login($user);
            }
            return $next($request);
        }
    }

    public static function setLogin()
    {
        $token = $_COOKIE['si-dara-token'] ?? null;

        if (!$token) return false;

        try {
            $response = Http::withToken($token)
                ->withoutVerifying()
                ->get(config('services.sidara.url') . '/api/me');

            if ($response->successful()) {
                return (object) [
                    'success' => true,
                    'payload' => $response->object()
                ];
            } else {
                setcookie('si-dara-token', '', time() - 3600, '/', config('setting.domain'));
            }
        } catch (\Exception $e) {
            logger('SSO login error: ' . $e->getMessage());
        }

        return false;
    }

    public static function getLoginLink()
    {
        $baseUrl = config('services.sidara.url') . '/sso/login';

        $state = bin2hex(random_bytes(16));
        session(['sso_state' => $state]);

        $params = [
            'redirect_uri' => url('/'),
            'state'        => $state,
        ];

        return $baseUrl . '?' . http_build_query($params);
    }


    public function createUserFromSso($payload)
    {
        $user = new User();
        $user->sso_id = $payload->id;
        $user->name =  $payload->name;
        $user->unit_id = $payload->unit_id ?? 1;
        $user->email = $payload->email ?? '';
        $user->photo  = $payload->photo ?? null;
        $user->save();

        // Untuk Assign Role nya
        $role = Role::where('name', 'Operator')->first();
        if ($role) {
            $user->addRole($role);
            $user->active_role_id = $role->id;
            $user->save();
        }

        return $user;
    }
}
