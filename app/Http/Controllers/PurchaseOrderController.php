<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\KalkulasiEoq;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of purchase orders.
     */
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['items.bahanBaku', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('po.index', compact('purchaseOrders'));
    }

    /**
     * Show the form for creating a new PO.
     */
    public function create()
    {
        $bahanBakus = BahanBaku::with('stok')->orderBy('kode', 'asc')->get();

        // Get latest EOQ data for suggestions
        $latestTahun = KalkulasiEoq::max('tahun');
        $eoqData = [];
        if ($latestTahun) {
            $kalkulasi = KalkulasiEoq::where('tahun', $latestTahun)->get();
            foreach ($kalkulasi as $k) {
                $eoqData[$k->bahan_baku_id] = [
                    'eoq' => round($k->eoq),
                    'rop' => round($k->rop),
                    'ss' => round($k->safety_stock),
                ];
            }
        }

        $noPo = PurchaseOrder::generateNoPo();

        return view('po.create', compact('bahanBakus', 'eoqData', 'noPo'));
    }

    /**
     * Store a newly created PO.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'catatan' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.bahan_baku_id' => 'required|exists:bahan_bakus,id',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.keterangan' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $po = PurchaseOrder::create([
                'no_po' => PurchaseOrder::generateNoPo(),
                'tanggal' => $validated['tanggal'],
                'status' => PurchaseOrder::STATUS_DRAFT,
                'catatan' => $validated['catatan'],
                'user_id' => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'bahan_baku_id' => $item['bahan_baku_id'],
                    'qty' => $item['qty'],
                    'keterangan' => $item['keterangan'] ?? null,
                ]);
            }
        });

        return redirect()->route('po.index')
            ->with('success', 'Purchase Order berhasil dibuat.');
    }

    /**
     * Display the PO detail (printable).
     */
    public function show(PurchaseOrder $po)
    {
        $po->load(['items.bahanBaku', 'user']);
        return view('po.show', compact('po'));
    }

    /**
     * Update PO status.
     */
    public function updateStatus(Request $request, PurchaseOrder $po)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,disetujui,dikirim,diterima,dibatalkan',
        ]);

        $po->update(['status' => $validated['status']]);

        return redirect()->back()
            ->with('success', 'Status PO berhasil diperbarui menjadi "' . ucfirst($validated['status']) . '".');
    }
}
