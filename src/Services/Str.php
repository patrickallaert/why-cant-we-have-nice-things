<?php

namespace History\Services;

class Str extends \Illuminate\Support\Str
{
    /**
     * {@inheritdoc}
     */
    protected static function charsArray()
    {
        $chars = parent::charsArray();
        unset($chars['at']);

        return $chars;
    }
}
