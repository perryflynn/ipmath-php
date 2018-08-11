<?php

namespace IPMath;


/**
 * This class contains all required functions to make math with IPv4 addresses
 */

class Ipv4Calculator extends IpBaseCalculator
{

    /**
     * Largest possible CIDR
     * @return int
     */
    public static function CIDRMAX()
    {
        return 32;
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
        return 8;
    }


    /**
     * Separator between ip address parts
     * @return string
     */
    public static function OCTETSEPARATOR()
    {
        return '.';
    }


    /**
     * Convert a number into a binary ip address part
     * @param int $number
     * @return string
     */
    public static function toBinary($number)
    {
        return str_pad(decbin((int)$number), self::OCTETLENGTHBIN(), '0', STR_PAD_LEFT);
    }


    /**
     * Convert a binary ip address part into a number
     * @param type $bin
     * @return type
     */
    public static function fromBinary($bin)
    {
        return (int)bindec($bin);
    }


    /**
     * Convert IP address parts into a IP address string
     * @param array $blocks
     * @return string
     */
    public static function addressBlocks2Address(array $blocks)
    {
        return implode(self::OCTETSEPARATOR(), $blocks);
    }


    /**
     * Convert a IP address string into IP Address parts
     * @param type $address
     * @return array
     */
    public static function address2addressBlocks($address)
    {
        return explode(self::OCTETSEPARATOR(), $address);
    }


    /**
     * Get the first host address of a network
     * @param string $ipaddress
     * @param int $cidr
     * @return string
     */
    public static function getHostAddressFirst($ipaddress, $cidr)
    {
        $ip = new Ipv4Address($ipaddress."/".$cidr);
        $binarynet = $ip->getNetworkAddressBinary(false);

        $binaryfirst = base_convert(((int)base_convert($binarynet, 2, 10))+1, 10, 2);
        return implode(self::OCTETSEPARATOR(), self::fromBinaryBlock(self::binstr2binoctets($binaryfirst)));
    }


    /**
     * Get the last host address of a network
     * @param string $ipaddress
     * @param int $cidr
     * @return string
     */
    public static function getHostAddressLast($ipaddress, $cidr)
    {
        $ip = new Ipv4Address($ipaddress."/".$cidr);
        $binarynet = $ip->getBroadcastAddressBinary(false);

        $binarylast = base_convert(((int)base_convert($binarynet, 2, 10))-1, 10, 2);
        return implode(self::OCTETSEPARATOR(), self::fromBinaryBlock(self::binstr2binoctets($binarylast)));
    }


    /**
     * Get the number of available host addresses in a CIDR
     * @param int $cidr
     * @return int
     */
    public static function getNetworkHostCount($cidr)
    {
        return pow(2, self::CIDRMAX()-$cidr)-2;
    }


    /**
     * Get the smallest network of two addresses
     * @param string $address1
     * @param string $address2
     * @param bool $hostiponly
     * @return \IPMath\Ipv4Address
     * @throws \InvalidArgumentException
     */
    public static function getSmallestNetwork($address1, $address2, $hostiponly=false)
    {
        $binary1 = self::binoctets2binstr(self::toBinaryBlock(explode(self::OCTETSEPARATOR(), $address1)));
        $binary2 = self::binoctets2binstr(self::toBinaryBlock(explode(self::OCTETSEPARATOR(), $address2)));

        if(strlen($binary1)!=self::CIDRMAX() || strlen($binary2)!=self::CIDRMAX())
        {
            throw new \InvalidArgumentException("No valid ip address given");
        }

        $cidr=0;
        for($i=0; $i<self::CIDRMAX(); $i++)
        {
            if($binary1[$i]===$binary2[$i])
            {
                $cidr++;
            }
            else
            {
                break;
            }
        }

        if($hostiponly===true)
        {
            while($cidr>0 && (self::isNetworkAddress($address1, $cidr) || self::isNetworkAddress($address2, $cidr) ||
                self::isBroadcastAddress($address1, $cidr) || self::isBroadcastAddress($address2, $cidr)))
            {
                $cidr--;
            }
        }

        $temp = new Ipv4Address($address1.self::CIDRSEPARATOR().$cidr);
        return new Ipv4Address($temp->getNetworkAddress(), $temp->getNetmask());
    }


