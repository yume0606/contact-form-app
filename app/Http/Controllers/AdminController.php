<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;

class AdminController extends Controller
{
    public function index(IndexContactRequest $request)
    {
        $query = Contact::with(['category', 'tags']);

        $validated = $request->validated();

        $query->when($validated['keyword'] ?? null, function ($q, $keyword) {
            $q->where(function ($q2) use ($keyword) {
                $q2->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        });

        $query->when($validated['gender'] ?? null, function ($q, $gender) {
            $q->where('gender', $gender);
        });

        $query->when($validated['category_id'] ?? null, function ($q, $categoryId) {
            $q->where('category_id', $categoryId);
        });

        $query->when($validated['date'] ?? null, function ($q, $date) {
            $q->whereDate('created_at', $date);
        });

        $contacts = $query->latest()->paginate(7);

        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.index', compact('contacts', 'categories', 'tags'));
    }

    public function show(Contact $contact)
    {
        $contact->load(['category', 'tags']);

        return view('admin.show', compact('contact'));
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect('/admin');
    }
}
