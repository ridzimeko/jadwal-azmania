<?php

namespace App\Helpers;

class ColorHelper
{
    public static function getTextColor(string $bgColor): string
    {
        // Hilangkan tanda #
        $hex = ltrim($bgColor, '#');

        // Jika format singkat (#abc), ubah jadi format panjang (#aabbcc)
        if (strlen($hex) === 3) {
            $hex = preg_replace('/(.)/', '$1$1', $hex);
        }

        // Konversi ke RGB
        [$r, $g, $b] = [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];

        // Rumus luminance (kecerahan)
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b);

        // Jika warna gelap, pakai teks putih; jika cerah, pakai teks hitam
        return $luminance < 128 ? '#FFFFFF' : '#000000';
    }
}
