<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        $editProfileUrl = \Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage::getUrl();
        $editprofilePath = ltrim(parse_url($editProfileUrl, PHP_URL_PATH), '/');

        if (
            $user
            && $user->must_change_password
            && ! str_starts_with($request->path(), $editprofilePath)
            && $request->path() !== 'pengelola-kegiatan/logout'
        ) {
            return redirect($editProfileUrl);
        }
        return $next($request);
    }
}
