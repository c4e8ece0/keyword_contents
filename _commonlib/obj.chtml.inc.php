<?

class CHtml
{

	function CHtml(){}

	/*
	###########################################################################
	��������� �������� �� ���������� html-�����
	###########################################################################
	*/
	private static function IsHtml ($buf)
	{
		$arr = Array('<html', '<body', '<div', '<table', '<td',  '<br', '<p', '<HTML', '<BODY', '<DIV', '<TABLE', '<TD',  '<BR', '<P', '<Html', '<Body', '<Div', '<Table', '<Td',  '<Br');
		$buf = strtolower($buf);
		foreach($arr as $str)
			if(strpos($buf, $str)!==False)
				return True;

		return False;
	}


	/*
	###########################################################################
	�������� ������ ���������� ����� �� ����
	###########################################################################
	*/
	public static function Format($buf)
	{

		$n     = strlen($buf);
		$res   = Array();
		$tag   = '';                   # ����� ��� ���� ��� ������ ����� �����
		$flag['tin'] =                 # ���� ���������� � ����
		$flag['ain'] = False;          # ���� ���������� � ���������� ��������� ����
		$flag['atr']   = '';           #������ �������� ���������

		for($i = 0; $i < $n; $i++)
		{
			$let = $buf[$i];

			if($let == '<' || $flag['tin']) # ������ � ���
			{

				if(!$flag['tin']) # ������ ������ ����
				{
					$flag['tin'] = True;
					$res[] = $tag;
					$tag = $let;
					continue;
				}
				else # �� ������ ������ ����
				{
					if($let == '\\' && ($buf[$i+1] == '"' || $buf[$i+1] == '\'')) # ������� ������-�������������������
					{
						if($buf[$i+1] == '"')
						{
							$tag.= '\\"';
						}
						elseif($buf[$i+1] == '\'')
						{
							$tag.= '\'';
						}
						$i++;
						continue;
					}

					if(!$flag['ain']) # �� � ����������� ���������
					{
						if($let == '=')
						{
							$flag['ain'] = True;

							$tmp = $buf[$i+1];
							if($tmp == '\'' || $tmp == '"')
							{
								$flag['atr'] = $tmp;
								$i++;
							}
							else
							{
								$flag['atr'] = ' ';
							}

							$tag.= '="';
							continue;
						}
						elseif($let == '>')
						{
							$flag['tin'] = False;
							$res[] = $tag . $let;
							$tag = '';
							continue;
						}
						else
						{
							$tag.= strtolower($let);
							continue;
						}
					}
					else # � ����������� ���������
					{
						if(($let == $flag['atr'] && ($flag['atr'] == '"' || $flag['atr'] == '\'')) || ($flag['atr'] == ' ' && ($let == '>' || $let == ' ')))
						{
							if($let == '"' || $let == '\'')  $tag.= '"';
							if($let == ' ')  $tag.= '" ';
							if($let == '>')
							{
								$tag.= '">';
								$flag['tin'] = False;
								$res[] = $tag;
								$tag = '';
							}

							$flag['ain'] = False;
							$flag['atr'] = '';
							continue;
						}
						else
						{
							$tag.= $let;
							continue;
						}

					}

				}
			}
			else # ����� ��� �����
			{
				$tag.= $let;
			}
			
		}
		$res[] = $tag;

		$ret = "";
		foreach($res as $k=>$v)
		{
			if($v && $v[0] == '<' && @$v[1]!= '\/')
			{
				$n = $m = array();
				$arr = preg_match_all("/ ((?:href|rel)=\".*?[\"])/", $v, $m);
				if(count($m[1]))
				{
					preg_match("/^<([^ >]*)/", $v, $n);
					$v = '<' . $n[1] . ' ' . implode(' ', $m[1]) . '>';
				}
				else
				{
					preg_match("/^<([^ >]*)/", $v, $n);
					$v = '<' . $n[1] . '>';
				}
			}
			$ret.= $v;
		}

		return $ret;

	}


