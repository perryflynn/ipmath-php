<?php

namespace IPMath;

/**
 * This class represents a IPv4 Address
 */

class Ipv4Address
{

    /**
     * IP Address
     * @var string
     */
    protected $address;

    /**
     * CIDR
     * @var int
     */
    protected $cidr;


    /**
     * Call the constructor with the CIDR inside the address variable or with a
     * network mask address as second argument
     * @param string $address
     * @param string $netmask
     * @throws \InvalidArgumentException
     */
    public function __construct($address, $netmask=null)
    {
        if(strpos($address, Ipv4Calculator::CIDRSEPARATOR())===false && $netmask===null)
        {
            // IP address, no cidr, no netmask
            $this->address = $address;
            $this->cidr = Ipv4Calculator::CIDRMAX();
        }
        else if(strpos($address, Ipv4Calculator::CIDRSEPARATOR())!==false && $netmask===null)
        {
            // IP address, cidr, no netmask
            $temp = explode(Ipv4Calculator::CIDRSEPARATOR(), $address);
            $this->address = $temp[0];
            $this->cidr = (int)$temp[1];
        }
        else if($netmask!==null)
        {
            // IP address, no cidr, netmask
            $this->address = $address;
            $this->cidr = Ipv4Calculator::netmask2cidr($netmask);
        }
        else
        {
            throw new \InvalidArgumentException("Invalid call of constructor");
        }
    }


    /**
     * Get the IP Address in CIDR Format
     * @return string
     */
    public function __toString()
    {
        return $this->address.Ipv4Calculator::CIDRSEPARATOR().$this->cidr;
    }


    /**
     * Get the IP Address
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }


    /**
     * Get the CIDR
     * @return int
     */
    public function getCidr()
    {
        return $this->cidr;
    }


    /**
     * Get the network mask address as binary parts
     * @return array
     */
    public function getNetmaskBinaryOctets()
    {
        return Ipv4Calculator::binstr2binoctets(Ipv4Calculator::cidr2netmaskbinary($this->cidr));
    }


    /**
     * Get the network mask address as binary string
     * @param bool $separator
     * @return string
     */
    public function getNetmaskBinary($separator=true)
    {
        return implode($separator ? Ipv4Calculator::OCTETSEPARATOR() : '', $this->getNetmaskBinaryOctets());
    }


    /**
     * Get the network mask address
     * @return string
     */
    public function getNetmask()
    {
        return Ipv4Calculator::cidr2netmask($this->cidr);
    }


    /**
     * Get the wildcard address
     * @return string
     */
    public function getWildcard()
    {
        return Ipv4Calculator::cidr2wildcard($this->cidr);
    }


    /**
     * Get the wildcard address as binary parts
     * @return array
     */
    public function getWildcardBinaryOctets()
    {
        return Ipv4Calculator::binstr2binoctets(Ipv4Calculator::cidr2wildcardbinary($this->cidr));
    }


    /**
     * Get the wildcard address as binary string
     * @param bool $separator
     * @return string
     */
    public function getWildcardBinary($separator=true)
    {
        return implode($separator ? Ipv4Calculator::OCTETSEPARATOR() : '', $this->getWildcardBinaryOctets());
    }


    /**
     * Get the IP address parts as array
     * @return array
     */
    public function getAddressOctets()
    {
        return explode(Ipv4Calculator::OCTETSEPARATOR(), $this->address);
    }


    /**
     * Get the IP address parts as a binary array
     * @return array
     */
    public function getAddressOctetsBinary()
    {
        return Ipv4Calculator::toBinaryBlock($this->getAddressOctets());
    }


    /**
     * Get the IP address as a binary string
     * @param bool $separator
     * @return string
     */
    public function getAddressBinary($separator=true)
    {
        return implode($separator ? Ipv4Calculator::OCTETSEPARATOR() : '', $this->getAddressOctetsBinary());
    }


    /**
     * Get the network address parts as a binary array
     * @return array
     */
    public function getNetworkAddressOctetsBinary()
    {
        $binaryaddr = $this->getAddressBinary(false);
        $binarynetm = $this->getNetmaskBinary(false);

        $binnet = $binaryaddr & $binarynetm;
        return Ipv4Calculator::binstr2binoctets($binnet);
    }


    /**
     * Get the network address as a binary string
     * @param bool $separator
     * @return string
     */
    public function getNetworkAddressBinary($separator=true)
    {
        return implode($separator ? Ipv4Calculator::OCTETSEPARATOR() : '', $this->getNetworkAddressOctetsBinary());
    }


    /**
     * Get the network address
     * @return string
     */
    public function getNetworkAddress()
    {
        $blocks = Ipv4Calculator::fromBinaryBlock($this->getNetworkAddressOctetsBinary());
        return implode(Ipv4Calculator::OCTETSEPARATOR(), $blocks);
    }


    /**
     * Get the broadcast address parts as a binary array
     * @return array
     */
    public function getBroadcastAddressOctetsBinary()
    {
        $binaryaddr = $this->getAddressBinary(false);
        $binarywild = $this->getWildcardBinary(false);

        $binbc = $binaryaddr | $binarywild;
        return Ipv4Calculator::binstr2binoctets($binbc);
    }


    /**
     * Get the broadcast address as a binary string
     * @param bool $separator
     * @return string
     */
    public function getBroadcastAddressBinary($separator=true)
    {
        return implode($separator ? Ipv4Calculator::OCTETSEPARATOR() : '', $this->getBroadcastAddressOctetsBinary());
    }


    /**
     * Get the broadcast address
     * @return string
     */
    public function getBroadcastAddress()
    {
        $blocks = Ipv4Calculator::fromBinaryBlock($this->getBroadcastAddressOctetsBinary());
        return implode(Ipv4Calculator::OCTETSEPARATOR(), $blocks);
    }


    /**
     * Get the first host address in the network
     * @return string
     */
    public function getNetworkHostAddressFirst()
    {
        return Ipv4Calculator::getHostAddressFirst($this->getNetworkAddress(), $this->getCidr());
    }


    /**
     * Get the last host address in the network
     * @return string
     */
    public function getNetworkHostAddressLast()
    {
        return Ipv4Calculator::getHostAddressLast($this->getNetworkAddress(), $this->getCidr());
    }


    /**
     * Get the number of host addresses available in the network
     * @return int
     */
    public function getNetworkHostCount()
    {
        return Ipv4Calculator::getNetworkHostCount($this->getCidr());
    }

}
