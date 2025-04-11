    <?php

    use Illuminate\Support\Facades\Storage;
    use Melihovv\Base64ImageDecoder\Base64ImageDecoder;
    use Illuminate\Support\Str;

    function uploadBase64Image($base64Image, $subdirectory = 'img/barang')
    {
        $decoder = new Base64ImageDecoder($base64Image, ['jpeg', 'png', 'jpg']);
        $decodedContent = $decoder->getDecodedContent();
        $format = $decoder->getFormat();
        $imageName = Str::random(10) . '.' . $format;
        $imagePath = $subdirectory . '/' . $imageName;

        Storage::disk('public')->put($imagePath, $decodedContent);

        return $imagePath;
    }
