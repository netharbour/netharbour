<?
// Sample IP addresses
$ipaddr = '1.2.3.4/24'; // IPv4 with /24 netmask
$ipaddr = '1:2::3:4/64'; // IPv6 with /64 netmask

// Strip out the netmask, if there is one.
$cx = strpos($ipaddr, '/');
if ($cx)
{
  $subnet = (int)(substr($ipaddr, $cx+1));
  $ipaddr = substr($ipaddr, 0, $cx);
}
else $subnet = null; // No netmask present

// Convert address to packed format
$addr = inet_pton($ipaddr);

// Let's display it as hexadecimal format
foreach(str_split($addr) as $char) echo str_pad(dechex(ord($char)), 2, '0', STR_PAD_LEFT);
echo "<br />\n";

// Convert the netmask
if (is_integer($subnet))
{
  // Maximum netmask length = same as packed address
  $len = 8*strlen($addr);
  if ($subnet > $len) $subnet = $len;
 
  // Create a hex expression of the subnet mask
  $mask  = str_repeat('f', $subnet>>2);
  switch($subnet & 3)
  {
  case 3: $mask .= 'e'; break;
  case 2: $mask .= 'c'; break;
  case 1: $mask .= '8'; break;
  }
  $mask = str_pad($mask, $len>>2, '0');

  // Packed representation of netmask
  $mask = pack('H*', $mask);
}

// Display the netmask as hexadecimal
foreach(str_split($mask) as $char) echo str_pad(dechex(ord($char)), 2, '0', STR_PAD_LEFT);
?>