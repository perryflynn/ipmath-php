<?php

namespace IPMath;

/**
 * The basic interface for the IPv4 and IPv6 classes
 */

interface ICalculator
{
    
    // Properties
    public static function CIDRSEPARATOR();
    public static function CIDRMIN();
    public static function CIDRMAX();
    public static function OCTETLENGTHBIN();
    public static function OCTETSEPARATOR();
    
    // Prettyfy
    public static function addressBlocks2Address(array $blocks);
    public static function address2addressBlocks($address);
    
    // Netmask tools
    public static function cidr2netmaskbinary($cidr);
    public static function cidr2wildcardbinary($cidr);
    public static function cidr2netmask($cidr);
    public static function netmask2cidr($netmask);
    public static function cidr2wildcard($cidr);
    public static function getCidrNetmaskAll();
    
    // Binary tools
    public static function toBinary($value);
    public static function fromBinary($value);
    public static function toBinaryBlock(array $blocks);
    public static function fromBinaryBlock(array $blocks);
    public static function binstr2binoctets($binstr);
    public static function binoctets2binstr(array $octets, $separator=false);
    
}
