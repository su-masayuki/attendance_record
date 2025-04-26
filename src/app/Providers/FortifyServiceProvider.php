<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Requests\CustomLoginRequest;
use App\Http\Requests\CustomAdminLoginRequest;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Requests\LoginRequest;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Fortify::ignoreRoutes();

        config(['fortify.features' => [
            Features::registration(),
            Features::emailVerification(),
        ]]);

        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
            return view('auth.register');
        });

        Fortify::loginView(function () {
            return request()->is('admin/*') ? view('auth.admin_login') : view('auth.login');
        });

        Fortify::authenticateUsing(function (LoginRequest $request) {
            if ($request->is('admin/*')) {
                $adminRequest = app(CustomAdminLoginRequest::class);
                $validator = Validator::make($request->all(), $adminRequest->rules(), $adminRequest->messages());
            } else {
                $userRequest = app(CustomLoginRequest::class);
                $validator = Validator::make($request->all(), $userRequest->rules(), $userRequest->messages());
            }

            if ($validator->fails()) {
                throw new \Illuminate\Validation\ValidationException($validator);
            }

            if ($request->is('admin/*')) {
                $admin = \App\Models\Admin::where('email', $request->email)->first();
                if ($admin && Hash::check($request->password, $admin->password)) {
                    Auth::guard('admin')->login($admin);
                    return $admin;
                }
            } else {
                $user = \App\Models\User::where('email', $request->email)->first();
                if ($user && Hash::check($request->password, $user->password)) {
                    Auth::guard('web')->login($user);
                    return $user;
                }
            }

            throw \Illuminate\Validation\ValidationException::withMessages([
                Fortify::username() => ['ログイン情報が登録されていません'],
            ]);
        });

        app('router')->get('/home', function () {
            $user = Auth::user();
            if (!$user) {
                return redirect('/login');
            }
            return redirect($user->is_admin ? '/admin/attendance/list' : '/attendance');
        })->name('home');

        app('router')->post('/logout', function (Request $request) {
            $user = Auth::user();

            if ($user && $user->is_admin) {
                Auth::guard('admin')->logout();
                $redirect = '/admin/login';
            } else {
                Auth::guard('web')->logout();
                $redirect = '/login';
            }

            $request->session()->flush();
            $request->session()->regenerate();

            return redirect($redirect);
        })->middleware('web')->name('logout');

        App::bind(LoginRequest::class, CustomLoginRequest::class);
    }
}
