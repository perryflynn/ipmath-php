<?php

namespace IPMath;

/**
 * This class contains all magic which is the
 * same on both IP versions
 */

abstract class IpBaseCalculator implements ICalculator
{

    /**
     * Converts a CIDR to binary network mask format
     * @param int $cidr
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function cidr2netmaskbinary($cidr)
    {
        $intcidr = (int)$cidr;
        if($intcidr>static::CIDRMAX() || $intcidr<static::CIDRMIN())
        {
            throw new \InvalidArgumentException("Expected number between ".static::CIDRMIN()." and ".static::CIDRMAX());
        }

        return str_pad("", $intcidr, '1', STR_PAD_RIGHT).str_pad("", static::CIDRMAX()-$intcidr, '0', STR_PAD_RIGHT);
    }


    /**
     * Converts a CIDR to a binary wildcard address format
     * @param int $cidr
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function cidr2wildcardbinary($cidr)
    {
        $intcidr = (int)$cidr;
        if($intcidr>static::CIDRMAX() || $intcidr<static::CIDRMIN())
        {
            throw new \InvalidArgumentException("Expected number between ".static::CIDRMIN()." and ".static::CIDRMAX());
        }

        return str_pad("", $intcidr, '0', STR_PAD_RIGHT).str_pad("", static::CIDRMAX()-$intcidr, '1', STR_PAD_RIGHT);
    }


    /**
     * Converts the parts of a ip address to binary format
     * @param array $blocks
     * @return array
     */
    public static function toBinaryBlock(array $blocks)
    {
        foreach($blocks as &$block)
        {
            $block = static::toBinary($block);
        }
        unset($block);
        return $blocks;
    }


    /**
     * Converts the binary ip address parts to the original format
     * @param array $blocks
     * @return array
     */
    public static function fromBinaryBlock(array $blocks)
    {
        foreach($blocks as &$block)
        {
            $block = static::fromBinary($block);
        }
        unset($block);
        return $blocks;
    }


    /**
     * Splits a binary ip address string into its parts
     * @param string $binstr
     * @return array
     */
    public static function binstr2binoctets($binstr)
    {
        return str_split($binstr, static::OCTETLENGTHBIN());
    }


    /**
     * Merge the parts of a binary ip address into a string
     * @param array $octets
     * @param string $separator
     * @return string
     */
    public static function binoctets2binstr(array $octets, $separator=false)
    {
        return implode($separator ? static::OCTETSEPARATOR() : '', $octets);
    }


    /**
     * Converts a CIDR into a network mask address
     * @param int $cidr
     * @return string
     */
    public static function cidr2netmask($cidr)
    {
        $binary = static::cidr2netmaskbinary($cidr);
        $blocks = static::fromBinaryBlock(static::binstr2binoctets($binary));

        return static::addressBlocks2Address($blocks);
    }


    /**
     * Converts a network mask address into a CIDR
     * @param string $netmask
     * @return int
     * @throws \InvalidArgumentException
     */
    public static function netmask2cidr($netmask)
    {
        $blocks = static::toBinaryBlock(explode(static::OCTETSEPARATOR(), $netmask));
        $binstr = implode('', $blocks);

        if(preg_match('/^([1]+[0]+|0+|1+)$/', $binstr)!==1 || strlen($binstr)!=static::CIDRMAX())
        {
            throw new \InvalidArgumentException("Invalid network mask");
        }

        return substr_count($binstr, '1');
    }


    /**
     * Converts a CIDR into a wildcard address
     * @param int $cidr
     * @return string
     */
    public static function cidr2wildcard($cidr)
    {
        $binary = static::cidr2wildcardbinary($cidr);
        $blocks = static::fromBinaryBlock(static::binstr2binoctets($binary));

        return implode(static::OCTETSEPARATOR(), $blocks);
    }


    /**
     * Gets all possible CIDR and network mask in a array
     * @return array
     */
    public static function getCidrNetmaskAll()
    {
        $result = array();
        for($i=static::CIDRMIN(); $i<=static::CIDRMAX(); $i++)
        {
            $result[$i] = static::cidr2netmask($i);
        }
        return $result;
    }

}
