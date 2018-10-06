<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models;

class BaseController extends Controller
{
    use \RachidLaasri\LaravelInstaller\Helpers\MigrationsHelper;

    public function index(Request $request)
    {
        $migrations = $this->getMigrations();
        try {
            $dbMigrations = $this->getExecutedMigrations();
        } catch (\Illuminate\Database\QueryException $e) {
            $dbMigrations = [];
        }

        $todo = count($migrations) - count($dbMigrations);
        if ($todo == count($migrations)) {
            return view('vendor.installer.welcome');
        } elseif ($todo != 0) {
            return view('vendor.installer.update.overview', ['numberOfUpdatesPending' => $todo]);
        }

        $args['books'] = Models\Book::orderBy('created_at', 'desc')->paginate(10);
        $args['authors'] = Models\Author::orderBy('created_at', 'desc')->paginate(10);

        $args['carousel'] = [];
        $args['carousel'][] = ['image' => 'favicon_gb.jpg'];
        $args['carousel'][] = ['image' => 'archivio.png'];

        return view('index', $args);
    }

    public function contacts(Request $request)
    {
        return view('contacts', $args);
    }

    public function contactsForm(Request $request)
    {
        return view('contacts', $args);
    }

    public function cookies(Request $request)
    {
        return view('cookies', $args);
    }


    public function search(Request $request)
    {
        $ignore = [
            'di',
            'a',
            'da',
            'in',
            'su',
            'per',
            'tra',
            'fra',
            'il',
            'lo',
            'la',
            'gli',
            'uno',
            'una',
            'un',
            'del',
        ];
        $search = [];
        $initial = explode(' ', trim($request->input('search')));
        foreach ($initial as $s) {
            if (!empty($s) && !in_array($s, $ignore)) {
                $search[] = trim($s);
            }
        }
        $books = [];
        $books['title'] = $search;

        $authors = [];
        $authors['name'] = $search;

        $subject = [];
        $subject['name'] = $search;

        $args['results'] = Models\Book::orderBy('created_at', 'desc')->where(function ($q) use ($books) {
            foreach ($books as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        $q->orWhere($key, 'LIKE', '%'.$v.'%');
                    }
                } else {
                    $q->orWhere($key, 'LIKE', '%'.$value.'%');
                }
            }
        })->orWhereHas('authors', function ($q) use ($authors) {
            foreach ($authors as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        $q->orWhere($key, 'LIKE', '%'.$v.'%');
                    }
                } else {
                    $q->orWhere($key, 'LIKE', '%'.$value.'%');
                }
            }
        })->orWhereHas('subject', function ($q) use ($subject) {
            foreach ($subject as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        $q->orWhere($key, 'LIKE', '%'.$v.'%');
                    }
                } else {
                    $q->orWhere($key, 'LIKE', '%'.$value.'%');
                }
            }
        })->with('authors', 'subject', 'user')->paginate(20);
        $args['results']->setPath(route($request->route()->getName()));
        $args['search'] = $request->input('search');

        return view('search', $args);
    }
}
