<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CategoryController extends Controller
{
    public function ListAllCategories()
    {
        $categories = Category::all();
        if ($categories->isEmpty()) {
            return response()->json(['message' => 'No categories found'], 404);
        }
        return response()->json($categories);
    }
    public function ListCategoryById($id)
    {
        $categories = Category::find($id);
        if (!$categories) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        return response()->json($categories);
    }
    public function createCategory(Request $request)
    {
        // Logic to create a new category
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = Category::create([
            'name' => $request->name,
        ]);
        return response()->json($category, 201);
    }
    public function updateCategory(Request $request, $id)
    {
        // Logic to update a category
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $category->update([
            'name' => $request->name,
        ]);
        return response()->json($category);
    }
    public function deleteCategory($id)
    {
        // Logic to delete a category
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }
}
