<?
define ('V6REG', "/^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/");

class Netblock
{
	var $ip = "invalid";
	var $n_ip;
	var $bin_ip;
	var $long_ip;
	var $long_network;
	var $netmask;
	var $netmask_ip;
	var $subnet_size;
	
	var $wildcard;
	
	var $network;
	var $bin_network;
	
	var $broadcast;
	var $bin_broadcast;
	
	var $hostmin;
	var $hostmax;
	var $hostPerNet;
	var $class;
	var $family;
	var $is_negative=FALSE;
	
	var $max_bits;
	
	var $description = "";
	
	var $split = array();
	var $query;
	
	function __construct($ip="", $desc="")
	{
		if ($ip!="")
		{
			$this->set_desc($desc);
			$this->set_IP($ip);
		}
	}
	
	function set_IP($ip, $family="")
	{
		$is_negative = false;
		$this->ip = $ip;
		$match = explode("/", $this->ip);
			
		$this->n_ip = $match[0];
		$this->netmask = $match[1];
		
		if(strpos($this->n_ip, ':') === false && strpos($this->n_ip, '.') === false)
		{
			if($family == 4)
			{
				$this->n_ip = long2ip($this->n_ip);
				$this->ip = $this->n_ip."/".$this->netmask;
			}
			else if ($family ==6)
			{
				$this->n_ip = Netblock::inet_dtop($this->n_ip);
				$this->ip = $this->n_ip."/".$this->netmask;
			}
			else 
			{
				$this->n_ip = "0";
			}
		}
		
		$bin = "";
		
		if(preg_match('/^\d{1,3}(\.\d{1,3}){3,3}/',$this->n_ip))
		{
			$this->family = 4;
			$this->max_bits = 32;
			
			
			$ip_octets = explode(".", $this->n_ip);
			foreach ($ip_octets as $id=>$num)
			{
				$bin .= str_pad(decbin($num), 8, 0, STR_PAD_LEFT);
			}
		}
		if(preg_match(V6REG,$this->n_ip))
		{
			$this->family = 6;
			$this->max_bits = 128;
			
			$hex = $this->expand_hex_ip6($this->n_ip);
			$ip_dual_octets = explode(":", $hex);
			
			foreach ($ip_dual_octets as $id=>$num)
			{
				$bin .= str_pad(base_convert($num, 16, 2), 16, 0, STR_PAD_LEFT);
			}
			
			/*echo $this->n_ip."/".$this->netmask."<br/> ".$hex."<br/>";
			echo $bin."<br/>";
			echo $this->bin_to_ip6($bin)."<br/>";
			exit;*/
		}
		
		
		if ($this->family == 4)
		{
			$this->bin_ip = $bin;
			//print "Address. ". $this->n_ip." -> ".chunk_split($bin,8,".")."<br/>";
			$this->long_ip = sprintf("%u", ip2long($this->n_ip));
				
			$bin = str_pad(str_pad("", $this->netmask, 1), $this->max_bits, 0);
			$this->netmask_ip = long2ip(bindec($bin));
				
			//print "NetMask. ". $this->netmask_ip." -> ".chunk_split($bin,8,".")."<br/>" ;
				
			$bin = str_pad(str_pad("", 32-$this->netmask, 1), $this->max_bits, 0, STR_PAD_LEFT);
			$this->wildcard = long2ip(bindec($bin));
				
			$this->subnet_size = bindec($bin) +1;
				
			//print "Wildcard. ". $this->wildcard." -> ".chunk_split($bin,8,".")."<br/>" ;
				
			$bin = str_pad(substr($this->bin_ip, 0, $this->netmask), $this->max_bits, 0, STR_PAD_RIGHT);
			$this->network = long2ip(bindec($bin));
			$this->bin_network = $bin;
			$this->long_network = bindec($bin);
			//print "Network. ". $this->network." -> ".chunk_split($bin,8,".")."<br/>" ;
				
			$bin = str_pad(substr($this->bin_ip, 0, $this->netmask), $this->max_bits, 1, STR_PAD_RIGHT);
			$this->broadcast = long2ip(bindec($bin));
			$this->bin_broadcast = $bin;
				
			//print "Broadcast. ". $this->broadcast." -> ".chunk_split($bin,8,".")."<br/>" ;
			
			
			if($this->netmask == 32)
			{
				$this->hostmin = $this->network;
				$this->hostmax = $this->network;
				$this->hostPerNet = 1;
			}
			else
			{
				$bin = decbin(ip2long($this->network)+1);
				$this->hostmin = long2ip(bindec($bin));
				
				//print "HostMin. ". $this->hostmin." -> ".chunk_split($bin,8,".")."<br/>" ;
				
				$bin = decbin(ip2long($this->broadcast)-1);
				$this->hostmax = long2ip(bindec($bin));
					
				//print "HostMax. ". $this->hostmax." -> ".chunk_split($bin,8,".")."<br/>" ;
					
				$this->hostPerNet = (bindec(str_pad("",($this->max_bits-$this->netmask),1))-1);
				//print "Host/Net. ". (bindec(str_pad("",(32-$this->netmask),1))-1)."<br/><br/>";
			}
				
				
			
			if($this->description=="")
			{
				$this->description = "IPV4 ".$this->n_ip;	
			}
		}
		else if ($this->family == 6)
		{
			
			$this->bin_ip = $bin;
			$this->long_ip = $this->inet_ptod($this->bin_to_ip6($bin));
			//print "Address. ". $this->n_ip." -> ".chunk_split($bin,16,".")."<br/>";
			
			$bin = str_pad(str_pad("", $this->netmask, 1), $this->max_bits, 0);
			$this->netmask_ip = $this->bin_to_ip6($bin);
				
			//print "NetMask. ". $this->netmask_ip." -> ".chunk_split($bin,16,".")."<br/>" ;
			
				
			$bin = str_pad(str_pad("", $this->max_bits-$this->netmask, 1), $this->max_bits, 0, STR_PAD_LEFT);
			$this->wildcard = $this->bin_to_ip6($bin);
				
			$this->subnet_size = $this->inet_ptod($this->bin_to_ip6($bin)) +1;
				
			//print "Wildcard. ". $this->wildcard." -> ".chunk_split($bin,16,".")." ".$this->subnet_size."<br/>" ;
			
			$bin = str_pad(substr($this->bin_ip, 0, $this->netmask), $this->max_bits, 0, STR_PAD_RIGHT);
			$this->network = $this->bin_to_ip6($bin);
			$this->bin_network = $bin;
			$this->long_network = $this->inet_ptod($this->network);
				
			//print "Network. ". $this->network." -> ".chunk_split($bin,16,".")."<br/>" ;
				
			$bin = str_pad(substr($this->bin_ip, 0, $this->netmask), $this->max_bits, 1, STR_PAD_RIGHT);
			$this->broadcast = $this->bin_to_ip6($bin);
			$this->bin_broadcast = $bin;
				
			//print "Broadcast. ". $this->broadcast." -> ".chunk_split($bin,16,".")."<br/>" ;
				
			
			if($this->netmask == 128)
			{
				$this->hostmin = $this->network;
				$this->hostmax = $this->network;
				$this->hostPerNet = 1;
			}
			else
			{
				$bin = $this->binary_add($this->ip6_to_bin($this->network), "1");
				$this->hostmin = $this->bin_to_ip6($bin);
				//print "HostMin. ". $this->hostmin." -> ".chunk_split($bin,16,".")."<br/>" ;
			
			
				$bin = $this->ip6_to_bin($this->broadcast);
				$this->hostmax = $this->bin_to_ip6($bin);
				
				//print "HostMax. ". $this->hostmax." -> ".chunk_split($bin,8,".")."<br/>" ;
			
			
				$this->hostPerNet = $this->inet_ptod($this->bin_to_ip6(str_pad("",($this->max_bits-$this->netmask),1)));
			
				//print "Host/Net. ". $this->hostPerNet."<br/><br/>";
			}
				
			
		}
		else
		{ echo "INVALID";}
		
	}
	
	
	function set_length($length)
	{
		$this->netmask = $length;
	}
	
