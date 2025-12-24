<?php
$source = "C:\\Users\\toledok\\Downloads\\MINI_FARMACIA.webp";
$dest = "c:\\Users\\toledok\\Downloads\\Minifarma\\public\\favicon.png";

if (!file_exists($source)) {
    die("Source file not found: $source");
}

$im = @imagecreatefromwebp($source);
if (!$im) {
    die("Failed to load WebP. GD extension might be missing or invalid format.");
}

imagepng($im, $dest);
imagedestroy($im);

echo "Conversion successful: $dest";
