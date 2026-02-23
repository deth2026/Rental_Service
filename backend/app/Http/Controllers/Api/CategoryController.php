<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(Category::paginate(15));
    }

    public function store(Request $_request)
    {
        $record = Category::create($_request->all());

        return response()->json($record, 201);
    }

    public function show(Category $category)
    {
        return response()->json($category);
    }

    public function update(Request $_request, Category $category)
    {
        $category->update($_request->all());

        return response()->json($category->fresh());
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
