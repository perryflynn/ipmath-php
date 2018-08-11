<?php

namespace IPMath;


/**
 * This class should contain all required functions to make math with
 * IPv6 addresses but is not finished.
 *
 * Feel free to make a pull request at https://github.com/perryflynn
 */

class Ipv6Calculator extends IpBaseCalculator
{

    /**
     * Largest possible CIDR
     * @return int
     */
    public static function CIDRMAX()
    {
        return 128;
    }


    /**
     * Smallest possible CIDR
     * @return int
     */
    public static function CIDRMIN()
    {
        return 0;
    }


    /**
     * Separator between ip address and CIDR
     * @return string
     */
    public static function CIDRSEPARATOR()
    {
        return '/';
    }


    /**
     * Binary length of a ip address part
     * @return int
     */
    public static function OCTETLENGTHBIN()
    {
        return 16;
    }


    /**
     * Separator between ip address parts
     * @return string
     */
    public static function OCTETSEPARATOR()
    {
        return ':';
    }


    /**
     * Convert a binary address part into hexadecimal format
     * @param string $value
     * @return string
     */
    public static function fromBinary($value)
    {
        return base_convert($value, 2, 16);
    }


    /**
     * Convert a hexadecimal address part into binary format
     * @param string $value
     * @return string
     */
    public static function toBinary($value)
    {
        return base_convert($value, 16, 2);
    }


    /**
     * Merge address parts into a IPv6 address string
     * @param array $blocks
     * @return string
     */
    public static function addressBlocks2Address(array $blocks)
    {
        $revblocks = array_reverse($blocks);
        $hasnotnull = null;
        foreach($revblocks as &$block)
        {
            $test = preg_match('/^0+$/', $block)===1;
            if($test===true && $hasnotnull!==true)
            {
                $block = "";
                $hasnotnull = false;
            }
            else if($test===false && $hasnotnull===false)
            {
                $hasnotnull = true;
            }
        }
        unset($block);

        $nulledblocks = array_reverse($revblocks);
        $addressstr = implode(self::OCTETSEPARATOR(), $nulledblocks);

        $addressshort = preg_replace('/([^:]*)(:{3,})([^:]*)/', '$1::$3', $addressstr);

        if(preg_match('/[^:]:$/', $addressshort)===1)
        {
            $addressshort = $addressshort.":";
        }

        return $addressshort;
    }


    /**
     * Split a IPv6 address into an array of address parts
     * @param string $address
     * @return array
     */
    public static function address2addressBlocks($address)
    {
        $target = self::CIDRMAX()/self::OCTETLENGTHBIN();
        $parts = explode('::', $address);

        // Part 1
        $blocks1 = array();
        if(isset($parts[0]) && $parts[0]!="")
        {
            $blocks1 = explode(':', trim($parts[0], ':'));
        }
        $count1 = count($blocks1);

        // Part 2
        $blocks2 = array();
        if(isset($parts[1]) && $parts[1]!="")
        {
            $blocks2 = explode(':', trim($parts[1], ':'));
        }
        $count2 = count($blocks2);

        // Build result
        $result = array();
        $diff = $target-$count1-$count2;

        foreach($blocks1 as $block)
        {
            $result[] = $block;
        }

        for($i=0; $i<$diff; $i++)
        {
            $result[] = "0";
        }

        foreach($blocks2 as $block)
        {
            $result[] = $block;
        }

        return $result;
    }

}
