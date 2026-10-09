<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::withCount('movies')->paginate(10);
        return view('admin.genres.index', compact('genres'));
    }

    public function create()
    {
        return view('admin.genres.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:genres,name',
            'description' => 'nullable|string',
        ]);

        Genre::create($data);

        return redirect()->route('admin.genres.index')->with('status', 'Gênero criado com sucesso!');
    }

    public function edit(Genre $genre)
    {
        return view('admin.genres.edit', compact('genre'));
    }

    public function update(Request $request, Genre $genre)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:genres,name,' . $genre->id,
            'description' => 'nullable|string',
        ]);

        $genre->update($data);

        return redirect()->route('admin.genres.index')->with('status', 'Gênero atualizado com sucesso!');
    }

    public function destroy(Genre $genre)
    {
        if ($genre->movies()->exists()) {
            return back()->with('error', 'Não é possível excluir este gênero pois existem filmes associados a ele.');
        }

        $genre->delete();

        return redirect()->route('admin.genres.index')->with('status', 'Gênero excluído com sucesso!');
    }
}