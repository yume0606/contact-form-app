<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Models\Contact;
use App\Http\Resources\ContactResource;
use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Http\Requests\Api\V1\UpdateContactRequest;
class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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

        $perPage = $validated['per_page'] ?? 20;
        $contacts = $query->latest()->paginate($perPage);

        return ContactResource::collection($contacts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContactRequest $request)
    {
        $validated = $request->validated();
        $tagIds = $validated['tag_ids'] ?? [];
        unset($validated['tag_ids']);

        $contact = Contact::create($validated);

        $contact->tags()->attach($tagIds);

        $contact->load(['category', 'tags']);

        return (new ContactResource($contact))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        $contact->load(['category', 'tags']);
        return new ContactResource($contact);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContactRequest $request, Contact $contact)
    {
        $validated = $request->validated();
        $tagIds = $validated['tag_ids'] ?? [];
        unset($validated['tag_ids']);

        $contact->update($validated);

        $contact->tags()->sync($tagIds);

        $contact->load(['category', 'tags']);

        return (new ContactResource($contact));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return response()->noContent();
    }
}
