<?php
function addSnaWatermark($filePath)
{
    if (!file_exists($filePath)) {
        return false;
    }

    $info = getimagesize($filePath);
    if ($info === false) {
        return false;
    }

    $width  = $info[0];
    $height = $info[1];
    $mime   = $info['mime'];

    // Create image resource from file
    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($filePath);
            break;
        case 'image/png':
            $image = imagecreatefrompng($filePath);
            break;
        case 'image/webp':
            $image = imagecreatefromwebp($filePath);
            break;
        default:
            // Unsupported type
            return false;
    }

    if (!$image) {
        return false;
    }

    // Text settings
    $text     = 'SNA';
    $fontFile = __DIR__ . '/fonts/Lato-Bold.ttf';
    if (!file_exists($fontFile)) {
        return false;
    }

    // Fixed / uniform font size across images
    $fontSize = 16;      // adjust as needed, same everywhere
    $angle    = 30;      // fixed 30 degrees

    // Color (white, semi transparent)
    // alpha: 0 = opaque, 127 = fully transparent
    $textColor = imagecolorallocatealpha($image, 255, 255, 255, 115);

    // Calculate text bounding box once (for reference only)
    $bbox = imagettfbbox($fontSize, $angle, $fontFile, $text);
    if ($bbox === false) {
        imagedestroy($image);
        return false;
    }

    $textWidth  = max($bbox[2], $bbox[4]) - min($bbox[0], $bbox[6]);
    $textHeight = max($bbox[1], $bbox[3]) - min($bbox[5], $bbox[7]);

    // FIXED spacing between repeated watermarks (independent of image size)
    // You can tweak these constants; they are literal pixels
    $stepX = 120;   // horizontal spacing in px
    $stepY = 80;    // vertical spacing in px

    // Draw watermarks in a zigzag pattern across the whole image
    $rowIndex = 0;

    // We start above the image and go past the bottom to cover entire area
    for ($y = -$height; $y < $height * 2; $y += $stepY, $rowIndex++) {
        // Zig-zag: shift every other row horizontally by half the step
        $xOffset = ($rowIndex % 2 === 0) ? 0 : (int)($stepX / 2);

        for ($x = -$width; $x < $width * 2; $x += $stepX) {
            $posX = (int)($x + $xOffset);
            // baseline roughly in the vertical center band
            $posY = (int)($y + $height / 2);

            imagettftext(
                $image,
                $fontSize,
                $angle,
                $posX,
                $posY,
                $textColor,
                $fontFile,
                $text
            );
        }
    }

    // Save back to disk (preserve type)
    switch ($mime) {
        case 'image/jpeg':
            imagejpeg($image, $filePath, 90);
            break;
        case 'image/png':
            imagesavealpha($image, true);
            imagepng($image, $filePath, 6);
            break;
        case 'image/webp':
            imagewebp($image, $filePath, 90);
            break;
    }

    imagedestroy($image);
    return true;
}