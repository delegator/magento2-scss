<?php

// @codingStandardsIgnoreFile

use Magento\Framework\Phrase;

function __()
{
    $argc = func_get_args();
    $text = array_shift($argc);
    if (!empty($argc) && is_array($argc[0])) {
        $argc = $argc[0];
    }
    return new Phrase($text, $argc);
}
