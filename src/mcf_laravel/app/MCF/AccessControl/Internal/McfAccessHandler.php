<?php

declare (strict_types = 1);

namespace App\MCF\AccessControl\Internal;

use App\MCF\AccessControl\Data\RoleRouteAccess;
use App\MCF\AccessControl\Enum\GuardType;
use App\MCF\AccessControl\McfAccess;
use App\MCF\AccessControl\Registry\McfRouteDataRegistry;
use App\MCF\Authentication\McfAuth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class McfAccessHandler
{
    public function handle(Request $request): ?Response
    {
        $routeName = $request->route()?->getName();

        if ($routeName === null) {
            return null;
        }

        $routeAccess = McfRouteDataRegistry::get($routeName);

        if ($routeAccess === null) {
            return null;
        }

        return match ($routeAccess->guard) {
            GuardType::ANY   => null,

            GuardType::GUEST => $this->handleGuest(),

            GuardType::AUTH  => $this->handleAuth(),

            GuardType::ROLE  => $this->handleRole($routeAccess),
        };
    }

    private function handleGuest(): ?Response
    {
        if (! McfAuth::check()) {
            return null;
        }

        return redirect('/');
    }

    private function handleAuth(): ?Response
    {
        if (McfAuth::check()) {
            return null;
        }

        return redirect()->route(
            McfAccess::resolveLoginRouteName(),
        );
    }

    private function handleRole(RoleRouteAccess $routeAccess): ?Response
    {
        if (! McfAuth::check()) {
            return redirect()->route(
                McfAccess::resolveLoginRouteName(),
            );
        }

        $user = McfAuth::user();

        if ($user === null) {
            return redirect()->route(
                McfAccess::resolveLoginRouteName(),
            );
        }

        $userRole = McfAccess::resolveRole($user);

        foreach ($routeAccess->roles as $roleData) {
            if ($userRole === $roleData->role) {
                return null;
            }
        }

        abort(401);
    }
}
