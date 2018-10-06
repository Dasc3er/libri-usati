<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        return view('admin/administration', $args);
    }

    public function logins(Request $request)
    {
        $args['results'] = Models\Login::with('user')->orderBy('created_at', 'desc')->paginate(100);
        $args['results']->setPath(route($request->route()->getName()));

        return view('admin/logins', $args);
    }

    public function resetlogins(Request $request)
    {
        Models\Login::truncate();
        return redirect()->route('visite');
    }

    public function visits(Request $request)
    {
        $args['results'] = Models\Visit::orderBy('created_at', 'desc')->paginate(100);
        $args['results']->setPath(route($request->route()->getName()));

        return view('admin/visits', $args);
    }

    public function resetVisits(Request $request)
    {
        Models\Visit::truncate();
        return redirect()->route('visite');
    }
}
