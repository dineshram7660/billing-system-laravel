<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::orderBy('product_name')->paginate(20);

        return view('products.index', compact('products'));
    }

    /**
     * Autocomplete source for the Bill/Estimate line-item picker — see
     * ajax_search_product.php / ajax_get_price.php in the legacy app.
     * Any authenticated user can search products; the Product module's
     * own CRUD permissions gate editing the catalog, not looking it up
     * from another module's form.
     */
    public function search(): JsonResponse
    {
        $term = (string) request('q', '');

        $products = Product::query()
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($query) use ($term) {
                    $query->where('product_name', 'like', "%{$term}%")
                        ->orWhere('service_no', 'like', "%{$term}%")
                        ->orWhere('hsn_code', 'like', "%{$term}%");
                });
            })
            ->orderBy('product_name')
            ->limit(20)
            ->get(['id', 'product_name', 'service_no', 'hsn_code', 'per_unit', 'price']);

        return response()->json($products);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('products.form', ['product' => new Product]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        Product::create($request->validated());

        return redirect()->route('products.index')->with('status', 'Product added successfully.');
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('products.form', compact('product'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $product->update($request->validated());

        return redirect()->route('products.index')->with('status', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $product->delete();

        return redirect()->route('products.index')->with('status', 'Product deleted successfully.');
    }
}
