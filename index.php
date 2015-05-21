<?

define('INCLUDE_COMMON', dirname(__FILE__) . '/_commonlib');

include INCLUDE_COMMON . '/inc.start.inc.php';
include INCLUDE_COMMON . '/inc.morphy.inc.php';

$_title = 'Разметка списка запросов нужными словами';

$i   = 0;
$res = '';

$plimit = 10000;
$wlimit = 200;

if($_SERVER['HTTP_HOST'] == 'red' || str_replace('www.', '', $_SERVER['HTTP_HOST']) == 'expodisplay.ru') {
	$plimit = 50000;
	$wlimit = 10000;
}

$itwas = array();
$time = time();

$__RES = '';


function __printr(&$a)
{
	print '<pre>';
	print_r($a);
	print '</pre>';
}


$_delim = !$_POST || empty($_POST['delim']) ? '|'  : $_POST['delim'];
$_norm  = !$_POST || !empty($_POST['norm'])  ? 1 : 0;

$_err = $_POST ? '<div style="border:1px solid red; padding:5px 10px; margin-bottom:10px; color:red;">Запросы и слова должны быть заполнены</div>' : '';

if(!empty($_POST['list']))
{
	$_err = '';
	$list = $_list = array_splice(array_map('trim', explode("\n", $_POST['list'])), 0, $plimit); // no filter for void strings
	$word = $_word = array_splice(array_unique(array_filter(array_map('trim', explode("\n", $_POST['word'])), 'strlen')), 0, $wlimit);

	if($_norm)
	{
		foreach($list as $k=>$v)
		{
			$list[$k] = ' ' . String::UniHash($v) . ' ' ;
		}

		foreach($word as $k=>$v)
		{
			$word[$k] = ' ' . String::UniHash($v) . ' ';
		}
	}
	else
	{
		foreach($list as $k=>$v)
		{
			$list[$k] = strtolower($v);
		}

		foreach($word as $k=>$v)
		{
			$word[$k] = strtolower($v);
		}
	}


	$pre = array(); // предварительная выборка подстрок
	foreach($_list as $k=>$v) // $list (without "_" used in comparing)
	{
		foreach($word as $a=>$b)
		{
			if(strpos($list[$k], $b) !== false)
			{
				$pre[$k][] = $b;
			}
		}
	}

	$r = array(); // склейка множественных вариантов
	foreach($pre as $k=>$v)
	{
		if(count($v) > 1)
		{
			asort($v);
		}

		$v = array_filter(array_map('trim', $v), 'strlen');

		$r[$k] = implode($_delim, $v);
	}


	$res = array();
	$tf  = array();
	$tfr = array();

	foreach($_list as $k=>$v)
	{
		$res[] = $v . (isset($r[$k]) ? "\t" . $r[$k] : '');
		$tf = array_merge($tf, String::UniWord($v));
	}
	$tf = array_count_values($tf);
	arsort($tf);

	foreach($tf as $k=>$v)
	{
		$tfr[] = $k . "\t" . $v	;
	}


	/*
	---------------------------------------------------------------------------
	Вывод
	---------------------------------------------------------------------------
	*/

	$__RES.=
	'
	<hr>
	<table style="width:100%;">
	<tr>
		<td style="width:50%;"><b>Маркеры:</b><br><textarea style="width:100%; height:300px;">' . htmlspecialchars(implode("\n", $res)) . '</textarea></td>
		<td style="width:50%;"><b>TF:</b><br><textarea style="width:100%; height:300px;">' . htmlspecialchars(implode("\n", $tfr)) . '</textarea></td>
	</tr>
	</table>
	<hr>
	';
}


?><html>
<head>
<title><?php print $_title;?></title>
<style>
td {vertical-align:top;}
table.sub {border-collapse:collapse; border-spacing:0;}
table.sub td, table.sub th {font-size:smaller; padding:2px; border-top:1px solid #ccc; text-align:center;}
table.sub th {background:#ccc; border-left:1px solid #fff; padding:2px 10px;}
</style>
<meta http-equiv="Content-type" content="text/html; charset=windows-1251">
</head>
<body>
<h1><?php print $_title;?></h1>
<form action="./" method="post">
<table>
<tr>
	<td><b>Список запросов:</b><br><small>разделитель - новая строка,<br>max = <?php print $plimit;?></td>
	<td><textarea name="list"  rows="10" cols="70"><?=htmlspecialchars(@$_POST['list']);?></textarea></td>
	<td><b>Список слов:</b><br><small>по слову на строку<br>max = <?php print $wlimit;?></td>
	<td><textarea name="word"  rows="10" cols="30"><?=htmlspecialchars(@$_POST['word']);?></textarea></td>
</tr>
<tr><td><b>Разделитель:</b><br><small>если несколько<br>попаданий</small></td><td colspan="3"><input type="text" name="delim" value="<?php print @$_POST['delim'] ? htmlspecialchars(@$_POST['delim']) : $_delim;?>" style="text-align:center; width:30px;"></td>
<tr><td><b>Нормализовать:</b><br><small>и искать по словам,<br>иначе - по строчным<br>подстрокам</small></td><td colspan="3"><input type="checkbox" name="norm"<?php print $_norm ? ' checked' : '';?>></td>
<tr><td></td><td colspan="3"><?=$_err;?><input type="submit" value="Мне повезёт!" style="width:200px; height:40px;"></td>
</table>
</form>
<br>

<?=$__RES;?>

<br><br>

<?=file_get_contents('http://www.adsem.ru/inst/marathon.php');?>

<br><br>
<!--LiveInternet counter--><script type="text/javascript">document.write("<a href='http://www.liveinternet.ru/click' target=_blank><img src='//counter.yadro.ru/hit?t44.1;r" + escape(document.referrer) + ((typeof(screen)=="undefined")?"":";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?screen.colorDepth:screen.pixelDepth)) + ";u" + escape(document.URL) + ";" + Math.random() + "' border=0 width=31 height=31 alt='' title='LiveInternet'><\/a>")</script><!--/LiveInternet-->
</body>
</html>