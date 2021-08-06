<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\User;
use Socialite;
use Auth;

class OAuthLoginController extends Controller
{

   private $homeUrl = "home";

     /**
     * 各SNSのOAuth認証画面にリダイレクトして認証
     * @param string $provider サービス名
     * @return mixed
     */
    public function socialOAuth(string $provider)
    {

        if (Auth::check()){
            return redirect()->route($this->homeUrl);
        }
        return Socialite::driver($provider)->redirect();
    }

    /**
     * 各サイトからのコールバック
     * @param string $provider サービス名
     * @return mixed
     */
    public function handleProviderCallback($provider)
    {
        if (Auth::check()){
            return redirect()->route($this->homeUrl);
        }

        $socialUser = Socialite::driver($provider)->user();
        $user = User::firstOrNew(['email' => $socialUser->getEmail()]);

        if (!$user->exists) {
            $user->email = $socialUser->getEmail();
            $user->name = $socialUser->getNickname() ?? $socialUser->getName() ?? $socialUser->getNick();
            $user->provider_id = $socialUser->getId();
            $user->provider_name = $provider;
            $user->save();

            $user->assignRole('customer');

        }
        Auth::login($user);

        return redirect()->route($this->homeUrl);
    }
}