	function get_length()
	{
		return $this->netmask;
	}
	
	function get_description()
	{
		return $this->description;
	}
	
	function set_desc($desc)
	{
		$this->description = $desc;
	}
	
	function get_IP()
	{
		return $this->ip;
	}
	
	function get_family()
	{
		return $this->family;	
	}
	
	function get_string()
	{
		return $this->ip;
	}
	
	function get_binary()
	{
		if($this->family == 4)
		{
			return rtrim(chunk_split($this->bin_ip,8,"."), ".");
		}
		else if ($this->family == 6){
			return rtrim(chunk_split($this->bin_ip,16,"."), ".");
		}
	}
	
	function get_long()
	{
		$match = explode (".", $this->long_network);
		return $match[0];
	}
	
	function get_long_ip()
	{
		$match = explode (".", $this->long_ip);
		return $match[0];
	}
	
	function get_netmask()
	{
		return $this->netmask_ip;
	}
	
	function get_wildcard()
	{
		return $this->wildcard;
	}
	
	function get_network()
	{
		return $this->network;
	}
	
	function get_broadcast()
	{
		return $this->broadcast;
	}
	
	function get_hostmin()
	{
		return $this->hostmin;
	}
	
	function get_hostmax()
	{
		return $this->hostmax;
	}
	
