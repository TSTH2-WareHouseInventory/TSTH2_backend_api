<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BarangController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'auth:api',
            // new Middleware('permission:view_barang', only: ['index']),
            new Middleware('permission:create_barang', only: ['store']),
            new Middleware('permission:update_barang', only: ['update']),
            new Middleware('permission:delete_barang', only: ['destroy']),
        ];
    }
    public function index()
    {
        $barang = Barang::with('gudangs')->get();
        return response()->json([
            'message' => 'Daftar barang berhasil diambil!',
            'data' => $barang
        ], 200);
    }

    public function store(Request $request)
    {

        $data = $request->all();

        $validator = Validator::make($data, [
            'jenisbarang_id' => 'nullable|exists:jenis_barangs,id',
            'satuan_id' => 'nullable|exists:satuans,id',
            'barangcategory_id' => 'nullable|exists:barang_categories,id',
            'barang_nama' => 'required|string|max:255|unique:barangs,barang_nama',
            'barang_harga' => 'required|numeric|min:0',
            'barang_gambar' => 'nullable|string',
            'gudang_id' => 'required|exists:gudangs,id',
            'stok_tersedia' => 'required|numeric|min:0',
        ], [
            'barang_nama.unique' => 'Barang dengan nama ini sudah ada di database!',
            'barang_nama.required' => 'Nama barang wajib diisi!',
            'barang_harga.required' => 'Harga barang tidak boleh kosong!',
            'barang_harga.numeric' => 'Harga barang harus berupa angka!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Input tidak valid!',
                'errors' => $validator->errors()
            ], 422);
        }

        $data['barang_slug'] = Str::slug($request->barang_nama);
        $data['user_id'] = Auth::id();
        $data['barang_gambar'] = $request->barang_gambar ? uploadBase64Image($request->barang_gambar) : 'default_image.png';


        $barang = Barang::create($data);

        $barang->gudangs()->attach($request->gudang_id, [
            'stok_tersedia' => $request->stok_tersedia,
            'stok_dipinjam' => 0,
            'stok_maintenance' => 0,
        ]);

        return response()->json([
            'message' => 'Barang berhasil ditambahkan!',
            'data' => Barang::with('gudangs')->find($barang->id)
        ], 201);
    }



    public function show($id)
    {
        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json(['message' => 'Barang tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Barang berhasil ditemukan!',
            'data' => $barang
        ], 200);
    }




    public function update(Request $request, $id)
    {
        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json(['message' => 'Barang tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'jenisbarang_id' => 'nullable|exists:jenis_barangs,id',
            'satuan_id' => 'nullable|exists:satuans,id',
            'barangcategory_id' => 'nullable|exists:barang_categories,id',
            'barang_nama' => 'required|string|max:255|unique:barangs,barang_nama,' . $id,
            'barang_harga' => 'required|numeric|min:0',
            'barang_gambar' => 'nullable|string',
            'gudang_id' => 'required|exists:gudangs,id',
            'stok_tersedia' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Input tidak valid!',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only([
            'jenisbarang_id',
            'satuan_id',
            'barangcategory_id',
            'barang_nama',
            'barang_harga'
        ]);

        $data['barang_slug'] = Str::slug($request->barang_nama);

        if (!empty($request->barang_gambar)) {
            if ($barang->barang_gambar && $barang->barang_gambar !== 'default_image.png') {
                Storage::disk('public')->delete($barang->barang_gambar);
            }

            $data['barang_gambar'] = uploadBase64Image($request->barang_gambar);
        }

        $barang->update($data);

        $barang->gudangs()->syncWithoutDetaching([
            $request->gudang_id => [
                'stok_tersedia' => $request->stok_tersedia,
                'stok_dipinjam' => 0,
                'stok_maintenance' => 0,
            ]
        ]);

        return response()->json([
            'message' => 'Barang berhasil diperbarui!',
            'data' => $barang
        ], 200);
    }



    public function destroy($id)
    {
        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json(['message' => 'Barang tidak ditemukan'], 404);
        }

        $barang->delete();
        return response()->json(['message' => 'Barang berhasil dihapus'], 200);
    }

    public function generateQRCodeimage($id)
    {
        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json(['error' => 'Barang tidak ditemukan'], 404);
        }

        $fileName = $barang->barang_kode . '.svg';
        $path = 'qr_code/' . $fileName;

        $qrContent = $barang->barang_kode;
        $qrCodeContent = QrCode::format('svg')
            ->size(300)
            ->errorCorrection('H')
            ->generate($qrContent);
        Storage::disk('public')->put($path, $qrCodeContent);
        $qrCodeUrl = asset('storage/' . $path);

        return response()->json([
            'barang_kode' => $barang->barang_kode,
            'qr_code_url' => $qrCodeUrl
        ]);
    }

    public function generateAllQRCodesimage()
    {
        $barangs = Barang::all();
        $qrCodes = [];

        foreach ($barangs as $barang) {
            $fileName = $barang->barang_kode . '.png';
            $path = 'qr_code/' . $fileName;

            $qrContent = $barang->barang_kode;

            $qrCodeContent = QrCode::format('png')
                ->size(300)
                ->errorCorrection('H')
                ->generate($qrContent);

            Storage::disk('public')->put($path, $qrCodeContent);
            $qrCodeUrl = asset('storage/' . $path);
            $qrCodes[] = [
                'barang_id'    => $barang->id,
                'barang_kode'  => $barang->barang_kode,
                'qr_code_url'  => $qrCodeUrl,
            ];
        }
        return response()->json([
            'message' => 'QR codes generated successfully.',
            'data'    => $qrCodes,
        ]);
    }

    public function generateAllQRCodes(): \Illuminate\Http\JsonResponse
    {
        $barangs = Barang::all();
        $qrCodesHtml = "
            <h2 style='text-align: center;'>Daftar QR Code</h2>
            <table style='width: 100%; border-collapse: collapse; text-align: center;'>
        ";

        $counter = 0;
        foreach ($barangs as $barang) {
            $qrContent = $barang->barang_kode;
            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode(
                QrCode::format('png')->size(300)->errorCorrection('H')->generate($qrContent)
            );

            // Buka baris baru setiap 2 QR Code
            if ($counter % 2 == 0) {
                $qrCodesHtml .= "<tr>";
            }

            // tempat menambahkan tataletak qr_code
            $qrCodesHtml .= "
                <td style='border: 1px solid #000; padding: 10px;border:none'>
                    <img src='{$qrCodeBase64}' width='150' height='150'>
                    <p style='font-size: 14px;'>{$barang->barang_kode}</p>
                </td>
            ";

            if ($counter % 2 == 1) {
                $qrCodesHtml .= "</tr>";
            }

            $counter++;
        }

        // Tutup baris jika terakhir hanya 1 kolom kalau ganjil
        if ($counter % 2 == 1) {
            $qrCodesHtml .= "<td></td></tr>";
        }

        $qrCodesHtml .= "</table>";

        $pdf = Pdf::loadHTML($qrCodesHtml)->setPaper('a4', 'portrait');

        $pdfPath = 'qr_codes/generated_qr_codes.pdf';
        Storage::disk('public')->put($pdfPath, $pdf->output());

        return response()->json([
            'message' => 'QR codes PDF generated successfully.',
            'pdf_url' => asset('storage/' . $pdfPath),
        ], 200);
    }

    public function generateQRCodeById(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        // default nya 1
        $barang = Barang::findOrFail($id);
        $jumlah = $request->input('jumlah', 1);

        $qrCodesHtml = "
        <h2 style='text-align: center;'>QR Code Untuk {$barang->barang_kode}</h2>
        <table style='width: 100%; border-collapse: collapse; text-align: center;'>
    ";

        for ($i = 0; $i < $jumlah; $i++) {
            if ($i % 2 == 0) {
                $qrCodesHtml .= "<tr>";
            }

            $qrContent = $barang->barang_kode;
            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode(
                QrCode::format('png')->size(300)->errorCorrection('H')->generate($qrContent)
            );

            $qrCodesHtml .= "
            <td style='border: 1px solid #000; padding: 10px; border:none'>
                <img src='{$qrCodeBase64}' width='150' height='150'>
                <p style='font-size: 14px;'>{$barang->barang_kode}</p>
            </td>
        ";

            if ($i % 2 == 1) {
                $qrCodesHtml .= "</tr>";
            }
        }

        if ($jumlah % 2 == 1) {
            $qrCodesHtml .= "<td></td></tr>";
        }

        $qrCodesHtml .= "</table>";

        $pdf = Pdf::loadHTML($qrCodesHtml)->setPaper('a4', 'portrait');
        $pdfPath = "qr_codes/qr_code_{$barang->id}.pdf";
        Storage::disk('public')->put($pdfPath, $pdf->output());

        return response()->json([
            'message' => 'QR codes PDF generated successfully.',
            'pdf_url' => asset('storage/' . $pdfPath),
        ], 200);
    }
}
