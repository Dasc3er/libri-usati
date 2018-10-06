<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $args['results'] = Models\User::with('books')->orderBy('name', 'asc')->withTrashed()->paginate(30);
        $args['results']->setPath(route($request->route()->getName()));

        return view('users/index', $args);
    }

    public function datail()
    {
        if (!empty($args['id'])) {
            $args['user'] = Models\User::findOrFail($args['id']);
        } elseif (auth()->check()) {
            $args['user'] = auth()->user();
        }

        $args['books'] = $args['user']->books()->orderBy('id', 'DESC')->get();

        return view('users/detail', $args);
    }

    public function delete()
    {
        $args['delete'] = true;

        return $this->datail();
    }

    public function deletePost(Request $request)
    {
        if (!empty($args['id'])) {
            $user = Models\User::findOrFail($args['id']);

            if ($user->id != auth()->user()->id && $user->id != $this->settings['app']['superuser']) {
                $user->cascadeDelete();
            }
        }

        return redirect()->route('users');
    }

    public function enable(Request $request)
    {
        if (!empty($args['id'])) {
            $user = Models\User::withTrashed()->findOrFail($args['id']);

            if ($user->id != auth()->user()->id) {
                $user->restore();
            }
        }

        return redirect()->route('users');
    }

    public function get()
    {
        if (!empty($args['id'])) {
            $user = Models\User::withTrashed()->findOrFail($args['id']);

            if ($user->role == 0) {
                $user->role = 1;
            } else {
                $user->role = 0;
            }

            $user->save();
        }

        return redirect()->route('users');
    }

    public function credentials(Request $request)
    {
        $args['result'] = auth()->user()->toArray();

        return view('users/credentials', $args);
    }

    public function credentialsPost(Request $request)
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
                $user = auth()->user();

                $user->name = $name;
                $user->username = $username;

                if ($user->email != $email) {
                    $user->email = $email;
                    \Utils::sendEmail($email, 'verification', [':path' => 'http://'.$request->getUri()->getHost().route('verify-email', ['code' => $user->email_token])]);
                }

                $user->password = $password;
                $user->email_token = secure_random_string();

                $user->save();

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

                return redirect()->route('credentials');
            }
        }
    }

    public function verifyEmail(Request $request)
    {
        Models\User::where(['email_token' => $args['code'], 'state' => 1])->update(['email_token' => null]);
        return redirect()->route();
    }

    public function sendVerify(Request $request)
    {
        $user = auth()->user();

        $user->email_token = secure_random_string();
        $user->save();

        \Utils::sendEmail($user->email, 'verification', [':path' => 'http://'.$request->getUri()->getHost().route('verify-email', ['code' => $user->email_token])]);

        return redirect()->route();
    }
}