	function get_hostPerNet()
	{
		return $this->hostPerNet;
	}
	
	function get_ipPerNet()
	{
		if ($this->family == 6)
		{
			return bcpow(2, 128-$this->netmask);
		}
		else
		{
			return bcpow(2, 32-$this->netmask);
		}
	}
	
	function get_is_negative()
	{
		return $is_negative;
	}
	
	function split_IP($subnet = 0)
	{
		if (isset($this->ip))
		{
			$divisor = pow(2, $subnet - $this->netmask);
			$split_arr = array();
			if ($divisor < 1)
			{
				echo "SUBNET SPLIT IS INVALID";
				exit;	
			}
			
			if ($divisor > 10000)
			{
				echo "This is very CPU intesive, you have over 10000 splits, try a smaller amounts first thanks!";
				exit;
			}
			
			$add = "0";
			
			if($this->family ==4)
			{
				$increment = Netblock::binary_add(str_pad("", 32-$subnet, 1, STR_PAD_RIGHT), "1");
				
				for ($i = 0; $i<$divisor; $i++)
				{
					$bin = Netblock::binary_add($this->bin_network, $add);
					$add = Netblock::binary_add($increment, $add);
						
					//echo $bin." ".long2ip(bindec($bin))."/".$subnet."<br/>";
					$split_arr[$i] = long2ip(bindec($bin))."/".$subnet;
				}
			}
			else if ($this->family ==6)
			{
				$increment = Netblock::binary_add(str_pad("", 128-$subnet, 1, STR_PAD_RIGHT), "1");
				
				for ($i = 0; $i<$divisor; $i++)
				{
					$bin = Netblock::binary_add($this->bin_network, $add);
					$add = Netblock::binary_add($increment, $add);
						
					//echo $bin." ".Netblock::bin_to_ip6(($bin))."/".$subnet."<br/>";	
					$split_arr[$i] = Netblock::bin_to_ip6(($bin))."/".$subnet;
				}
			}
			
			if (!empty($split_arr))
			{
				$this->split = $split_arr;
				return $split_arr;
			}
			else
			{
				echo "NOT SPLITT-ABLE";
			}
			
		}
	}
	
	function join_IP()
	{
		
	}
	
	/*function check_class($bin_net)
	{
		if (preg_match('/^0/',$bin_net)){
			$class="A";
		}elseif (preg_match('/^10/',$bin_net)){
			$class="B";
		}elseif (preg_match('/^110/',$bin_net)){
			$class="C";
		}elseif (preg_match('/^1110/',$bin_net)){
			$class="D";
		}else{
			$class="E";
		}
	
	}*/
	
	function dec_to_bit($dec)
	{
		$string = decbin($dec);
		$length=0;
		for ($i=0; $i<strlen($string); $i++)
		{
			if(substr($string, $i, 1) == "1")
			{
				$length++;
			}
			else {
			break;	
			}
		}
		
		$bit = 32-$length;
		return $bit;
	}
	
	function inet_ptod($ip_address)
	{
		// IPv4 address
		if (strpos($ip_address, ':') === false && strpos($ip_address, '.') !== false) {
			$ip_address = '::' . $ip_address;
		}
	
		// IPv6 address
		if (strpos($ip_address, ':') !== false) {
			$network = inet_pton($ip_address);
			$parts = unpack('N*', $network);
	
			foreach ($parts as &$part) {
					if ($part < 0) {
							$part = bcadd((string) $part, '4294967296');
					}
	
					if (!is_string($part)) {
							$part = (string) $part;
					}
			}
	
			$decimal = $parts[4];
			$decimal = bcadd($decimal, bcmul($parts[3], '4294967296'));
			$decimal = bcadd($decimal, bcmul($parts[2], '18446744073709551616'));
			$decimal = bcadd($decimal, bcmul($parts[1], '79228162514264337593543950336'));
	
			return $decimal;
		}
	
		// Decimal address
		return $ip_address;
	}
	
