<?php

namespace App\Services;

use App\Repositories\BarangRepository;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;

class QRCodeService
{
    protected $barangRepository;

    public function __construct(BarangRepository $barangRepository)
    {
        $this->barangRepository = $barangRepository;
    }

    public function generateQRCodeImage($id)
    {
        $barang = $this->barangRepository->findById($id);
        if (!$barang) {
            return ['error' => 'Barang tidak ditemukan'];
        }

        $fileName = $barang->barang_kode . '.svg';
        $path = 'qr_code/' . $fileName;

        $qrCodeContent = QrCode::format('svg')
            ->size(300)
            ->errorCorrection('H')
            ->generate($barang->barang_kode);

        Storage::disk('public')->put($path, $qrCodeContent);

        return [
            'barang_kode' => $barang->barang_kode,
            'qr_code_url' => asset('storage/' . $path),
        ];
    }

    public function generateAllQRCodesImage()
    {
        $barangs = $this->barangRepository->getAll();
        $qrCodes = [];

        foreach ($barangs as $barang) {
            $fileName = $barang->barang_kode . '.png';
            $path = 'qr_code/' . $fileName;

            $qrCodeContent = QrCode::format('png')
                ->size(300)
                ->errorCorrection('H')
                ->generate($barang->barang_kode);

            Storage::disk('public')->put($path, $qrCodeContent);
            $qrCodes[] = [
                'barang_id'   => $barang->id,
                'barang_kode' => $barang->barang_kode,
                'qr_code_url' => asset('storage/' . $path),
            ];
        }

        return [
            'message' => 'QR codes generated successfully.',
            'data'    => $qrCodes,
        ];
    }

    public function generateAllQRCodesPDF()
    {
        $barangs = $this->barangRepository->getAll();
        $qrCodesHtml = "<h2 style='text-align: center;'>Daftar QR Code</h2>
                        <table style='width: 100%; border-collapse: collapse; text-align: center;'>";

        $counter = 0;
        foreach ($barangs as $barang) {
            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode(
                QrCode::format('png')->size(300)->errorCorrection('H')->generate($barang->barang_kode)
            );

            if ($counter % 2 == 0) {
                $qrCodesHtml .= "<tr>";
            }

            $qrCodesHtml .= "<td style='padding: 10px;'><img src='{$qrCodeBase64}' width='150' height='150'><p>{$barang->barang_kode}</p></td>";

            if ($counter % 2 == 1) {
                $qrCodesHtml .= "</tr>";
            }

            $counter++;
        }

        if ($counter % 2 == 1) {
            $qrCodesHtml .= "<td></td></tr>";
        }

        $qrCodesHtml .= "</table>";

        $pdf = Pdf::loadHTML($qrCodesHtml)->setPaper('a4', 'portrait');
        $pdfPath = 'qr_codes/generated_qr_codes.pdf';
        Storage::disk('public')->put($pdfPath, $pdf->output());

        return ['message' => 'QR codes PDF generated successfully.', 'pdf_url' => asset('storage/' . $pdfPath)];
    }

    public function generateQRCodePDF($id, $jumlah)
    {
        $barang = $this->barangRepository->findById($id);
        if (!$barang) {
            return ['error' => 'Barang tidak ditemukan'];
        }

        $qrCodesHtml = "<h2 style='text-align: center;'>QR Code Untuk {$barang->barang_kode}</h2>
                        <table style='width: 100%; border-collapse: collapse; text-align: center;'>";

        for ($i = 0; $i < $jumlah; $i++) {
            if ($i % 2 == 0) {
                $qrCodesHtml .= "<tr>";
            }

            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode(
                QrCode::format('png')->size(300)->errorCorrection('H')->generate($barang->barang_kode)
            );

            $qrCodesHtml .= "<td style='padding: 10px;'><img src='{$qrCodeBase64}' width='150' height='150'><p>{$barang->barang_kode}</p></td>";

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

        return ['message' => 'QR codes PDF generated successfully.', 'pdf_url' => asset('storage/' . $pdfPath)];
    }
}
