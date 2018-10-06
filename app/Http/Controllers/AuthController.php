<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if (empty($_SESSION['try'])) {
            $_SESSION['try'] = 0;
        }

        if (intval($_SESSION['try']) != 0 && intval($_SESSION['try']) % 3 == 0) {
            $time = 180 + (30 * (intval($_SESSION['try']) / 3 - 1));

            $args['minutes'] = floor($time / 60);
            $args['seconds'] = floor($time % 60);
            $args['time'] = $time;

            $_SESSION['time'] = strtotime('+'.floor($time / 60).' Minutes +'.floor($time % 60).' Seconds', strtotime('now'));
        }

        return view('auth/login', $args);
    }

    public function loginPost(Request $request)
    {
        if (!$this->validator->hasErrors()) {
            $email = $request->input('email');
            $password = $request->input('password');

            if (!empty($email) && !empty($password) && (empty($_SESSION['time']) || $_SESSION['time'] < strtotime('now'))) {
                $auth = auth()->attempt($email, $password);

                if ($auth) {
                    $request->session()->flash('infos', __('login.success'));
                    return redirect()->route();

                    session_regenerate_id();
                } else {
                    $request->session()->flash('errors', __('login.error'));

                    if (!empty($_SESSION['try'])) {
                        $_SESSION['try'] = intval($_SESSION['try']) + 1;
                    }

                    return redirect()->route('login');
                }
            }
        } else {
            return redirect()->route('login');
        }
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->flash('infos', __('logout.success'));

        return redirect()->route();
    }

    public function register(Request $request)
    {
        return view('auth/registration', $args);
    }

    public function registerPost(Request $request)
    {
        if (!$this->validator->hasErrors()) {
            $name = $request->input('name');
            $username = $request->input('username');
            $email = $request->input('email');
            $password = $request->input('password');
            $rep_password = $request->input('rep_password');

            $userFree = \Utils::isUsernameFree($username);
            $emailFree = \Utils::isEmailFree($email);

            if ($userFree && $emailFree && $password == $rep_password) {
                $user = new Models\User();

                $user->name = $name;
                $user->username = $username;
                $user->email = $email;
                $user->password = $password;
                $user->email_token = secure_random_string();
                $user->role = 0;
                $user->state = 1;

                $user->save();

                \Utils::sendEmail($email, 'verification', [':path' => 'http://'.$request->getUri()->getHost().route('verify-email', ['code' => $user->email_token])]);

                $request->session()->flash('infos', __('register.success'));
                return redirect()->route();

                session_regenerate_id();
            } else {
                if ($password != $rep_password) {
                    $request->session()->flash('errors', __('base.errorPassword'));
                }
                if ($userFree) {
                    $request->session()->flash('errors', __('base.errorEmail'));
                }
                if ($emailFree) {
                    $request->session()->flash('errors', __('base.errorUsername'));
                }

                return redirect()->route('registration');
            }
        }
    }

    public function retrieve(Request $request)
    {
        return view('auth/retrieve', $args);
    }

    public function retrievePost(Request $request)
    {
        if (!$this->validator->hasErrors()) {
            $email = $request->input('email');

            $result = Models\User::where(['email' => \Crypt::encode($email), 'state' => 1])->first();
            if (!empty($result)) {
                $token = secure_random_string();

                $result->reset_token = $token;
                $result->save();

                \Utils::sendEmail($email, 'reset', [':path' => 'http://'.$request->getUri()->getHost().route('retrieve', ['token' => $token])]);

                $request->session()->flash('infos', __('retrieve.success'));
                return redirect()->route();
            } else {
                return redirect()->route('retrieve-password');
            }
        }
    }

    public function retrieveToken(Request $request)
    {
        return view('users/credentials', $args);
    }

    public function retrieveTokenPost(Request $request)
    {
        $password = $request->input('password');
        $rep_password = $request->input('rep_password');

        if (!$this->validator->hasErrors() && $password == $rep_password) {
            $result = Models\User::where(['reset_token' => $args['token']])->first();

            $result->password = $password;
            $result->reset_token = '';
            $result->save();

            $request->session()->flash('infos', __('credentials.success'));
            return redirect()->route();
            session_regenerate_id();
        } else {
            if ($password != $rep_password) {
                $request->session()->flash('errors', __('errorPassword'));
            } else {
                $request->session()->flash('errors', __('credentials.error'));
            }

            return redirect()->route('recupero', ['token' => $args['token']]);
        }
    }
}