	/**
	 * Convert an IP address from decimal format to presentation format
	 *
	 * @param string $decimal An IP address in IPv4, IPv6 or decimal notation
	 * @return string The IP address in presentation format
	 */
	function inet_dtop($decimal)
	{
		// IPv4 or IPv6 format
		if (strpos($decimal, ':') !== false || strpos($decimal, '.') !== false) {
			return $decimal;
		}
	
		// Decimal format
		$parts = array();
		$parts[1] = bcdiv($decimal, '79228162514264337593543950336', 0);
		$decimal = bcsub($decimal, bcmul($parts[1], '79228162514264337593543950336'));
		$parts[2] = bcdiv($decimal, '18446744073709551616', 0);
		$decimal = bcsub($decimal, bcmul($parts[2], '18446744073709551616'));
		$parts[3] = bcdiv($decimal, '4294967296', 0);
		$decimal = bcsub($decimal, bcmul($parts[3], '4294967296'));
		$parts[4] = $decimal;
	
		foreach ($parts as &$part) {
			if (bccomp($part, '2147483647') == 1) {
					$part = bcsub($part, '4294967296');
			}
	
			$part = (int) $part;
		}
	
		$network = pack('N4', $parts[1], $parts[2], $parts[3], $parts[4]);
		$ip_address = inet_ntop($network);
	
		// Turn IPv6 to IPv4 if it's IPv4
		if (preg_match('/^::\d+.\d+.\d+.\d+$/', $ip_address)) {
			return substr($ip_address, 2);
		}
	
		return $ip_address;
	}
	
	/**
     * Converts an IPv6 address from Binary into Hex representation.
     *
     * @param String $bin the IP address as binary
     *
     * @return String the uncompressed Hex representation
     * @access private
     @ @since 1.1.0
     */
	function bin_to_ip6($bin,$short=true)
    {
        $ip = "";

        if (strlen($bin) < 128) {

            $bin = str_pad($bin, 128, '0', STR_PAD_LEFT);

        }

        $parts = str_split($bin, "16");

        foreach ( $parts as $v ) {

        	$str = base_convert($v, 2, 16);
		if ($short === FALSE) {
			$str = str_pad($str, 4, '0', STR_PAD_LEFT);
		}
            	$ip .= $str.":";

        }
        $ip = substr($ip, 0, -1);

        return $ip;
    }
	
	function ip6_to_bin($ip) 
    {
        $binstr = '';

        $ip = $this->expand_hex_ip6($ip);

        $parts = explode(':', $ip);

        foreach ( $parts as $v ) {

            $str     = base_convert($v, 16, 2);
            $binstr .= str_pad($str, 16, '0', STR_PAD_LEFT);

        }

        return $binstr;
    }
	
	function expand_hex_ip6($ip)
	{
		$match = explode("/", $ip);
		$addr = inet_pton($match[0]);
		$hex = "";
		foreach(str_split($addr) as $char)
		{
			$hex .= str_pad(dechex(ord($char)), 2, '0', STR_PAD_LEFT);
		}
		$hex = rtrim(chunk_split($hex,4,":"), ":");
		return $hex;
	}
	
	function binary_add($bin, $add)
	{
		$newString = $bin;
		
		$add = str_pad($add, max(strlen($newString),strlen($add)), '0', STR_PAD_LEFT);
		$newString = str_pad($newString, max(strlen($newString),strlen($add)), '0', STR_PAD_LEFT);
		
		$carry = false;
		
		for ($i =0; $i<strlen($add); $i++)	
		{
			if (substr($add, -($i+1), 1) == "1" && substr($newString, -($i+1), 1) == "1")
			{
				if(!$carry)
				{
					$newString = substr_replace($newString, "0", -($i+1), 1);
				}
				else
				{
					$newString = substr_replace($newString, "1", -($i+1), 1);
				}
				$carry = true;
			}
			else if (substr($add, -($i+1), 1) == "0" && substr($newString, -($i+1), 1) == "1")
			{
				if(!$carry)
				{
					$newString = substr_replace($newString, "1", -($i+1), 1);
					$carry = false;
				}
				else
				{
					$newString = substr_replace($newString, "0", -($i+1), 1);
					$carry = true;
				}
			}
			
			else if (substr($add, -($i+1), 1) == "0" && substr($newString, -($i+1), 1) == "0")
			{
				if(!$carry)
				{
					$newString = substr_replace($newString, "0", -($i+1), 1);
				}
				else
				{
					$newString = substr_replace($newString, "1", -($i+1), 1);
				}
				$carry = false;
			}
			
			else if (substr($add, -($i+1), 1) == "1" && substr($newString, -($i+1), 1) == "0")
			{
				if(!$carry)
				{
					$newString = substr_replace($newString, "1", -($i+1), 1);
					$carry = false;
				}
				else
				{
					$newString = substr_replace($newString, "0", -($i+1), 1);
					$carry = true;
				}
			}
		}
		
		if ($carry)
		{
			$newString = str_pad($newString, strlen($newString)+1, '1', STR_PAD_LEFT);	
		}
		return $newString;
	}
	