	/*
	###########################################################################
	���������� html-��������� ��� ����������� �������������.
	�������� �������� ����������� ���������� � ���� ������� �� �������.
	###########################################################################
	*/
	public static function Prepare($buf)
	{
		global $SYMBOL;

		$buf = String::Plain($buf);

		$buf = preg_replace("/<+/", '<', $buf);
		$buf = preg_replace("/>+/", '>', $buf);

		$buf = preg_replace("/<!--.*?-->/", ' ', $buf);
		$buf = preg_replace("/<!.*?>/", ' ', $buf);
		$buf = self::ClearPairs($buf, array('script', 'style', 'iframe'));
		$buf = self::Format($buf);
		$buf = preg_replace("/&#(\d+);/", chr((int)"0x0$1"), $buf);
		$buf = preg_replace("/&nbsp;/i", ' ', $buf);
		$buf = str_replace(array_keys($SYMBOL['SYSTEM']), ' ', $buf);

		return $buf;
	}

	
	/*
	###########################################################################
	�������� ��������� html-�����
	###########################################################################
	*/
	public static function Clear($buf)
	{
		$buf = self::Prepare($buf);
		$buf = preg_replace("/<.*?>/", ' ', $buf);
		$buf = String::Format($buf);
		$buf = trim($buf);

		return $buf;
	}
	
	/*
	###########################################################################
	������� <TITLE> �� html-���������
	###########################################################################
	*/
	public static function Title($buf)
	{
		$buf = String::Plain($buf);
		$buf = preg_match("/<title[^>]*>(.*?)</i", $buf, $match);

		$buf = isset($match[1])?trim($match[1]):"";

		return $buf;
	}


	/*
	###########################################################################
	�������� ������ html-����� � ���������� �����������
	###########################################################################
	*/
	public static function ClearPairs($buf, $tag)
	{
		$buf = String::Plain($buf);

		if(is_string($tag))
		{
			$buf = preg_replace("/<".$tag."\b[^>]*>.*?<\/".$tag."[^>]*?>/i", ' ', $buf);
		}
		elseif(is_array($tag))
		{
			foreach ($tag as $t)
			{
				$buf = preg_replace("/<".$t."\b[^>]*>.*?<\/".$t."[^>]*>/i", ' ', $buf);
			}
		}


		return trim($buf);
	}
	
	
	/*
	###########################################################################
	������� ������� body �� html-���������
	###########################################################################
	*/
	public static function Body($buf)
	{
		$buf = self::Prepare($buf);

		if( preg_match("/(<body[^a-z][^>]*>)/i", $buf, $match) )
		{
			return substr($buf, strpos($buf, $match[1]));
		}
		else
			return False;

	}


	/*
	###########################################################################
	������� ������� body �� html-���������
	###########################################################################
	*/
	public static function Base($url, $content)
	{
		preg_match("/<base[^>]*href=\"(http.*?)\"[^>]*>/i", $content, $match);
		if(isset($match[1]) && strlen($match[1])>10)
		{
			return $match[1];
		}
		else
			return $url;

	}


	/*
	###########################################################################
	������� �������� �� html-��������� (���������� ������� ���������).
	###########################################################################
	*/
	public static function Passages($buf)
	{
		if(func_num_args()<2)
		{
			$buf = self::Prepare($buf);
		}

		$clear = $res = Array();

		if(func_num_args() > 1)
		{
			$clear = func_get_arg(1);
		}

		if( $buf = self::Body($buf) )
#		if($buf)
		{
#			$buf = self::ClearPairs($buf, array('textarea', 'select', 'title'));
			$buf = self::ClearPairs($buf, array('textarea', 'select'));
			if(count($clear))
				$buf = self::ClearPairs($buf, $clear);

			$buf = preg_replace("/(<(?:br|p|div|table|td|th|tr|li|ol|ul|h1|h2|h3|h4|h5|h6).*?>)/i", "\n$1", $buf);
			$buf = preg_replace("/(<\/(?:br|p|div|table|td|th|tr|li|ol|ul|h1|h2|h3|h4|h5|h6)[^>]*>)/i", "$1\n", $buf);
			$arr = explode("\n", $buf);
			foreach($arr as $k=>$v)
			{
				$r = self::Clear($v);
				if(String::Clear($r))
					$res[] = $r;
			}
			return $res;		
		}
		else
		{
			return False;
		}
	}

