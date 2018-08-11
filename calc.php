<?php

// Load classes
include_once __DIR__."/src/IPMath/ICalculator.php";
include_once __DIR__."/src/IPMath/IpBaseCalculator.php";
include_once __DIR__."/src/IPMath/Ipv4Calculator.php";
include_once __DIR__."/src/IPMath/Ipv4Address.php";
include_once __DIR__."/src/IPMath/Ipv6Calculator.php";


// Load namespaces
use IPMath\Ipv4Calculator;
use IPMath\Ipv4Address;
use IPMath\Ipv6Calculator;


// Helper functions to pretty-print the results
function pad($length, $str)
{
    return str_pad($str, $length, ' ', STR_PAD_RIGHT);
}

function printnet(Ipv4Address $ip)
{
    echo "#-> Info about ".$ip->getAddress()."\n";
    echo "Address:     ".pad(18, $ip)."   ".$ip->getAddressBinary()."\n";
    echo "Netmask:     ".pad(18, $ip->getNetmask())."   ".$ip->getNetmaskBinary()."\n";
    echo "Wildcard:    ".pad(18, $ip->getWildcard())."   ".$ip->getWildcardBinary()."\n";
    echo "Network:     ".pad(18, $ip->getNetworkAddress())."   ".$ip->getNetworkAddressBinary()."\n";
    echo "Broadcast:   ".pad(18, $ip->getBroadcastAddress())."   ".$ip->getBroadcastAddressBinary()."\n";
    echo "Host IPs:    ".Ipv4Calculator::getNetworkHostCount($ip->getCidr())."\n";
    echo "First IP:    ".pad(18, $ip->getNetworkHostAddressFirst())."\n";
    echo "Last IP:     ".pad(18, $ip->getNetworkHostAddressLast())."\n";
    echo "\n";
}

function printshort(Ipv4Address $ip)
{
    echo "Net: ".pad(18, $ip->getNetworkAddress().Ipv4Calculator::CIDRSEPARATOR().$ip->getCidr())."; ".
        pad(15, $ip->getNetworkHostAddressFirst())." - ".pad(15, $ip->getNetworkHostAddressLast())." (".$ip->getNetworkHostCount()."); ".
        "BC: ".pad(15, $ip->getBroadcastAddress()).
        "\n";
}

echo "<pre>";


// Test data
$testsmallestcidr = array(
    array("192.168.42.10", "192.168.42.11"), 
    array("172.23.8.10", "172.23.4.15"), 
    array("8.8.8.8", "8.8.4.4"), 
    array("192.168.1.1", "192.168.1.255")
);

$testips = array(
    "192.168.42.123/24", 
    "10.175.191.219/26", 
    "10.175.191.175/27", 
    "172.23.8.42/23"
);


// Print infos about some ip addresses and networks
foreach($testips as $testip)
{
    $ip = new Ipv4Address($testip);
    printnet($ip);
}


// Calculate the smalles common network for two ip addresses
echo "#-> Smalles subnet of two IPs\n";
foreach($testsmallestcidr as $addr)
{
    $temp = Ipv4Calculator::getSmallestNetwork($addr[0], $addr[1], false);
    echo pad(15, $addr[0])." + ".pad(15, $addr[1])." = /".pad(2, $temp->getCidr())."; Net: ".pad(15, $temp->getNetworkAddress())."; Bcast: ".pad(15, $temp->getBroadcastAddress())."\n";
}

echo "\n";

echo "#-> Smalles subnet of two IPs, Host IPs only\n";
foreach($testsmallestcidr as $addr)
{
    $temp = Ipv4Calculator::getSmallestNetwork($addr[0], $addr[1], true);
    echo pad(15, $addr[0])." + ".pad(15, $addr[1])." = /".pad(2, $temp->getCidr())."; Net: ".pad(15, $temp->getNetworkAddress())."; Bcast: ".pad(15, $temp->getBroadcastAddress())."\n";
}

echo "\n";


// Print all possible IPv5 network masks
echo "#-> Possible IPv4 Networkmasks\n";
foreach(Ipv4Calculator::getCidrNetmaskAll() as $cidr => $netmask)
{
    echo "/".str_pad($cidr, 2, ' ', STR_PAD_RIGHT).
        " = ".str_pad($netmask, 15, ' ', STR_PAD_RIGHT).
        "\n";
}

echo "\n";


// Print all possible IPv6 network masks
echo "#-> Possible IPv6 Networkmasks\n";

foreach(Ipv6Calculator::getCidrNetmaskAll() as $cidr => $netmask)
{
    echo "/".str_pad($cidr, 3, ' ', STR_PAD_RIGHT).
        " = ".$netmask. 
        //" = ". json_encode(Ipv6Calculator::address2addressBlocks($netmask)).
        "\n";
}


// Create new subnets from a given network
$subnets = iterator_to_array(Ipv4Calculator::createSubnetsDynamic("192.168.0.0", 16, null, 512));

echo "\n";
echo "#-> Create ".count($subnets)." subnets\n";
echo "\n";

foreach($subnets as $ip)
{
    printshort($ip);
}

echo "</pre>";

