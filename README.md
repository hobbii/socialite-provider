# Hobbii Socialite Provider
A login provider for Hobbii with [Laravel Socialite](https://github.com/laravel/socialite)

```shell
composer require hobbii/socialite-provider
```

## Installation
Add the following environment variables:
```env
HOBBII_LOGIN_SERVICE=
HOBBII_CLIENT_ID=
HOBBII_CLIENT_SECRET=
```

## Usage
Use the `hobbii`-driver with socialite:

````php
<?php

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('hobbii')->redirect();
    }
    
    public function callback(Request $request): RedirectResponse
    {
        $hobbiiUser = Socialite::driver('hobbii')->user();
        
        $user = User::updateOrCreate([
            'email' => $hobbiiUser->getEmail(),
        ], [
            'first_name' => Arr::get($hobbiiUser->user, 'first_name'),
            'last_name' => Arr::get($hobbiiUser->user, 'last_name'),
            'token' => $hobbiiUser->token,
            'refresh_token' => $hobbiiUser->refreshToken,
        ]);
        
        Auth::login($user);
        
        $request->session()->regenerate();

        return redirect()->intended('/');
    }
}
````

## Customisation
Publish the configuration file to customise settings, by running
```shell
php artisan vendor:publish --provider="Hobbii\SocialiteProvider\SocialiteServiceProvider" --tag=config
```
Customise the configurations in `config/hobbii-socialite.php`.