	function binary_sub($bin, $sub)
	{
		$newString = $bin;
		$sub = str_pad($sub, strlen($newString), '0', STR_PAD_LEFT);
		
		for ($i =0; $i<strlen($sub); $i++)	
		{
			if (substr($sub, -($i+1), 1) == "1" && substr($newString, -($i+1), 1) == "1")
			{
				$newString = substr_replace($newString, "0", -($i+1), 1);
			}
			else if (substr($sub, -($i+1), 1) == "0" && substr($newString, -($i+1), 1) == "1")
			{
				$newString = substr_replace($newString, "1", -($i+1), 1);
			}
			
			else if (substr($sub, -($i+1), 1) == "0" && substr($newString, -($i+1), 1) == "0")
			{
				$newString = substr_replace($newString, "0", -($i+1), 1);
			}
			
			else if (substr($sub, -($i+1), 1) == "1" && substr($newString, -($i+1), 1) == "0")
			{
				$newString = NetBlock::bin_sub_carry($newString, -($i+1));
				$newString = substr_replace($newString, "1", -($i+1), 1);
			}
		}
		return $newString;
	}
	
	function bin_sub_carry($bin, $pos)
	{
		$str = $bin;
		if (substr($str, $pos, 1) != "")
		{
			if 	(substr($str, $pos, 1) == "0")
			{
				$str = substr_replace($str, "1", $pos, 1);
				$str = Netblock::bin_sub_carry($str, $pos-1);
			}
			else if (substr($str, $pos, 1) == "1")
			{
				$str = substr_replace($str, "0", $pos, 1);
			}
		}
		else
		{
			$is_negative = true;	
		}
		
		return $str;
	}
	
	public function __toString()
    {
        return $this->ip;
    }

	public function print_all($title = "")
	{
		if ($title != "")
		{
			$title = " - ". $title;	
		}
		$str ="";
		$str .=  "<font style='font-size:18px'>".$this->get_IP().$title."</font><br/><hr /> ";
		//$str .=  "Binary: <font style='font-size:14px'>".$this->get_binary()."</font><br/>";
		//$str .=  "Long: <font style='font-size:14px'>".$this->get_long()."</font><br/>";
		$str .= "<table>";
		$str .=  "<tr><td>Network</td> <td><font style='font-size:14px'>".$this->get_network()."</font><br/></td></tr>";
		$str .=  "<tr><td>Subnet mask</td> <td><font style='font-size:14px'>".$this->get_netmask()."</font><br/></td></tr>";
		$str .=  "<tr><td>Broadcast</td> <td><font style='font-size:14px'>".$this->get_broadcast()."</font><br/></td></tr>";
		$str .=  "<tr><td>Wildcard</td> <td><font style='font-size:14px'>".$this->get_wildcard()."</font><br/></td></tr>";
		$str .=  "<tr><td>Hostmin</td> <td><font style='font-size:14px'>".$this->get_hostmin()."</font><br/></td></tr>";
		$str .=  "<tr><td>Hostmax</td> <td><font style='font-size:14px'>".$this->get_hostmax()."</font><br/></td></tr>";
		$str .=  "<tr><td>Host Per Net</td> <td><font style='font-size:14px'>".number_format($this->get_hostPerNet())."</font><br/></td></tr>";
		$str .=  "<tr><td>IP Per Net</td> <td><font style='font-size:14px'>".number_format($this->get_ipPerNet())."</font><br/></td></tr>";
		$str .= "</table>";
		return $str;
	}
}

?>
