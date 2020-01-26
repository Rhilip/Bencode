<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 1/22/2020
 * Time: 2020
 */

namespace Rhilip\Bencode;

/**
 * Class Bencode
 *
 * Convert Map:
 *  - Dictionary (starts with d, ends with e, with key sort)
 *  - List (starts with l, ends with e
 *  - Integer (starts with i, ends with e
 *  - String (starts with number denoting number of characters followed by : and then the string)
 *
 * @package Rhilip\Bencode
 * @author Rhilip
 * @license MIT
 *
 * @see https://en.wikipedia.org/wiki/Bencode
 * @see https://wiki.theory.org/index.php/BitTorrentSpecification
 */
class Bencode
{
    /**
     * Decode bencoded data from string
     *
     * @param string $data
     * @param int $pos
     * @return mixed
     * @throws ParseErrorException
     */
    public static function decode($data, &$pos = 0)
    {
        $start_decode = ($pos === 0);
        if ($data[$pos] === 'd') {
            $pos++;
            $return = [];
            while ($data[$pos] !== 'e') {
                $key = self::decode($data, $pos);
                $value = self::decode($data, $pos);
                if ($key === null || $value === null) {
                    break;
                }
                if (!is_string($key)) {
                    throw new ParseErrorException('Invalid key type, must be string: ' . gettype($key));
                }
                $return[$key] = $value;
            }
            ksort($return, SORT_STRING);
            $pos++;
        } elseif ($data[$pos] === 'l') {
            $pos++;
            $return = [];
            while ($data[$pos] !== 'e') {
                $value = self::decode($data, $pos);
                $return[] = $value;
            }
            $pos++;
        } elseif ($data[$pos] === 'i') {
            $pos++;
            $digits = strpos($data, 'e', $pos) - $pos;
            $return = substr($data, $pos, $digits);
            if ($return === '-0') {
                throw new ParseErrorException('Cannot have integer value -0');
            }
            $multiplier = 1;
            if ($return[0] === '-') {
                $multiplier = -1;
                $return = substr($return, 1);
            }
            if (!ctype_digit($return)) {
                throw new ParseErrorException('Cannot have non-digit values in integer number: ' . $return);
            }
            $return = $multiplier * ((int)$return);
            $pos += $digits + 1;
        } else {
            $digits = strpos($data, ':', $pos) - $pos;
            $len = (int)substr($data, $pos, $digits);
            $pos += ($digits + 1);
            $return = substr($data, $pos, $len);
            $pos += $len;
        }
        if ($start_decode) {
            if ($pos !== strlen($data)) {
                throw new ParseErrorException('Could not fully decode bencode string');
            }
        }
        return $return;
    }

    /**
     * Encode arbitrary data to bencode string
     *
     * @param mixed $data
     * @return string
     */
    public static function encode($data)
    {
        if (is_array($data)) {
            $return = '';
            $check = -1;
            $list = true;
            foreach ($data as $key => $value) {
                if ($key !== ++$check) {
                    $list = false;
                    break;
                }
            }
            if ($list) {
                $return .= 'l';
                foreach ($data as $value) {
                    $return .= self::encode($value);
                }
            } else {
                $return .= 'd';
                ksort($data, SORT_STRING);
                foreach ($data as $key => $value) {
                    $return .= self::encode(strval($key));
                    $return .= self::encode($value);
                }
            }
            $return .= 'e';
        } elseif (is_integer($data)) {
            $return = 'i' . $data . 'e';
        } else {
            $return = strlen($data) . ':' . $data;
        }
        return $return;
    }

    /**
     * Load data from bencoded file
     *
     * @param string $path
     * @return mixed
     * @throws ParseErrorException
     */
    public static function load($path)
    {
        return self::decode(file_get_contents($path));
    }

    /**
     * Dump data to bencoded file
     *
     * @param string $path
     * @param $data
     * @return mixed
     */
    public static function dump($path, $data)
    {
        return file_put_contents($path, self::encode($data));
    }
}