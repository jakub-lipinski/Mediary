<?php

namespace App\Http\Controllers;

use App\Http\Requests\Note\StoreNoteRequest;
use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class NoteController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia('Notes/Index', [
            'notes' => $request->user()->notes()->latest('date')->get(),
        ]);
    }

    public function store(StoreNoteRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['note'] ??= '';

        $request->user()->notes()->create($data);

        return redirect()->back()->with('success', 'Wpis dziennika dodany pomyślnie.');
    }

    public function destroy(Request $request, Note $note): RedirectResponse
    {
        abort_unless($note->user()->is($request->user()), 403);

        $note->delete();

        return redirect()->back()->with('success', 'Wpis dziennika usunięty pomyślnie.');
    }
}
