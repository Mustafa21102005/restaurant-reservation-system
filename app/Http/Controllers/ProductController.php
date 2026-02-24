<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Image;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();

        return view('admin.products.index', compact('products'));
    }

    /**
     * Retrieve product types from the 'type' ENUM column and all product categories.
     *
     * This method extracts the ENUM values defined in the 'type' column of the
     * 'products' table using a raw SQL query. It also fetches all available
     * categories from the database.
     *
     * @return array{
     *     types: string[],
     *     categories: \Illuminate\Database\Eloquent\Collection<\App\Models\Category>
     * }
     */
    private function getTypeAndCategories()
    {
        $typeColumn = DB::select("SHOW COLUMNS FROM products WHERE Field = 'type'")[0]->Type;
        preg_match('/^enum\((.*)\)$/', $typeColumn, $matches);
        $types = array_map(fn($value) => trim($value, "'"), explode(',', $matches[1]));

        $categories = Category::all();

        return compact('types', 'categories');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = $this->getTypeAndCategories();

        return view('admin.products.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->safe()->except(['image']);

        $product = Product::create($data);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');

            $product->imageable()->create([
                'path' => $imagePath,
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product Created Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $data = $this->getTypeAndCategories();

        $data['product'] = $product;

        return view('admin.products.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        // Get validated data except image
        $data = $request->safe()->except(['image']);

        $product->update($data);

        // Handle image replacement
        if ($request->hasFile('image')) {

            // Delete old image (file + db record)
            if ($product->imageable) {
                Storage::disk('public')->delete($product->imageable->path);
                $product->imageable()->delete();
            }

            // Store new image
            $imagePath = $request->file('image')->store('products', 'public');

            // Attach new image
            $product->imageable()->create([
                'path' => $imagePath,
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Check if the product has an image
        if ($product->imageable) {
            // Get the correct image path from the public storage folder
            $imagePath = storage_path('app/public/' . $product->imageable->path);

            // Ensure the file exists before attempting to delete
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            // Delete the image record from the database
            $product->imageable()->delete();
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product Deleted Successfully!');
    }
}
