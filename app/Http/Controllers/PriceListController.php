<?php

namespace App\Http\Controllers;

use App\Domain\Pricing\Models\PriceList;
use App\Domain\Pricing\Models\PriceListItem;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriceListController extends Controller
{
    public function index(Request $request)
    {
        $query = PriceList::withCount('items');

        if ($request->filled('channel_type')) {
            $query->where('channel_type', $request->channel_type);
        }

        if ($request->filled('search')) {
            $search = '%' . strtolower($request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(code) LIKE ?', [$search]);
            });
        }

        $priceLists = $query->orderBy('id', 'asc')->paginate(10)->withQueryString();

        $stats = [
            'total' => PriceList::count(),
            'active' => PriceList::where('is_active', true)->count(),
            'total_items' => PriceListItem::count(),
            'gt_count' => PriceList::where('channel_type', 'GT')->count(),
            'mt_count' => PriceList::where('channel_type', 'MT')->count(),
        ];

        return view('price_lists.index', compact('priceLists', 'stats'));
    }

    public function create()
    {
        return view('price_lists.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:30|unique:price_lists,code',
            'name' => 'required|string|max:100',
            'channel_type' => 'required|in:GT,MT,WHOLESALE,RETAIL,ALL',
            'currency' => 'required|string|max:10',
            'valid_from' => 'required|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
        ]);

        $priceList = PriceList::create([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'channel_type' => $validated['channel_type'],
            'currency' => strtoupper(trim($validated['currency'])),
            'valid_from' => $validated['valid_from'],
            'valid_to' => $validated['valid_to'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('price-lists.show', $priceList->id)
            ->with('success', "Daftar harga {$priceList->name} ({$priceList->code}) berhasil dibuat. Silakan tambahkan harga per produk.");
    }

    public function show(Request $request, $id)
    {
        $priceList = PriceList::withCount('items')->findOrFail($id);

        $query = $priceList->items()->with(['product.category', 'productUnit']);

        if ($request->filled('search')) {
            $search = '%' . strtolower($request->search) . '%';
            $query->whereHas('product', function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(sku) LIKE ?', [$search]);
            });
        }

        $items = $query->orderBy('id', 'asc')->paginate(15)->withQueryString();

        // Ambil semua produk aktif beserta satuannya untuk dropdown tambah item
        $products = Product::where('is_active', true)->with('units')->orderBy('name')->get();

        return view('price_lists.show', compact('priceList', 'items', 'products'));
    }

    public function edit($id)
    {
        $priceList = PriceList::findOrFail($id);
        return view('price_lists.edit', compact('priceList'));
    }

    public function update(Request $request, $id)
    {
        $priceList = PriceList::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:30|unique:price_lists,code,' . $id,
            'name' => 'required|string|max:100',
            'channel_type' => 'required|in:GT,MT,WHOLESALE,RETAIL,ALL',
            'currency' => 'required|string|max:10',
            'valid_from' => 'required|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'required|boolean',
        ]);

        $priceList->update([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'channel_type' => $validated['channel_type'],
            'currency' => strtoupper(trim($validated['currency'])),
            'valid_from' => $validated['valid_from'],
            'valid_to' => $validated['valid_to'] ?? null,
            'is_active' => (bool) $validated['is_active'],
        ]);

        return redirect()->route('price-lists.show', $priceList->id)
            ->with('success', "Informasi daftar harga {$priceList->name} berhasil diperbarui.");
    }

    public function storeItem(Request $request, $id)
    {
        $priceList = PriceList::findOrFail($id);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_unit_id' => 'required|exists:product_units,id',
            'min_quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
        ]);

        // Verifikasi bahwa unit milik produk yang dipilih
        $unit = ProductUnit::where('id', $validated['product_unit_id'])
            ->where('product_id', $validated['product_id'])
            ->firstOrFail();

        PriceListItem::updateOrCreate(
            [
                'price_list_id' => $priceList->id,
                'product_id' => $validated['product_id'],
                'product_unit_id' => $unit->id,
                'min_quantity' => $validated['min_quantity'],
            ],
            [
                'unit_price' => $validated['unit_price'],
            ]
        );

        return redirect()->route('price-lists.show', $priceList->id)
            ->with('success', "Harga produk berhasil disimpan ke dalam daftar harga {$priceList->name}.");
    }

    public function destroyItem($id, $itemId)
    {
        $priceList = PriceList::findOrFail($id);
        $item = PriceListItem::where('price_list_id', $priceList->id)->where('id', $itemId)->firstOrFail();
        $item->delete();

        return redirect()->route('price-lists.show', $priceList->id)
            ->with('success', "Item harga berhasil dihapus dari daftar harga.");
    }

    public function destroy($id)
    {
        $priceList = PriceList::findOrFail($id);

        if ($priceList->customers()->exists()) {
            $priceList->update(['is_active' => false]);
            return redirect()->route('price-lists.index')
                ->with('info', "Daftar harga {$priceList->name} sedang digunakan oleh pelanggan, sehingga statusnya dinonaktifkan.");
        }

        $name = $priceList->name;
        $priceList->items()->delete();
        $priceList->delete();

        return redirect()->route('price-lists.index')
            ->with('success', "Daftar harga {$name} berhasil dihapus.");
    }
}
