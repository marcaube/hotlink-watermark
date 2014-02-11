<?php

/* CONFIG ----------------------------------------------------------------------------------------------------------- */

$watermark        = __DIR__ . '/watermark.gif'; // The image/logo to watermark
$watermarkOffset  = 2; // Offset in px from both bottom and right
$watermarkOpacity = 60; // From 0 to 100, the watermark's opacity

/* END CONFIG ------------------------------------------------------------------------------------------------------- */


// Get the infos from the requested image
$root      = $_SERVER['DOCUMENT_ROOT'];
$uri       = parse_url(urldecode($_SERVER['REQUEST_URI']), PHP_URL_PATH);
$imagePath = $root . '/' . $uri;
$extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

if (!in_array($extension, array('png', 'gif', 'jpg', 'jpeg'))) {
    exit();
}


/**
 * Open an image with the proper GD function
 *
 * @param  string   $filePath
 *
 * @return resource
 */
function openImage($filePath)
{
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    switch ($extension) {
        case 'png':
            $image = @imagecreatefrompng($filePath);
            break;
        case 'gif':
            $image = @imagecreatefromgif($filePath);
            break;
        default:
            $image = @imagecreatefromjpeg($filePath);
            break;
    }

    return $image;
}


/**
 * Apply the watermark to the image
 *
 * @param string $filePath
 * @param string $watermarkPath
 * @param int    $offset
 * @param int    $opacity
 *
 * @return null|resource
 */
function generateImage($filePath, $watermarkPath, $offset = 2, $opacity = 60)
{
    $src = openImage($filePath);
    $watermarkPath = openImage($watermarkPath);

    // Get images dimensions
    $imageHeight = imagesy($src);
    $imageWidth  = imagesx($src);
    $logoWidth   = imagesx($watermarkPath);
    $logoHeight  = imagesy($watermarkPath);

    // Calc where to start drawing the watermark
    $startX = $imageWidth - $offset - $logoWidth;
    $startY = $imageHeight - $offset - $logoHeight;

    imagecopymerge($src, $watermarkPath, $startX, $startY, 0, 0, $logoWidth, $logoHeight, $opacity);
    imagedestroy($watermarkPath);

    return $src;
}


/**
 * Send image to the browser with the proper MIME type
 *
 * @param resource $resource
 * @param string   $extension
 */
function sendImage($resource, $extension)
{
    if (in_array($extension, array('png', 'gif', 'jpeg'))) {
        header("Content-Type: image/".$extension);
    } else {
        header("Content-Type: image/jpeg");
    }

    switch ($extension) {
        case 'png':
            imagepng($resource);
            break;
        case 'gif':
            imagegif($resource);
            break;
        default:
            imagejpeg($resource);
            break;
    }

    imagedestroy($resource);
    exit();
}

// Make the magic happen
$image = generateImage($imagePath, $watermark, $watermarkOffset, $watermarkOpacity);
sendImage($image, $extension);