    /**
     * Is the given IP address a boardcast address in its network
     * @param string $address
     * @param int $cidr
     * @return bool
     */
    public static function isBroadcastAddress($address, $cidr)
    {
        $temp = new Ipv4Address($address.self::CIDRSEPARATOR().$cidr);
        return $temp->getAddressBinary()===$temp->getBroadcastAddressBinary();
    }


    /**
     * Is the given IP address a network address in its network
     * @param string $address
     * @param int $cidr
     * @return bool
     */
    public static function isNetworkAddress($address, $cidr)
    {
        $temp = new Ipv4Address($address.self::CIDRSEPARATOR().$cidr);
        return $temp->getAddressBinary()===$temp->getNetmaskBinary();
    }


    /**
     * Create subnets by the number of required hosts or required networks
     * @param string $baseaddress
     * @param int $cidr
     * @param int $numnetworks
     * @param int $numhosts
     * @param bool $preferhosts
     * @return generator of \IPMath\IPv4Address
     * @throws \InvalidArgumentException
     */
    public static function createSubnetsDynamic($baseaddress, $cidr, $numnetworks=null, $numhosts=null, $preferhosts=null)
    {
        $availbits = self::CIDRMAX()-$cidr;
        $netbits = 1;
        $hostbits = 1;

        if($numnetworks!==null)
        {
            $netbits = ceil(log($numnetworks)/log(2));
        }

        if($numhosts!==null)
        {
            $hostbits = ceil(log($numhosts+2)/log(2));
        }

        if($numnetworks!==null && $numhosts===null)
        {
            $preferhosts=true;
        }
        else if($numnetworks===null && $numhosts!==null)
        {
            $preferhosts=false;
        }

        if($availbits > ($netbits+$hostbits))
        {
            if($preferhosts===true)
            {
                $hostbits += $availbits-($netbits+$hostbits);
            }
            else if($preferhosts===false)
            {
                $netbits += $availbits-($netbits+$hostbits);
            }
            else
            {
                $diff = ($availbits-($netbits+$hostbits))/2;
                $netbits += floor($diff);
                $hostbits += ceil($diff);
            }
        }
        else if($availbits < ($netbits+$hostbits))
        {
            if($preferhosts===true && ($netbits-($availbits-$hostbits))>0)
            {
                $netbits -= ($hostbits+$netbits)-$availbits;
            }
            else if($preferhosts===false && ($hostbits-($availbits-$netbits))>0)
            {
                $hostbits -= ($hostbits+$netbits)-$availbits;
            }
            else if($preferhosts===null)
            {
                $diff = (($netbits+$hostbits)-$availbits)/2;
                $diffn = floor($diff);
                $diffh = ceil($diff);

                if($netbits-$diffn > 0)
                {
                    $netbits -= $diffn;
                }
                if($hostbits-$diffh > 0)
                {
                    $hostbits -= $diffh;
                }
            }

            if($availbits < ($netbits+$hostbits))
            {
                throw new \InvalidArgumentException("Not enough bits available for the given settings");
            }
        }

        $targetcidr = $cidr+$netbits;
        return self::createSubnets($baseaddress, $cidr, $targetcidr);
    }


    /**
     * Create subnets by a target CIDR
     * @param string $baseaddress
     * @param int $cidr
     * @param int $targetcidr
     * @return generator of \IPMath\IPv4Address
     * @throws \InvalidArgumentException
     */
    public static function createSubnets($baseaddress, $cidr, $targetcidr)
    {
        $netbits = $targetcidr-$cidr;
        if($netbits<1)
        {
            throw new \InvalidArgumentException("Target CIDR must be greater than CIDR");
        }

        $sourceaddr = new Ipv4Address($baseaddress.self::CIDRSEPARATOR().$cidr);
        $netbinary = base_convert(substr($sourceaddr->getNetworkAddressBinary(false), 0, $targetcidr), 2, 10);
        for($i=0; $i<pow(2, $netbits); $i++)
        {
            $newbinstr = base_convert($netbinary, 10, 2).str_pad('', self::CIDRMAX()-$targetcidr, '0', STR_PAD_LEFT);
            $newnetadr = implode(self::OCTETSEPARATOR(), self::fromBinaryBlock(self::binstr2binoctets($newbinstr)));
            $newnet = new Ipv4Address($newnetadr.self::CIDRSEPARATOR().$targetcidr);
            $netbinary++;
            yield $newnet;
        }
    }

}
