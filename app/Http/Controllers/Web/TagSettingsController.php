<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagSettingsController extends Controller
{
    public function index()
    {
        $customerTags = Tag::where('type', 'customer')->orderBy('name')->withCount(['customers'])->get();
        $cardTags     = Tag::where('type', 'card')->orderBy('name')->withCount(['cards'])->get();

        return view('settings.tags', compact('customerTags', 'cardTags'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50|trim',
            'type' => 'required|in:customer,card',
        ]);

        $data['name'] = trim($data['name']);

        $exists = Tag::where('type', $data['type'])
            ->whereRaw('LOWER(name) = ?', [strtolower($data['name'])])
            ->exists();

        if ($exists) {
            return back()->with('error', "A etiqueta \"{$data['name']}\" já existe.");
        }

        Tag::create($data);

        return back()->with('success', "Etiqueta \"{$data['name']}\" criada.");
    }

    public function destroy(Tag $tag)
    {
        $name = $tag->name;
        $tag->customers()->detach();
        $tag->cards()->detach();
        $tag->delete();

        return back()->with('success', "Etiqueta \"{$name}\" removida.");
    }
}
