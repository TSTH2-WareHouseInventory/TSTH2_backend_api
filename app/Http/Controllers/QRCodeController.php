<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Services\QRCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeController extends Controller
{
    protected $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    public function generateQRCodeImage($id)
    {
        $result = $this->qrCodeService->generateQRCodeImage($id);
        return response()->json($result, isset($result['error']) ? 404 : 200);
    }

    public function generateAllQRCodesImage()
    {
        $result = $this->qrCodeService->generateAllQRCodesImage();
        return response()->json($result);
    }


    public function generateQRCodePDF(Request $request, $id)
    {
        $jumlah = $request->input('jumlah', 1);
        $result = $this->qrCodeService->generateQRCodePDF($id, $jumlah);
        return response()->json($result, isset($result['error']) ? 404 : 200);
    }
    public function generateAllQRCodesPDF()
    {
        $result = $this->qrCodeService->generateAllQRCodesPDF();
        return response()->json($result);
    }

    // public function generateQRCodeImage($id)
    // {
    //     $barang = Barang::find($id);
    //     if (!$barang) {
    //         return response()->json(['error' => 'Barang tidak ditemukan'], 404);
    //     }

    //     $fileName = $barang->barang_kode . '.svg';
    //     $path = 'qr_code/' . $fileName;

    //     $qrContent = $barang->barang_kode;
    //     $qrCodeContent = QrCode::format('svg')
    //         ->size(300)
    //         ->errorCorrection('H')
    //         ->generate($qrContent);
    //     Storage::disk('public')->put($path, $qrCodeContent);
    //     $qrCodeUrl = asset('storage/' . $path);

    //     return response()->json([
    //         'barang_kode' => $barang->barang_kode,
    //         'qr_code_url' => $qrCodeUrl
    //     ]);
    // }

    // public function generateAllQRCodesImage()
    // {
    //     $barangs = Barang::all();
    //     $qrCodes = [];

    //     foreach ($barangs as $barang) {
    //         $fileName = $barang->barang_kode . '.png';
    //         $path = 'qr_code/' . $fileName;

    //         $qrContent = $barang->barang_kode;

    //         $qrCodeContent = QrCode::format('png')
    //             ->size(300)
    //             ->errorCorrection('H')
    //             ->generate($qrContent);

    //         Storage::disk('public')->put($path, $qrCodeContent);
    //         $qrCodeUrl = asset('storage/' . $path);
    //         $qrCodes[] = [
    //             'barang_id'    => $barang->id,
    //             'barang_kode'  => $barang->barang_kode,
    //             'qr_code_url'  => $qrCodeUrl,
    //         ];
    //     }
    //     return response()->json([
    //         'message' => 'QR codes generated successfully.',
    //         'data'    => $qrCodes,
    //     ]);
    // }

    // public function generateAllQRCodesPDF(): \Illuminate\Http\JsonResponse
    // {
    //     $barangs = Barang::all();
    //     $qrCodesHtml = "
    //         <h2 style='text-align: center;'>Daftar QR Code</h2>
    //         <table style='width: 100%; border-collapse: collapse; text-align: center;'>
    //     ";

    //     $counter = 0;
    //     foreach ($barangs as $barang) {
    //         $qrContent = $barang->barang_kode;
    //         $qrCodeBase64 = 'data:image/png;base64,' . base64_encode(
    //             QrCode::format('png')->size(300)->errorCorrection('H')->generate($qrContent)
    //         );

    //         // Buka baris baru setiap 2 QR Code
    //         if ($counter % 2 == 0) {
    //             $qrCodesHtml .= "<tr>";
    //         }

    //         // tempat menambahkan tataletak qr_code
    //         $qrCodesHtml .= "
    //             <td style='border: 1px solid #000; padding: 10px;border:none'>
    //                 <img src='{$qrCodeBase64}' width='150' height='150'>
    //                 <p style='font-size: 14px;'>{$barang->barang_kode}</p>
    //             </td>
    //         ";

    //         if ($counter % 2 == 1) {
    //             $qrCodesHtml .= "</tr>";
    //         }

    //         $counter++;
    //     }

    //     // Tutup baris jika terakhir hanya 1 kolom kalau ganjil
    //     if ($counter % 2 == 1) {
    //         $qrCodesHtml .= "<td></td></tr>";
    //     }

    //     $qrCodesHtml .= "</table>";

    //     $pdf = Pdf::loadHTML($qrCodesHtml)->setPaper('a4', 'portrait');

    //     $pdfPath = 'qr_codes/generated_qr_codes.pdf';
    //     Storage::disk('public')->put($pdfPath, $pdf->output());

    //     return response()->json([
    //         'message' => 'QR codes PDF generated successfully.',
    //         'pdf_url' => asset('storage/' . $pdfPath),
    //     ], 200);
    // }

    // public function generateQRCodePDF(Request $request, $id): \Illuminate\Http\JsonResponse
    // {
    //     // default nya 1
    //     $barang = Barang::findOrFail($id);
    //     $jumlah = $request->input('jumlah', 1);

    //     $qrCodesHtml = "
    //     <h2 style='text-align: center;'>QR Code Untuk {$barang->barang_kode}</h2>
    //     <table style='width: 100%; border-collapse: collapse; text-align: center;'>
    // ";

    //     for ($i = 0; $i < $jumlah; $i++) {
    //         if ($i % 2 == 0) {
    //             $qrCodesHtml .= "<tr>";
    //         }

    //         $qrContent = $barang->barang_kode;
    //         $qrCodeBase64 = 'data:image/png;base64,' . base64_encode(
    //             QrCode::format('png')->size(300)->errorCorrection('H')->generate($qrContent)
    //         );

    //         $qrCodesHtml .= "
    //         <td style='border: 1px solid #000; padding: 10px; border:none'>
    //             <img src='{$qrCodeBase64}' width='150' height='150'>
    //             <p style='font-size: 14px;'>{$barang->barang_kode}</p>
    //         </td>
    //     ";

    //         if ($i % 2 == 1) {
    //             $qrCodesHtml .= "</tr>";
    //         }
    //     }

    //     if ($jumlah % 2 == 1) {
    //         $qrCodesHtml .= "<td></td></tr>";
    //     }

    //     $qrCodesHtml .= "</table>";

    //     $pdf = Pdf::loadHTML($qrCodesHtml)->setPaper('a4', 'portrait');
    //     $pdfPath = "qr_codes/qr_code_{$barang->id}.pdf";
    //     Storage::disk('public')->put($pdfPath, $pdf->output());

    //     return response()->json([
    //         'message' => 'QR codes PDF generated successfully.',
    //         'pdf_url' => asset('storage/' . $pdfPath),
    //     ], 200);
    // }
}
