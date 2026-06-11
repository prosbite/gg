<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\TagType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        $products = Product::with(['category', 'tags', 'images'])
            ->when(request('search'), fn($q, $search) => $q->where(function($q) use ($search) {
                $q->where('item_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = Category::with('tagTypes')->orderBy('name')->get(['id', 'name']);
        $allTagTypes = TagType::with('tags')->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'allTagTypes' => $allTagTypes,
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $tagIds = $data['tags'] ?? [];
        unset($data['tags']);

        if ($data['is_draft'] ?? false) {
            $data['item_code'] = $data['item_code'] ?? $this->generateDraftItemCode();
            $data['category_id'] = $data['category_id'] ?? Category::first()->id;
            $data['status'] = $data['status'] ?? 'available';
            $data['is_active'] = $data['is_active'] ?? true;
        }

        if (isset($data['specifics']) && is_string($data['specifics'])) {
            $data['specifics'] = !empty($data['specifics']) ? json_decode($data['specifics'], true) : null;
        }

        $product = Product::create($data);
        $product->tags()->sync($tagIds);

        if ($request->has('images')) {
            $imageEntries = $request->input('images', []);
            foreach ($imageEntries as $index => $imageData) {
                $file = $request->file("images.{$index}.file");
                if (!$file) continue;

                $path = $file->store('products', 'public');
                $product->images()->create([
                    'file_path' => $path,
                    'label' => $imageData['label'] ?? null,
                    'sort_order' => $index,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $tagIds = $data['tags'] ?? [];
        unset($data['tags']);

        if (isset($data['specifics']) && is_string($data['specifics'])) {
            $data['specifics'] = !empty($data['specifics']) ? json_decode($data['specifics'], true) : null;
        }

        $product->update($data);
        $product->tags()->sync($tagIds);

        if ($request->has('delete_images')) {
            foreach ($request->input('delete_images', []) as $imageId) {
                $image = $product->images()->find($imageId);
                if ($image) {
                    Storage::disk('public')->delete($image->file_path);
                    if ($image->thumbnail_path) {
                        Storage::disk('public')->delete($image->thumbnail_path);
                    }
                    $image->delete();
                }
            }
        }

        if ($request->has('new_images')) {
            $existingCount = $product->images()->count();
            $imageEntries = $request->input('new_images', []);
            foreach ($imageEntries as $index => $imageData) {
                $file = $request->file("new_images.{$index}.file");
                if (!$file) continue;

                $path = $file->store('products', 'public');
                $product->images()->create([
                    'file_path' => $path,
                    'label' => $imageData['label'] ?? null,
                    'sort_order' => $existingCount + $index,
                    'is_primary' => $existingCount + $index === 0,
                ]);
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    private function generateDraftItemCode(): string
    {
        $prefix = 'DRAFT';
        $date = now()->format('Ymd');
        $last = Product::where('item_code', 'like', "{$prefix}-{$date}-%")
            ->orderBy('item_code', 'desc')
            ->first();

        $next = $last ? ((int) substr($last->item_code, -4)) + 1 : 1;

        return "{$prefix}-{$date}-" . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