	public static function Links($buf, $host)
	{
		$buf1 = "";
		if(func_num_args() < 3)
		{
			$buf1 = $buf = self::Prepare($buf);
			$buf = self::ClearPairs($buf, array('textarea', 'select', 'noindex'));
		}

		$buf = str_replace(array('>', '<'), array('> ', ' <'), $buf);
		if(!$buf1)
			$buf1 = $buf;
		$buf = strip_tags($buf, '<div><table><td><th><tr><ol><ul><a>');

		$links = Array();
		$str   = Array();

		$links["internal"] = $links["external"] = Array();

		$realhost = $host;
		$host = URL::UniHost($host);

		$buf = '>' . $buf . '<';

		preg_match_all("/(<a[^>]*?href=\"([^\"]*)\"*>)/i", $buf, $match, PREG_OFFSET_CAPTURE);
		$match = $match[0];
		$strlen = strlen($buf);

		for($i=0; $i<count($match); $i++)
		{
			$t['ao_start'] = $match[$i][1];
			$t['ao_end']   = strpos($buf, '>', $match[$i][1])+1;

			$t['ac_start'] = strpos($buf, '<', $t['ao_end']);
			$t['ac_end']   = strpos($buf, '>', $t['ac_start']+1);

			$t['ne_start'] = strpos($buf, '<', $t['ac_end']);
			$t['pr_start'] = strrpos(substr($buf, 0, $t['ao_start']), '>');

			$t['u_start'] = strpos($buf, '"', $match[$i][1])+1;
			$t['u_end']   = strpos($buf, '"', $t['u_start']);

			$r['url']        = substr($buf, $t['u_start'], $t['u_end']-$t['u_start']);
			$r['html_start'] = substr($buf, $t['ao_start'], $t['ao_end']-$t['ao_start']);
			$r['html_end']   = substr($buf, $t['ac_start'], $t['ac_end']-$t['ac_start']+1);
			$r['left']       = substr($buf, $t['pr_start']+1, $t['ao_start']-1 - $t['pr_start']);
			$r['anchor']     = substr($buf, $t['ao_end'], $t['ac_start']-$t['ao_end']);
			$r['right']      = substr($buf, $t['ac_end']+1, $t['ne_start']-$t['ac_end']-1);

			$url   = trim($r['url']);
			$left  = trim($r['left']);
			$txt   = trim($r['anchor']);
			$right   = "";

#			print_r($t);
#			print_r($r);
#			sleep(1);

			if($r['html_start'] != '<a>' && strncmp($url, 'mailto:', 7)  && strncmp($url, 'javascript:', 11) && strpos($url, '#')===False)
			{
				$url   = URL::UrlFull(self::Base($realhost, $buf1), $url);

				if($url)
				{
					if($host == URL::UniHost($url) )
					{
						$links["internal"][] = $url;
					}
					else
					{
						$links["external"][] = $url;
					}
	
					if($r['html_end'] == '</a>')
					{
						$right = trim($r['right']);
					}

					$links["text"][URL::Normalize($url)][] = Array
					(
						"l"=> (String::Clear($left)?trim($left):""),
						"t"=> (String::Clear($txt)?trim($txt):""),
						"r"=> (String::Clear($right)?trim($right):"")
					);
				}
			}
		}

		$links["internal"] = URL::Normalize(array_unique($links["internal"]));
		$links["external"] = URL::Normalize(array_unique($links["external"]));

		return $links;
	}

};

?>