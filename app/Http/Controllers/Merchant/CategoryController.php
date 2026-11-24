<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = $request->user()->merchant->categories()->withCount('products')->get();
        return view('merchant.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:50']]);
        $request->user()->merchant->categories()->create($data);
        return back()->with('success', 'Kategori ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        abort_if($category->merchant_id !== $request->user()->merchant->id, 403);
        $category->update($request->validate(['name' => ['required', 'string', 'max:50']]));
        return back()->with('success', 'Kategori diperbarui.');
    }

    public function destroy(Request $request, Category $category)
    {
        abort_if($category->merchant_id !== $request->user()->merchant->id, 403);
        $category->delete();
        return back()->with('success', 'Kategori dihapus.');
    }
}