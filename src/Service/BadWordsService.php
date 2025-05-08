<?php


namespace App\Service;

class BadWordsService
{
    private $badWords;

    public function __construct()
    {
        $this->badWords = [
            'fuck', 'shit',  
        ];
    }

    public function containsBadWords(string $text): bool
    {
        foreach ($this->badWords as $word) {
            if (strpos(strtolower($text), strtolower($word)) !== false) {
                return true;
            }
        }
        return false;
    }
}
