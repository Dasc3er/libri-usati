<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models;

class AuthorController extends Controller
{
    public function index(Request $request)
    {
        $args['results'] = Models\Author::orderBy('surname')->orderBy('name')->paginate(30);
        $args['results']->setPath(route($request->route()->getName()));

        return view('authors/index', $args);
    }

    public function datail()
    {
        $args['result'] = Models\Author::findOrFail($args['id']);
        $args['books'] = $args['result']->books()->orderBy('created_at', 'desc')->paginate(10);
        $args['books']->setPath(route($request->route()->getName(), ['id' => $args['id']]));

        return view('authors/datail', $args);
    }

    public function form(Request $request)
    {
        if (!empty($args['id'])) {
            $args['result'] = Models\Author::findOrFail($args['id']);

            if ($args['result']->user_id != auth()->user()->id && !auth()->get()) {
                throw new \Slim\Exception\NotFoundException($request, $response);
            }
        }

        return view('authors/form', $args);
    }

    public function formPost(Request $request)
    {
        if (!$this->validator->hasErrors()) {
            if (!empty($args['id'])) {
                $author = Models\Author::findOrFail($args['id']);

                if ($author->user_id != auth()->user()->id && !auth()->get()) {
                    throw new \Slim\Exception\NotFoundException($request, $response);
                }
            } else {
                $author = new Models\Author();

                $author->user()->associate(auth()->user());
            }

            $author->name = $request->input('name');
            $author->surname = $request->input('surname');

            $author->save();

            $request->session()->flash('infos', __('authors.success'));
            return redirect()->route('authors');
        }
    }

    public function delete()
    {
        $args['delete'] = true;

        return $this->datail();
    }

    public function deletePost(Request $request)
    {
        if (!empty($args['id'])) {
            Models\Author::findOrFail($args['id'])->delete();
        }

        return redirect()->route('authors');
    }
}
