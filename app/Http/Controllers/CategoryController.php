<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\TagType;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::with('tagTypes')
            ->when(request('search'), fn($q, $search) => $q->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('prefix', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $tagTypes = TagType::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
            'tagTypes' => $tagTypes,
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $tagTypeIds = $data['tag_types'] ?? [];
        unset($data['tag_types']);

        $category = Category::create($data);
        $category->tagTypes()->sync($tagTypeIds);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $tagTypeIds = $data['tag_types'] ?? [];
        unset($data['tag_types']);

        $category->update($data);
        $category->tagTypes()->sync($tagTypeIds);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
