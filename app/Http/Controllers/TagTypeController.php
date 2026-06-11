<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTagTypeRequest;
use App\Http\Requests\UpdateTagTypeRequest;
use App\Models\TagType;
use Inertia\Inertia;
use Inertia\Response;

class TagTypeController extends Controller
{
    public function index(): Response
    {
        $tagTypes = TagType::withCount('tags')->latest()->paginate(20);

        return Inertia::render('TagType/Index', [
            'tagTypes' => $tagTypes,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('TagType/Create');
    }

    public function store(StoreTagTypeRequest $request): \Illuminate\Http\RedirectResponse
    {
        TagType::create($request->validated());

        return redirect()->back()
            ->with('success', 'Tag type created successfully.');
    }

    public function show(TagType $tagType): Response
    {
        $tagType->loadCount('tags');

        return Inertia::render('TagType/Show', [
            'tagType' => $tagType,
        ]);
    }

    public function edit(TagType $tagType): Response
    {
        return Inertia::render('TagType/Edit', [
            'tagType' => $tagType,
        ]);
    }

    public function update(UpdateTagTypeRequest $request, TagType $tagType): \Illuminate\Http\RedirectResponse
    {
        $tagType->update($request->validated());

        return redirect()->route('tag-types.index')
            ->with('success', 'Tag type updated successfully.');
    }

    public function destroy(TagType $tagType): \Illuminate\Http\RedirectResponse
    {
        $tagType->delete();

        return redirect()->route('tag-types.index')
            ->with('success', 'Tag type deleted successfully.');
    }
}
