<?php

use App\MCF\Authentication\McfAuth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| MCF Routes
|--------------------------------------------------------------------------
|
| Register your Modular Control Framework routes here.
|
| Set your application's home page by changing the route name below.
|
| Example:
| return redirect()->route('user.profile.index');
|
*/
Route::get('/', function () {

    if (McfAuth::check()) {
        if(McfAuth::user()->role_id ==1){
        return redirect()->route('user.userManagement.index');
        }
        else{
        return redirect()->route('user.profile.index');
        }
    }
    return redirect()->route('user.auth.login');

});


require_once __DIR__.'/Storage/Providers/Laravel/LaravelStorageRoute.php';

require_once __DIR__ . '/Realtime/Internal/RealtimeRoutes.php';

require_once __DIR__ . '/Modules/Shared/Layout/Backend/LayoutRoutes.php';

require_once __DIR__ . '/Modules/User/Auth/Backend/AuthRoutes.php';

require_once __DIR__ . '/Modules/User/Profile/Backend/ProfileRoutes.php';

require_once __DIR__.'/Modules/User/UserManagement/Backend/UserManagementRoutes.php';

require_once __DIR__.'/Modules/Shared/StorageTest/Backend/StorageTestRoutes.php';

require_once __DIR__.'/Modules/Shared/RealtimeTest/Backend/RealtimeTestRoutes.php';
