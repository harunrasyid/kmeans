<?php
use Defuse\Crypto;

if (!function_exists('encrypt')) {

    /**
     * Encrypt string
     *
     * @param $text
     * @return $text
     */
    function encrypt($text)
    {
        $keyString = config_get('security.defuse_key');
        $key = Crypto\Key::loadFromAsciiSafeString($keyString);
        return Crypto\Crypto::encrypt($text, $key);
    }
}

if (!function_exists('decrypt')) {

    /**
     * Decrypt string
     *
     * @param $text
     * @return $text
     */
    function decrypt($text)
    {
        $keyString = config_get('security.defuse_key');
        $key = Crypto\Key::loadFromAsciiSafeString($keyString);
        return Crypto\Crypto::decrypt($text, $key);
    }
}

if (!function_exists('hash_bcrypt')) 
{    
    /**
     * Hash text with bcrypt
     *
     * @param $text
     * @return $text
     */
    function hash_bcrypt($text)
    {
        $salt = config_get('security.hash_key');
        return password_hash($text, PASSWORD_BCRYPT, [
            'cost' => 11,
            'salt' => $salt
        ]);
    }
}
