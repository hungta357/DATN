<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Category::query()->orderBy('id')->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
        ]);

        $validated['slug'] = Str::slug(($validated['slug'] ?? '') ?: $validated['name']);
        Validator::make($validated, [
            'slug' => ['required', 'max:255', Rule::unique('categories', 'slug')],
        ])->validate();

        return response()->json([
            'data' => Category::create($validated),
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'data' => $category,
        ]);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
        ]);

        if (array_key_exists('slug', $validated) || array_key_exists('name', $validated)) {
            $validated['slug'] = Str::slug(
                $validated['slug'] ?? $validated['name'] ?? $category->name,
            );

            Validator::make($validated, [
                'slug' => [
                    'required',
                    'max:255',
                    Rule::unique('categories', 'slug')->ignore($category),
                ],
            ])->validate();
        }

        $category->update($validated);

        return response()->json([
            'data' => $category->refresh(),
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(status: 204);
    }
}
