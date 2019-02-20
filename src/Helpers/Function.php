<?php 

if (!function_exists('value'))
{
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (!function_exists('clean_text'))
{
    /**
     * Remove all mention, hashtag and link 
     * 
     * @param string $text 
     * @return string
     */
    function clean_text($text)
    {
        $text = preg_replace('/(^|\b)@\S*($|\b)/', '', $text);
        $text = preg_replace('/(^|\b)#\S*($|\b)/', '', $text);
        $urlRegex = '~(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))~';
        return preg_replace($urlRegex, '', $text);
    }
}

if (!function_exists('cut_string'))
{
    /**
     * Cut string into certain length
     * 
     * @param string $text
     * @param int $maxchar
     * @param string $end
     * @return string
     */
    function cut_string($text, $maxchar, $end='...') {
        if (strlen($text) > $maxchar && $text !== '') {
            $words = preg_split('/\s/', $text);      
            $output = '';
            $i      = 0;
            while (1) {
                $length = strlen($output) + strlen($words[$i]);
                if ($length > $maxchar) {
                    break;
                } 
                else {
                    $output .= " " . $words[$i];
                    ++$i;
                }
            }
            $output .= $end;
        } 
        else {
            $output = $text;
        }
        return $output;
    }
}

if (!function_exists('array_get'))
{
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function array_get($array, $key, $default = null)
    {
        if (is_null($key)) return $array;
        if (isset($array[$key])) return $array[$key];
        foreach (explode('.', $key) as $segment)
        {
            if (!is_array($array) || !array_key_exists($segment, $array))
            {
                return value($default);
            }
            $array = $array[$segment];
        }
        return $array;
    }
}

if (!function_exists('object_get'))
{
    /**
     * Get an item from an object using "dot" notation.
     *
     * @param  object  $object
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function object_get($object, $key, $default = null)
    {
        if (is_null($key) || trim($key) == '') return $object;
        foreach (explode('.', $key) as $segment)
        {
            if (!is_object($object) || !isset($object->{$segment}))
            {
                return value($default);
            }
            $object = $object->{$segment};
        }
        return $object;
    }
}

if (!function_exists('date_is_valid'))
{
    /**
     * Validate date format in Y-m-d H:i:s
     * 
     * @param string $date
     * @return boolean
     */
    function date_is_valid($date, $format = 'Y-m-d H:i:s') 
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}

if (!function_exists('array_sort'))
{
    /**
     * Sort array by it's certain value
     * 
     * @param array $array
     * @param string $on
     * @param string $order
     * @return array
     */
    function array_sort($array, $on, $order=SORT_ASC)
    {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }
            
            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                break;
                case SORT_DESC:
                    arsort($sortable_array);
                break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[] = $array[$k];
            }
        }

        return $new_array;
    }
}
