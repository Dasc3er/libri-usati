<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $args['results'] = Models\Book::orderBy('created_at', 'desc')->with('authors', 'user')->paginate(10);
        $args['results']->setPath(route($request->route()->getName()));

        return view('books/index', $args);
    }

    public function datail()
    {
        $args['result'] = Models\Book::with('authors', 'user')->findOrFail($args['id']);

        $args['concessions'] = $args['result']->concessions()->get();
        $args['requests'] = $args['result']->requests()->get();

        return view('books/datail', $args);
    }

    public function form(Request $request)
    {
        if (!empty($args['id'])) {
            $args['result'] = Models\Book::with('authors')->findOrFail($args['id']);

            if ($args['result']->user_id != auth()->user()->id && !auth()->get()) {
                throw new \Slim\Exception\NotFoundException($request, $response);
            }
        }

        $args['authors'] = Models\Author::orderBy('surname')->orderBy('name')->get();
        $args['subjects'] = Models\Subject::all();

        return view('books/form', $args);
    }

    public function formPost(Request $request)
    {
        if (!$this->validator->hasErrors()) {
            if (!empty($args['id'])) {
                $book = Models\Book::findOrFail($args['id']);

                if ($book->user_id != auth()->user()->id && !auth()->get()) {
                    throw new \Slim\Exception\NotFoundException($request, $response);
                }
            } else {
                $book = new Models\Book();

                $book->user()->associate(auth()->user());
            }

            $book->subject()->associate($request->input('subject'));
            $book->title = $request->input('title');
            $book->price = $request->input('price');
            $book->description = $request->input('description');

            $isbn = $request->input('isbn');
            $isValid = \Utils::isValidIsbn10($isbn) || \Utils::isValidIsbn13($isbn);
            if (!empty($isbn) && $isValid) {
                $book->isbn = $isbn;
            } elseif (!$isValid) {
                $request->session()->flash('errors', __('books.invalidISBN'));
            } elseif (empty($isbn) && !empty($book->isbn)) {
                $book->isbn = $isbn;
            }

            $book->save();

            if (!empty($_FILES['image']['name'])) {
                $type = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

                $target_file = 'assets/img/uploads/'.$book->id.'.'.$type;

                // Check if image file is a actual image or fake image
                $check = getimagesize($_FILES['image']['tmp_name']);
                if ($check !== false && ($type == 'png' || $type == 'jpg' || $type == 'jpeg')) {
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $request->session()->flash('infos', __('books.imageUploaded'));

                        $book->image = $book->id.'.'.$type;
                        $book->save();
                    } else {
                        $request->session()->flash('infos', __('books.imageError'));
                    }
                }
            } elseif (empty($book->image)) {
                $book->image = 'default.png';
                $book->save();
            }

            $results = [];
            $authors = trim($request->input('authorsAgain'));
            if (!empty($authors)) {
                $authors = explode(',', $authors);
                print_r($authors);
                foreach ($authors as $author) {
                    $segments = explode(' ', trim($author));

                    $name = $segments[0];
                    $surname = '';
                    if (isset($segments[1])) {
                        $surname = $segments[1];
                    }

                    $aut = Models\Author::where(['name' => $name, 'surname' => $surname])->first();
                    if (empty($aut)) {
                        $aut = new Models\Author();

                        $aut->user()->associate(auth()->user());

                        $aut->name = $name;
                        $aut->surname = $surname;

                        $aut->save();
                    }

                    $results[] = $aut->id;
                }
            }

            $authors = $request->input('authors');
            $authors = !empty($authors) ? $authors : [];
            $results = array_merge($results, $authors);

            $book->authors()->sync($results);

            $request->session()->flash('infos', __('books.success'));
            return redirect()->route('books');
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
            Models\Book::findOrFail($args['id'])->delete();
        }

        return redirect()->route('books');
    }

    public function ask(Request $request)
    {
        if (!empty($args['id'])) {
            $book = Models\Book::findOrFail($args['id']);

            if (!auth()->user()->isBookRequested($book)) {
                auth()->user()->requests()->attach($book->id);

                \Utils::sendEmail($book->user()->email, 'request', [':path' => 'http://'.$request->getUri()->getHost().route('book', ['id' => $book->id])]);
            } else {
                auth()->user()->requests()->detach($book->id);
            }
        }

        return redirect()->route('book', $args);
    }

    public function concede(Request $request)
    {
        if (!empty($args['id'])) {
            $book = Models\Book::findOrFail($args['id']);
            $user = Models\User::findOrFail($args['user']);

            if (!$user->isBookConcessed($book)) {
                $book->requests()->updateExistingPivot($args['user'], ['state' => true]);

                \Utils::sendEmail($user->email, 'request_accepted', [':path' => 'http://'.$request->getUri()->getHost().route('book', ['id' => $book->id])]);
            } else {
                $book->concessions()->updateExistingPivot($args['user'], ['state' => false]);
            }
        }

        return redirect()->route('book', $args);
    }
}
