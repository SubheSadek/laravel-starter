<?php

declare(strict_types=1);

namespace App\Traits;

trait FormatterTrait
{
    /**
     * Purify plain text. Remove HTML and JavaScript.
     */
    public function plainTextPurifier(?string $text = null): string
    {
        if (empty($text)) {
            return '';
        }

        $text = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $text);

        return trim(strip_tags($text));
    }
}
