<? 	header("Content-Type: application/xml; charset=UTF-8"); 
	include('comiccontrol/dbconfig.php');
	include('comiccontrol/initialize.php');
	$query = "SELECT * FROM cc_" . $tableprefix . "modules WHERE id='1'";
	$module = fetch($query);
	function selfURL() {
	$s = empty($_SERVER["HTTPS"]) ? ''
		: ($_SERVER["HTTPS"] == "on") ? "s"
		: "";
	$protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
	$port = ($_SERVER["SERVER_PORT"] == "80") ? ""
		: (":".$_SERVER["SERVER_PORT"]);
	return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
}
function strleft($s1, $s2) {
	return substr($s1, 0, strpos($s1, $s2));
}
	$str = '<?xml version="1.0" encoding="UTF-8" ?>
		<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
		<channel>
			<title>' . $module['title'] . '</title>
			<atom:link href="' . selfURL() . '" rel="self" type="application/rss+xml" />
			<link>' . $siteroot . '</link>
			<description>Latest ' . $module['title'] . ' comics and news</description>
			<language>en-us</language>';
			$items = array();
	$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $module['id'] . "' AND publishtime <= " . time() . " ORDER BY publishtime DESC LIMIT 20";
	$result = $z->query($query);
	while($row = $result->fetch_assoc()){
		$str .= '<item><title><![CDATA[' . $module['title'] . ' - ' . html_entity_decode($row['comicname'],ENT_QUOTES) . ']]></title>';
		$desc_data = '<a href="' . $siteroot . $module['slug'] . '/' . $row['slug'] . '">New comic!</a><br />Today\'s News:<br />' . $row['newscontent'];
		$desc_data = preg_replace("#(<\s*a\s+[^>]*href\s*=\s*[\"'])(?!http)([^\"'>]+)([\"'>]+)#", $siteroot . '$2$3', $desc_data);
		$desc_data = preg_replace("<html>", '', $desc_data);
		$desc_data = preg_replace("<body>", '', $desc_data);
		$desc_data = preg_replace("</html>", '', $desc_data);
		$desc_data = preg_replace("</body>", '', $desc_data);
		$dom = new DOMDocument();
		@$dom->loadHTML($desc_data);
		
		for ($i=0; $i<$dom->getElementsByTagName('img')->length; $i++) {
			$encoded = implode("/", array_map("rawurlencode",
				 explode("/", $dom->getElementsByTagName('img')
							->item($i)->getAttribute('src'))));
		
			$dom->getElementsByTagName('img')
					->item($i)
					->setAttribute('src',$encoded);
		}
		$desc_data = $dom->saveHTML();
		$desc_data = str_replace("<html>", '', $desc_data);
		$desc_data = str_replace("<body>", '', $desc_data);
		$desc_data = str_replace("</html>", '', $desc_data);
		$desc_data = str_replace("</body>", '', $desc_data);
		$str .= '<description><![CDATA[' . $desc_data . ']]></description>';
		$str .= '<link>' . $siteroot . $module['slug'] . '/' . $row['slug'] . '</link>';
		$str .= '<author>tech@thehiveworks.com</author>';
		$str .= '<pubDate>' . date("D, d M Y H:i:s O", $row['publishtime']) . '</pubDate>';
		$str .= '<guid>' . $siteroot . $module['slug'] . '/' . $row['slug'] . '</guid>';
		$str .= '</item>';
	}
	$str .= '</channel></rss>';
	echo $str;
?>
