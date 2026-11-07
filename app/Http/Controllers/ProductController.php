<?php

namespace App\Http\Controllers;

use App\Domain\Product\Models\Category;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductUnit;
use App\Domain\Tenant\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'units', 'stocks']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('search')) {
            $search = '%' . strtolower($request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(sku) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(brand) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(barcode) LIKE ?', [$search]);
            });
        }

        $products = $query->orderBy('id', 'desc')->paginate(12)->withQueryString();

        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'categories' => Category::where('is_active', true)->count(),
            'total_stock_units' => (int) DB::table('stocks')->sum('qty_on_hand'),
        ];

        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('products.index', compact('products', 'stats', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'sku' => 'required|string|max:50|unique:products,sku',
            'barcode' => 'nullable|string|max:50|unique:products,barcode',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'base_uom' => 'required|string|max:20',
            'cost_price' => 'required|numeric|min:0',
            'weight_gram' => 'nullable|numeric|min:0',
            'volume_cbm' => 'nullable|numeric|min:0',
            'units' => 'nullable|array',
            'units.*.unit_name' => 'nullable|string|max:30',
            'units.*.conversion_factor' => 'nullable|integer|min:1',
            'units.*.barcode' => 'nullable|string|max:50',
        ]);

        $baseUomUpper = strtoupper(trim($validated['base_uom']));

        DB::transaction(function () use ($validated, $baseUomUpper, $request) {
            $barcode = !empty($validated['barcode']) ? trim($validated['barcode']) : null;
            $brand = !empty($validated['brand']) ? trim($validated['brand']) : null;

            $product = Product::create([
                'tenant_id' => TenantContext::getTenantId(),
                'category_id' => $validated['category_id'],
                'sku' => strtoupper(trim($validated['sku'])),
                'barcode' => $barcode,
                'name' => trim($validated['name']),
                'brand' => $brand,
                'base_uom' => $baseUomUpper,
                'cost_price' => $validated['cost_price'],
                'weight_gram' => $validated['weight_gram'] ?? 0,
                'volume_cbm' => $validated['volume_cbm'] ?? 0,
                'is_active' => true,
            ]);

            // Selalu pastikan Base Unit (satuan dasar) terdaftar dengan faktor konversi 1
            ProductUnit::create([
                'product_id' => $product->id,
                'unit_name' => $baseUomUpper,
                'conversion_factor' => 1,
                'barcode' => $barcode,
                'is_base_unit' => true,
            ]);

            // Daftarkan satuan tambahan (misal: DUS, KARTON, PAK)
            if (!empty($validated['units'])) {
                foreach ($validated['units'] as $u) {
                    $uName = strtoupper(trim($u['unit_name'] ?? ''));
                    $conv = (int) ($u['conversion_factor'] ?? 1);
                    if ($uName !== '' && $uName !== $baseUomUpper && $conv > 1) {
                        ProductUnit::updateOrCreate(
                            ['product_id' => $product->id, 'unit_name' => $uName],
                            [
                                'conversion_factor' => $conv,
                                'barcode' => !empty($u['barcode']) ? trim($u['barcode']) : null,
                                'is_base_unit' => false,
                            ]
                        );
                    }
                }
            }
        });

        return redirect()->route('products.index')->with('success', "Produk {$validated['name']} ({$validated['sku']}) berhasil ditambahkan ke katalog.");
    }

    public function show($id)
    {
        $product = Product::with([
            'category',
            'units',
            'stocks.warehouse',
            'priceListItems.priceList',
            'priceListItems.productUnit',
        ])->findOrFail($id);

        return view('products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::with('units')->findOrFail($id);
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'sku' => 'required|string|max:50|unique:products,sku,' . $id,
            'barcode' => 'nullable|string|max:50|unique:products,barcode,' . $id,
            'category_id' => 'required|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'base_uom' => 'required|string|max:20',
            'cost_price' => 'required|numeric|min:0',
            'weight_gram' => 'nullable|numeric|min:0',
            'volume_cbm' => 'nullable|numeric|min:0',
            'is_active' => 'required|boolean',
            'units' => 'nullable|array',
            'units.*.unit_name' => 'nullable|string|max:30',
            'units.*.conversion_factor' => 'nullable|integer|min:1',
            'units.*.barcode' => 'nullable|string|max:50',
        ]);

        $baseUomUpper = strtoupper(trim($validated['base_uom']));

        DB::transaction(function () use ($product, $validated, $baseUomUpper) {
            $barcode = !empty($validated['barcode']) ? trim($validated['barcode']) : null;
            $brand = !empty($validated['brand']) ? trim($validated['brand']) : null;

            $product->update([
                'category_id' => $validated['category_id'],
                'sku' => strtoupper(trim($validated['sku'])),
                'barcode' => $barcode,
                'name' => trim($validated['name']),
                'brand' => $brand,
                'base_uom' => $baseUomUpper,
                'cost_price' => $validated['cost_price'],
                'weight_gram' => $validated['weight_gram'] ?? 0,
                'volume_cbm' => $validated['volume_cbm'] ?? 0,
                'is_active' => (bool) $validated['is_active'],
            ]);

            // Update atau buat base unit
            $baseUnit = ProductUnit::where('product_id', $product->id)->where('is_base_unit', true)->first();
            if ($baseUnit) {
                $baseUnit->update([
                    'unit_name' => $baseUomUpper,
                    'conversion_factor' => 1,
                    'barcode' => $barcode,
                ]);
            } else {
                ProductUnit::create([
                    'product_id' => $product->id,
                    'unit_name' => $baseUomUpper,
                    'conversion_factor' => 1,
                    'barcode' => $barcode,
                    'is_base_unit' => true,
                ]);
            }

            // Sync satuan tambahan
            if (!empty($validated['units'])) {
                foreach ($validated['units'] as $u) {
                    $uName = strtoupper(trim($u['unit_name'] ?? ''));
                    $conv = (int) ($u['conversion_factor'] ?? 1);
                    if ($uName !== '' && $uName !== $baseUomUpper && $conv > 1) {
                        ProductUnit::updateOrCreate(
                            ['product_id' => $product->id, 'unit_name' => $uName],
                            [
                                'conversion_factor' => $conv,
                                'barcode' => !empty($u['barcode']) ? trim($u['barcode']) : null,
                                'is_base_unit' => false,
                            ]
                        );
                    }
                }
            }
        });

        return redirect()->route('products.index')->with('success', "Data produk {$product->name} ({$product->sku}) berhasil diperbarui.");
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        $hasStock = $product->stocks()->where('qty_on_hand', '>', 0)->exists();
        $hasOrderItems = $product->salesOrderItems()->exists();

        if ($hasStock || $hasOrderItems) {
            $product->update(['is_active' => false]);
            return redirect()->route('products.index')->with('info', "Produk {$product->name} memiliki riwayat transaksi atau saldo stok fisik, sehingga statusnya dinonaktifkan secara aman.");
        }

        $productName = $product->name;
        $product->units()->delete();
        $product->delete();

        return redirect()->route('products.index')->with('success', "Produk {$productName} berhasil dihapus dari sistem.");
    }
}
