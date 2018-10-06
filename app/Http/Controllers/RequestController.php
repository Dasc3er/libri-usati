<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $args['results'] = Models\Subject::orderBy('name')->paginate(30);
        $args['results']->setPath(route($request->route()->getName()));

        return view('subjects/index', $args);
    }

    public function datail()
    {
        $args['result'] = Models\Subject::findOrFail($args['id']);
        $args['books'] = $args['result']->books()->orderBy('created_at', 'desc')->paginate(10);
        $args['books']->setPath(route($request->route()->getName(), ['id' => $args['id']]));

        return view('subjects/datail', $args);
    }

    public function form(Request $request)
    {
        if (!empty($args['id'])) {
            $args['result'] = Models\Subject::findOrFail($args['id']);
        }

        return view('subjects/form', $args);
    }

    public function formPost(Request $request)
    {
        if (!$this->validator->hasErrors()) {
            if (!empty($args['id'])) {
                $subject = Models\Subject::findOrFail($args['id']);
            } else {
                $subject = new Models\Subject();

                $subject->user()->associate(auth()->user());
            }

            $subject->name = $request->input('name');

            $subject->save();

            $request->session()->flash('infos', __('subjects.success'));
            return redirect()->route('subjects');
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
            Models\Subject::findOrFail($args['id'])->delete();
        }

        return redirect()->route('subjects');
    }
}
