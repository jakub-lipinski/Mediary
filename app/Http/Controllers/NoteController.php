<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        return Inertia('Notes/Index', [
            'notes' => $request->user()->notes()->orderBy('date', 'desc')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'mood' => 'required|string|max:64',
            'energy_level' => 'required|integer|min:1|max:10',
            'stress_level' => 'required|integer|min:1|max:10',
            'sleep_hours' => 'required|numeric|min:0|max:24',
            'water_intake' => 'required|numeric|min:0|max:20',
            'note' => 'nullable|string|max:5000',
        ]);

        $request->user()->notes()->create($data);

        return redirect()->back()->with('success', 'Wpis dziennika dodany pomyślnie.');
    }

    public function destroy(Note $note)
    {
        abort_unless($note->user_id === Auth::id(), 403);

        $note->delete();

        return redirect()->back()->with('success', 'Wpis dziennika usunięty pomyślnie.');
    }
}
