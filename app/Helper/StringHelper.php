<?php
namespace App\Helper;
class StringHelper {

    public static function string_between_two_string($str, $starting_word, $ending_word)
    {
        $subtring_start = strpos($str, $starting_word);
        $subtring_start += strlen($starting_word);
        $size = strpos($str, $ending_word, $subtring_start) - $subtring_start;

        return substr($str, $subtring_start, $size);
    }
}
