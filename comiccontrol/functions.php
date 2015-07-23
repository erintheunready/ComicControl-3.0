<?

//lightbox is not loaded.
$galleryloaded = false;

//comicDisplay() - displays comic in #comicbody div
function comicDisplay($comicid, $slug = "", $preview=false){
	global $tableprefix;
	global $dateformat;
	global $timeformat;
	global $lang;
	global $siteroot;
	global $z;
	global $usemaxwidth;
	global $root;
	global $maxwidth;
	
	$comicid = filterint($comicid);
	$slug = sanitizeSlug($slug);
	
	$query = "SELECT * FROM cc_" . $tableprefix . "modules WHERE id='" . $comicid . "' LIMIT 1";
	$comicinfo = fetch($query);
	
	//get comic that will be displayed
	
	//no comic selected, get most recent
	if($slug == ""){ 
		$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $comicid . "'";
		if(!$preview) $query .= " AND publishtime <= " . time();
		$query .= " ORDER BY publishtime DESC LIMIT 1";
		$result = $z->query($query);
		$row = $result->fetch_assoc();
		$id = $row['id'];
		$numrows = $result->num_rows;
	}
	
	//comic selected, get comic
	else{
		$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $comicid . "' AND slug='" . $slug . "'";
		if(!$preview){
		 $query .= " AND publishtime <= " . time(); 
		}
		$query .= " LIMIT 1";
		$result = $z->query($query);
		$row = $result->fetch_assoc();
		$id = $row['id'];
		$numrows = $result->num_rows;
	}
	
	//DISPLAY COMIC
	echo '<div id="cc-comicbody">';
	
	//no comic found; throw error
	if($numrows == 0){
		echo '<p style="float:left; clear:both; width:500px; margin: 0 auto; text-align:center;">' . $lang['thereisnocomic'] . '</p>';
	}
	
	//display comic
	else{
		
		//get next comic for link
		$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $comicid . "' AND publishtime > " . $row['publishtime'];
		if(!$preview) $query .= " AND publishtime <= " . time();
		$query .= " ORDER BY publishtime ASC LIMIT 1";
		$result = $z->query($query);
		$numrows2 = $result->num_rows;
		if($row['hovertext'] == "") $hovertext=$row['comicname']; else $hovertext=$row['hovertext'];
		
		//determine if showing lightbox for high res comic
		$isWide = false;
		if($usemaxwidth == "yes"){
			if($row['width'] > $maxwidth){
				$isWide = true;
				?>
				<script type="text/javascript" src="<?=$root?>includes/jquery.js"></script>
                <script type="text/javascript" src="<?=$root?>includes/lightGallery.js"></script>
                <link rel="stylesheet" href="<?=$root?>includes/lightGallery.css" type="text/css" media="screen" />
                
                <style type="text/css">
                .cc-showbig{
                    list-style: none outside none;
					padding:0;
                }
                .cc-showbig li{
                    display:block;
                }
                .cc-showbig li a {
                    cursor:pointer;
                }
                </style>
                <script>
                     $(document).ready(function() {
                        $(".cc-showbig").lightGallery();
                    });
                </script>
                <?
			}
		}
		
		//if mobile, generate code to display hovertext on tap
		$mobile = isMobile();
		if($row['hovertext'] == "") $mobile = false;
		if($mobile && !$isWide){
			?>
            <script>
				function showHovertext(){
					var coverup = document.getElementById("cc-coverup");
					if(coverup.style.display=="none"){
						coverup.style.display="block";
					}else{
						coverup.style.display="none";
					}
				}
			</script>
            <style>
				#cc-comicbody{
					position:relative;
				}
				#cc-coverup{
					 /* Fallback for web browsers that don't support RGBa */
					background-color: rgb(0, 0, 0);
					/* RGBa with 0.6 opacity */
					background-color: rgba(0, 0, 0, 0.6);
					/* For IE 5.5 - 7*/
					filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=#99000000, endColorstr=#99000000);
					/* For IE 8*/
					-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#99000000, endColorstr=#99000000)";
					display:none;
					width:100%;
					height:100%;
					position:absolute;
					top:0;
					left:0;
				}
				#cc-hoverdiv{
					position:absolute;
					width:60%;
					padding:3%;
					margin-left:17%;
					margin-top:35%;
					background:#fff;
					color:#000;
					font-size:2em;
					border-radius:10px;
				}
			</style>
            <?		}
			
		//display link if not mobile
		if($numrows2 > 0 && !$mobile && !$isWide){
			$row2 = $result->fetch_assoc();
			echo '<a href="' . $siteroot . $comicinfo['slug'] . "/" . $row2['slug'] . '">';
		}
		
		//handle case for swf
		if($row['mime'] == "application/x-shockwave-flash"){
			echo '<div id="cc-comic" style="height:' . $row['height'] . 'px; width:' . $row['width'] . 'px; display:inline-block;">';
			echo '<object height="' . $row['height'] . '" width="' . $row['row'] . '">
					<param name="movie" value="' . $row['imgname'] . '">
					<embed src="' . $row['imgname'] . '" type="application/x-shockwave-flash" height="' . $row['height'] . '" width="' . $row['width'] . '"></object>';
			echo '</div>';
		}
		
		//if not swf, display image
		else{
			if($isWide){
				echo '<ul class="cc-showbig">';
				echo '<li data-src="' . $siteroot . "comicshighres/" . $row['comichighres'] . '"><a><img src="' . $siteroot . "comics/" . $row['imgname'] . '" /></a></li>';
				echo '</ul>';
			}
			else{
				echo '<img title="' . $hovertext . '" src="' . $siteroot . 'comics/' . $row['imgname'] . '" id="cc-comic" border="0"';
					if($mobile && !$isWide) echo ' onclick="showHovertext()"';
					echo ' /><br />';
				$comicfile = "comics/" . $row['imgname'];
				if($numrows2 > 0 && !$mobile){
					echo '</a>';
				}
			
				//display hovertext div for mobile
				if($mobile){
					?>
					
					<div id="cc-coverup" onclick="showHovertext()"><div id="cc-hoverdiv"><?=$hovertext?></div></div>
					<?
				}
			}
		}
	}
	echo '</div>';
}


//displayNews() take comic id and slug and display news
function displayNews($comicid, $slug = "",$preview=false){
	global $tableprefix;
	global $disqusname;
	global $dateformat;
	global $timeformat;
	global $newsmode;
	global $siteroot;
	global $lang;
	global $z;
	
	$comicid = filterint($comicid);
	$slug = sanitizeSlug($slug);
	
	$query = "SELECT * FROM cc_" . $tableprefix . "modules WHERE id='" . $comicid . "' LIMIT 1";
	$comicinfo = fetch($query);	
	$numrows = 0;
	
	
	//if no slug, get most recent news id
	if($slug == ""){ 
		$query = "SELECT id FROM cc_" . $tableprefix . "comics WHERE comic='" . $comicid . "'";
		if(!$preview) $query .= " AND publishtime <= " . time();
		$query .= " ORDER BY publishtime DESC LIMIT 1";
		$result = $z->query($query);
		$row = $result->fetch_assoc();
		$id = $row['id'];
		$numrows = $result->num_rows;
	}
	
	//if slug, get news id
	else{
		$query = "SELECT id FROM cc_" . $tableprefix . "comics WHERE comic='" . $comicid . "' AND slug='" . $slug . "'";
		if(!$preview){
		 $query .= " AND publishtime <= " . time(); 
		}
		$query .= " LIMIT 1";
		$result = $z->query($query);
		$row = $result->fetch_assoc();
		$id = $row['id'];
		$numrows = $result->num_rows;
	}
	
	//GET MOST RECENT NEWS IF IN LATEST NEWS MODE
	if($newsmode == "latestnews"){
		$query = "SELECT id FROM cc_" . $tableprefix . "comics WHERE publishtime<=" . $row['publishtime'] . " AND newscontent!='' AND comic='" . $comicid . "' ORDER BY publishtime DESC LIMIT 1";
		$result = $z->query($query);
		if($result->num_rows > 0){
			$row = $result->fetch_assoc();
			$id = $row['id'];
		}
	}
	
	//display news
	echo '<div class="cc-newsarea">';
	if($numrows != 0){
		$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $comicid . "' AND id='" . $id . "' LIMIT 1";
		$result = $z->query($query);
		$news = $result->fetch_assoc();
		echo '<div class="cc-newsheader">';
		if($slug == ""){
			echo '<a href="' . $siteroot . $comicinfo['slug'] . '/' . $news['slug'] . '/">' . $news['newstitle']. '</a>'; 
		}else{
			echo $news['newstitle']; 
		}
		echo '</div><div class="cc-publishtime">posted ' . date($dateformat,$news['publishtime']) . ' at ' . date($timeformat,$news['publishtime']) . '<br /></div>';
		echo '<div class="cc-newsbody">';
		echo $news['newscontent'] . '';
		echo '</div></div>';
	}
	
	//throw error if no news id was retrieved
	else{
		echo '<p>' . $lang['thereisnonews'] . '</p></div>';
	}
}


//displayComments
function displayComments($comicid, $slug = "", $preview=false, $showonindex=false){
	global $tableprefix;
	global $disqusname;
	global $dateformat;
	global $timeformat;
	global $siteroot;
	global $z;
	
	$comicid = filterint($comicid);
	$slug = sanitizeSlug($slug);
	
	//get comic info
	$query = "SELECT * FROM cc_" . $tableprefix . "modules WHERE id='" . $comicid . "' LIMIT 1";
	$comicinfo = fetch($query);	
	$numrows = 0;
	
	//get comic id for comments
	
	//get latest comic if no slug
	if($slug == ""){
		$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $comicid . "'";
		if(!$preview) $query .= " AND publishtime <= " . time();
		$query .= " ORDER BY publishtime DESC LIMIT 1";
		$result = $z->query($query);
		$row = $result->fetch_assoc();
		$id = $row['id'];
		$numrows = $result->num_rows;
	}
	//if slug, get comic id
	else{
		$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $comicid . "' AND slug='" . $slug . "'";
		if(!$preview){
		 $query .= " AND publishtime <= " . time(); 
		}
		$query .= " LIMIT 1";
		$result = $z->query($query);
		$row = $result->fetch_assoc();
		$id = $row['id'];
		$numrows = $result->num_rows;
	}
	
	//get latest news id if in latestnews mode
	if($newsmode == "latestnews"){
		$query = "SELECT id FROM cc_" . $tableprefix . "comics WHERE publishtime<=" . $row['publishtime'] . " AND newscontent!='' AND comic='" . $comicid . "' ORDER BY publishtime DESC LIMIT 1";
		$result = $z->query($query);
		if($result->num_rows > 0){
			$row = $result->fetch_assoc();
			$id = $row['id'];
		}
	}
	
	//if slug is not set, display comment link
	if(($slug == "" && $numrows != 0) && !$showonindex){
		echo '<div class="cc-commentlink"><a href="' . $siteroot . $comicinfo['slug'] . '/' . $row['slug'] . '#disqus_thread" data-disqus-identifier="' . $row['commentid'] . '">View/Post Comments</a></div>';
		?>
		<script type="text/javascript">
			var disqus_shortname = '<?=$disqusname?>'; 
			var disqus_identifier = '<?=$row['commentid']?>';
			 (function () {
				var s = document.createElement('script'); s.async = true;
				s.type = 'text/javascript';
				s.src = 'http://' + disqus_shortname + '.disqus.com/count.js';
				(document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s);
			}());
		</script>
		<?
	}
	
	//if slug is set, display comments
	else if($numrows > 0){
		echo '<div class="cc-commentheader">Comments</div><div class="cc-commentbody">';
		?>
		<div id="disqus_thread"></div>
			<script type="text/javascript">
				var disqus_shortname = '<?=$disqusname?>'; 
				var disqus_url = '<?=$siteroot?><?=$comicinfo['slug']?>/<?=$row['slug']?>'; 
				var disqus_identifier = '<?=$row['commentid']?>';
				(function() {
					var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
					dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
					(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
				})();
			</script>
			<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
			<a href="http://disqus.com" class="dsq-brlink">blog comments powered by <span class="logo-disqus">Disqus</span></a>
        <?
		echo '</div>';
    }
}

function navDisplay($comicid, $slug="", $preview = false){
	global $tableprefix;
	global $siteroot;
	global $navaux;
	global $navorder;
	global $z;
	
	$comicid = filterint($comicid);
	$slug = sanitizeSlug($slug);
	
	//get comic info
	$comicinfo = fetch("SELECT * FROM cc_" . $tableprefix . "modules WHERE id='" . $comicid . "' LIMIT 1");
	
	//get comic id of latest if slug not set
	if($slug == ""){ 
		$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $comicid . "'";
		if(!$preview) $query .= " AND publishtime <= " . time();
		$query .= " ORDER BY publishtime DESC LIMIT 1";
		$result = $z->query($query);
		$numrows = $result->num_rows;
	}
	
	//get comic id if slug set
	else{
		$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $comicid . "' AND slug='" . $slug . "'";
		if(!$preview) $query .= " AND publishtime <= " . time();
		$query .= " LIMIT 1";
		$result = $z->query($query);
		$numrows = $result->num_rows;
	}
	
	//only display if comic was found
	if($numrows != 0){
		$current = $result->fetch_assoc();
		
		//first
		$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $comicid . "'";
		if(!$preview) $query .= " AND publishtime <=" . time();
		$query .= " ORDER BY publishtime ASC LIMIT 1";
		$first = fetch($query);
	
		//prev
		$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $comicid . "' AND publishtime < " . $current['publishtime'];
		if(!$preview) $query .= " AND publishtime <=" . time();
		$query .= " ORDER BY publishtime DESC LIMIT 1";
		$prev = fetch($query);
	
		//next
		$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $comicid . "' AND publishtime > " . $current['publishtime'];
		if(!$preview) $query .= " AND publishtime <=" . time();
		$query .= " ORDER BY publishtime ASC LIMIT 1";
		$next = fetch($query);
	
		//last
		$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $comicid . "'";
		if(!$preview) $query .= " AND publishtime <=" . time();
		$query .= " ORDER BY publishtime DESC LIMIT 1";
		$last = fetch($query);
		
		//get real numrows
		$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $comicid . "'";
		if(!$preview) $query .= " AND publishtime < " . time();
		$result = $z->query($query);
		$numrows = $result->num_rows;
	
		//BUTTON HTML
		if($current['id'] != $first['id'] && $numrows > 1){
			$firstbutton = '<a href="' . $siteroot . $comicinfo['slug'] . '/' . $first['slug'] . '" class="first" rel="start"></a>';
			$prevbutton = '<a href="' . $siteroot . $comicinfo['slug'] . '/' . $prev['slug'] . '" class="prev" rel="prev"></a>';
		}else{
			$firstbutton = '<div class="firstdis"></div>';
			$prevbutton = '<div class="prevdis"></div>';
		}
		$auxbutton = '<a href="' . $siteroot . $navaux . '" class="navaux" rel="rss"></a>';
		if($current['id'] != $last['id'] && $numrows > 1){
			$nextbutton = '<a href="' . $siteroot . $comicinfo['slug'] . '/' . $next['slug'] . '" class="next" rel="next"></a>';
			$lastbutton = '<a href="' . $siteroot . $comicinfo['slug'] . '/' . $last['slug'] . '" class="last" rel="index"></a>';
		}else{
			$nextbutton = '<div class="nextdis"></div>';
			$lastbutton = '<div class="lastdis"></div>';
		}
		
		//output buttons in their assigned order
		$navorderarr = explode("|",$navorder);
		$buttons = '<div class="nav">';
		foreach($navorderarr as $value){
			switch($value){
				case "first":
					$buttons .= $firstbutton;
					break;
				case "prev":
					$buttons .= $prevbutton;
					break;
				case "next":
					$buttons .= $nextbutton;
					break;
				case "last":
					$buttons .= $lastbutton;
					break;
				case "aux":
					$buttons .= $auxbutton;
					break;
			}
		}
		$buttons .= '</div>';
		echo $buttons;
	}
}

function displayTags($comicid,$slug="",$preview=false){
	global $tableprefix;
	global $z;
	global $lang;
	global $siteroot;
	
	$comicid = filterint($comicid);
	$slug = sanitizeSlug($slug);
	
	$query = "SELECT * FROM cc_" . $tableprefix . "modules WHERE id='" . $comicid . "' LIMIT 1";
	$result = $z->query($query);
	$comicinfo = $result->fetch_assoc();
	
	if($slug == ""){
		$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $comicid . "'";
		if(!$preview) $query .= " AND publishtime <= " . time();
		$query .= " ORDER BY publishtime DESC";
	}else{
		$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE slug='" . $slug . "' AND comic='" . $comicid . "'";
		if(!$preview) $query .= " AND publishtime <= " . time();
		$query .= " ORDER BY publishtime DESC LIMIT 1";
	}
	$result = $z->query($query);
	$comic = $result->fetch_assoc();
	
	$query = "SELECT DISTINCT tag FROM cc_" . $tableprefix . "comics_tags WHERE comicid='" . $comic['id'] . "'";
	if(!$preview) $query .= " AND publishtime <= " . time();
	$result = $z->query($query);
	$divided = false;
	if($result->num_rows > 0){
		echo '<div class="cc-tagline">' . $lang['tagline'] . ': ';
		while($tag = $result->fetch_assoc()){
			if($divided) echo ", ";
			$divided = true;
			echo '<a href="' . $siteroot . $comicinfo['slug'] . "/search/" . $tag['tag'] . '">' . $tag['tag'] . '</a>';
		}
		echo '</div>';
	}
}

function displayTranscript($comicid,$slug="",$preview=false){
	global $tableprefix;
	global $z;
	
	//sanitize arguments
	$comicid = filterint($comicid);
	$slug = sanitizeSlug($slug);
	
	//get comic info
	$query = "SELECT * FROM cc_" . $tableprefix . "modules WHERE id='" . $comicid . "' LIMIT 1";
	$comicinfo = fetch($query);
	
	//if no slug, get latest comic
	if($slug == ""){
		$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $comicid . "'";
		if(!$preview) $query .= " AND publishtime <= " . time();
		$query .= " ORDER BY publishtime DESC LIMIT 1";
		$result = $z->query($query);
		$row = $result->fetch_assoc();
		$id = $row['id'];
		$numrows = $result->num_rows;
	}
	
	//if slug is assigned, get comic
	else{
		$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $comicid . "' AND slug='" . $slug . "'";
		if(!$preview){
		 $query .= " AND publishtime <= " . time(); 
		}
		$query .= " LIMIT 1";
		$result = $z->query($query);
		$row = $result->fetch_assoc();
		$id = $row['id'];
		$numrows = $result->num_rows;
	}
	
	//output transcript
	echo '<div class="cc-transcript">' . $row['transcript'] . '</div>';
}


function displayTitle($comicid,$slug = "",$preview=false){
	global $tableprefix;
	global $slugarr;
	global $lang;
	global $z;
	
	//sanitize arguments
	$comicid = filterint($comicid);
	$slug = sanitizeSlug($slug);
	
	$query = "SELECT * FROM cc_" . $tableprefix . "modules WHERE id='" . $comicid . "' LIMIT 1";
	$moduleinfo = fetch($query);
	
	if($moduleinfo['type'] == "comic" || $moduleinfo['type'] == "blog"){
		//if no slug, get latest comic
		if($slug == ""){
			$query = "SELECT * FROM cc_" . $tableprefix . $moduleinfo['type'] . "s WHERE " . $moduleinfo['type'] . "='" . $comicid . "'";
			if(!$preview) $query .= " AND publishtime <= " . time();
			$query .= " ORDER BY publishtime DESC LIMIT 1";
		}
		
		else if($slugarr[1] == "archive"){
			echo $lang['archive'];
		}
		
		else if($slugarr[1] == "search"){
			echo $lang['search'];
		}
		
		//if slug is assigned, get comic or blog
		else{
			$query = "SELECT * FROM cc_" . $tableprefix . $moduleinfo['type'] . "s WHERE " . $moduleinfo['type'] . "='" . $comicid . "' AND slug='" . $slug . "'";
			if(!$preview) $query .= " AND publishtime <= " . time();
			$query .= " LIMIT 1";
		}
		$result = $z->query($query);
		$numrows = $result->num_rows;
		$row = $result->fetch_assoc();
		if($moduleinfo['type'] == "comic") echo $row['comicname']; else echo $row['title'];
	}else{
		echo $moduleinfo['title'];
	}
	
	//output title
	$row['comicname'];
}
function displayDropdown($comicid){
	global $tableprefix;
	global $dateformat;
	global $timeformat;
	global $siteroot;
	global $z;
	
	//sanitize arguments
	$comicid = filterint($comicid);
	
	//get comic info
	$query = "SELECT * FROM cc_" . $tableprefix . "modules WHERE id='" . $comicid . "' LIMIT 1";
	$comicinfo = fetch($query);
	
	//output dropdown for comic
	?>
    <script language="javascript">
	function changePage(comic,slug){
		var location = "<?=$siteroot?>" + comic + "/" + slug;
		window.location.href=location;
	}
	</script>
	<select name="comic" onChange="changePage('<?=$comicinfo['slug']?>',this.value)" width="100"><option value="">Select a comic...</option>
	<?
    $query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $comicid . "' AND publishtime <= " . time() . " ORDER BY publishtime ASC";
    $result = $z->query($query);
    while($row=$result->fetch_assoc()){
        echo '<option value="' . $row['slug'] . '">' . date($dateformat,$row['publishtime']) . ' - ' . $row['comicname'] . '</option>';
    }
    ?>
    </select>
    <?
}
function displayChapters($comicid){
	global $tableprefix;
	global $dateformat;
	global $timeformat;
	global $siteroot;
	global $z;
	
	//sanitize arguments
	$comicid = filterint($comicid);
	
	//get comic info
	$query = "SELECT * FROM cc_" . $tableprefix . "modules WHERE id='" . $comicid . "' LIMIT 1";
	$comicinfo = fetch($query);
	
	//display storylines in hierarchical order
	$query = "SELECT * FROM cc_".  $tableprefix . "comics_storyline WHERE comic='" . $comicid . "' AND parent='0' ORDER BY sorder ASC";
	$result = $z->query($query);
	$count = array();
	$numrows = array();
	$parent = 0;
	$results = array();
	$rows = array();
	$temp = 0;
	while($row = $result->fetch_assoc()){
		$parent = $row['id'];
		$query = "SELECT * FROM cc_".$tableprefix . "comics_storyline WHERE comic='" . $comicid . "' AND parent='" . $parent . "' ORDER BY sorder ASC";
		$results[$parent] = $z->query($query);
		$numrows[$parent] = $results[$parent]->num_rows;
		$count[$parent] = 0;
		$spacecount = 1;
		$haspages = false;
		$hasstorylines = false;
		$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE storyline='" . $parent . "' AND publishtime <=" . time() . " ORDER BY publishtime ASC LIMIT 1";
		$currresult = $z->query($query);
		if($currresult->num_rows > 0){ 
			$haspages = true;
			$firstpage = $currresult->fetch_assoc();
		}
		else{
			$query = "SELECT * FROM cc_" . $tableprefix . "comics_storyline WHERE parent='" . $parent . "' ORDER BY sorder ASC LIMIT 1";
			$currresult = $z->query($query);
			if($currresult->num_rows > 0) $hasstorylines = true;
		}
		if($hasstorylines || $haspages){
			echo '<div class="cc-chapterrow">';
			if($haspages) echo '<a href="' . $siteroot . $comicinfo['slug'] . "/" . $firstpage['slug'] . '">';
			echo $row['name'];
			if($haspages) echo '</a>';
			echo '</div>'; 
		}
		$query = "SELECT * FROM cc_".$tableprefix . "comics_storyline WHERE comic='" . $comicid . "' AND parent='" . $parent . "' ORDER BY sorder ASC";
		while($count[$parent] < $numrows[$parent]){
			$haspages = false;
			$hasstorylines = false;
			$currspace = 0;
			$count[$parent]++;
			$row2 = $results[$parent] -> fetch_assoc();
			$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE storyline='" . $row2['id'] . "' AND publishtime <=" . time() . " ORDER BY publishtime ASC LIMIT 1";
			$currresult = $z->query($query);
			if($currresult->num_rows > 0){ 
				$haspages = true;
				$firstpage = $currresult->fetch_assoc();
			}
			else{
				$query = "SELECT * FROM cc_" . $tableprefix . "comics_storyline WHERE parent='" . $row2['id'] . "' ORDER BY sorder ASC LIMIT 1";
				$currresult = $z->query($query);
				if($currresult->num_rows > 0) $hasstorylines = true;
			}
			if($hasstorylines || $haspages){
				echo '<div class="cc-chapterrow" style="margin-left:' . ($spacecount*50) . 'px">';
				if($haspages) echo '<a href="' . $siteroot . $comicinfo['slug'] . "/" . $firstpage['slug'] . '">';
				echo $row2['name'];
				if($haspages) echo '</a>';
				echo '</div>'; 
			}
			$temp = $parent;
			$parent = $row2['id'];
			$query = "SELECT * FROM cc_".$tableprefix . "comics_storyline WHERE comic='" . $comicid . "' AND parent='" . $parent . "' ORDER BY sorder ASC";
			$results[$parent] = $z->query($query);
			$numrows[$parent] = $results[$parent]->num_rows;
			if($numrows[$parent] == 0){
				$parent = $temp;
			}else{
				$count[$parent] = 0;
				$spacecount++;
			}
			if($count[$parent] == $numrows[$parent]){
				$query = "SELECT * FROM cc_".$tableprefix . "comics_storyline WHERE comic='" . $comicid . "' AND id='" . $parent . "' ORDER BY sorder ASC";
				$tempresult = $z->query($query);
				$temprow =  $tempresult -> fetch_assoc();
				$parent = $temprow['parent'];
				$spacecount--;
			}
		}
	}
}
function displayPage($pageid,$showtitle=false){
	global $tableprefix;
	global $z;
	
	$pageid = filterint($pageid);
	
	$query = "SELECT * FROM cc_" . $tableprefix . "pages WHERE id='" . $pageid . "'";
	$result = $z->query($query);
	if($result ->num_rows >0){
		$page = $result->fetch_assoc();
		if($showtitle) echo '<h1>' . $page['title'] . '</h1>';
		echo $page['content'];
	}else{
		echo '<div class="cc-nopage">' . $lang['nopage'] . '</p>';
	}
}
function displayGallery($slug,$showtitle=false){
	global $tableprefix;
	global $galleryloaded;
	global $siteroot;
	global $root;
	global $z;
	
	$slug = sanitizeSlug($slug);

	//don't load lightbox iff lightbox already loaded	
	if(!$galleryloaded){
		?>
		<script type="text/javascript" src="<?=$root?>includes/jquery.js"></script>
		<script type="text/javascript" src="<?=$root?>includes/lightGallery.js"></script>
		<link rel="stylesheet" href="<?=$root?>includes/lightGallery.css" type="text/css" media="screen" />
		
		<style type="text/css">
		.cc-gallery{
			list-style: none outside none;
		}
		.cc-gallery li{
			margin: 10px 10px 0px 0px;
			float:left;
			width: 125px;
			height:125px;
			text-align:center; 
			display:block;
		}
		.cc-gallery li a {
			height: 125px;
			width: 125px;
			cursor:pointer;
		}
		.customHtml{
			font-size:12px;
		}
		.customHtml a{
			color:#fff;
		}
		</style>
		<script>
             $(document).ready(function() {
                $(".cc-gallery").lightGallery();
            });
        </script>
		
		<?
		$galleryloaded = true;
	}
	
	$query = "SELECT * FROM cc_" . $tableprefix ."modules WHERE slug='" . $slug . "'";
	$result = $z->query($query);
	$gallery=$result->fetch_assoc();
	if($showtitle){
		echo '<h1>' . $gallery['title'] . '</h1>';
	}
	$query = "SELECT * FROM cc_" . $tableprefix . "galleries WHERE gallery='" . $gallery['id'] . "' ORDER BY porder ASC";
	$result = $z->query($query);
	if($result->num_rows == 0){
		echo '<div class="noimages">' . $lang['therearenoimages'] . '</div>';
	}else{
		echo '<ul class="cc-gallery">';
		while($row=$result->fetch_assoc()){
			echo '<li data-src="' . $siteroot . "uploads/" . $row['imgname'] . '" data-sub-html="<div class=\'customHtml\'>' . $row['caption'] . '</div>"><a><img src="' . $siteroot . "uploads/" . $row['thumbname'] . '" /></a></li>';
		}
		echo '<div style="clear:left"></div>';
		echo '</ul>';
	}
}
function displaySinglePost($tableid,$blogid,$comments="nocomments"){
	global $tableprefix;
	global $dateformat;
	global $timeformat;
	global $disqusname;
	global $siteroot;
	global $lang;
	global $z;
	
	$tableid = filterint($tableid);
	$blogid = filterint($blogid);
	
	$query = "SELECT * FROM cc_" . $tableprefix . "blogs WHERE blog='" . $tableid . "' AND id='" . $blogid . "' LIMIT 1";
	$result = $z->query($query);
	
	//display blog post
	if($result->num_rows == 1){
		//get blog info
		$query = "SELECT * FROM cc_" . $tableprefix . "modules WHERE id='" . $tableid . "' LIMIT 1";
		$bloginfo = fetch($query); 
		
		//display blog
		$blogpost = $result->fetch_assoc();
		echo '<div class="cc-blogtitle">';
		echo '<a href="' . $siteroot . $bloginfo['slug'] . "/" . $blogpost['slug'] . '">';
		echo $blogpost['title'] . '</div>';
		echo '</a>';
		echo '<div class="cc-blogpublishtime">' . date($dateformat,$blogpost['publishtime']) . $lang['at'] . date($timeformat,$blogpost['publishtime']) . '</div>';
		echo '<div class="cc-blogcontent">' . $blogpost['content'] . '</div>';
		
		$query = "SELECT DISTINCT tag FROM cc_" . $tableprefix . "blogs_tags WHERE blogid='" . $blogpost['id'] . "'";
		$result = $z->query($query);
		if($result->num_rows > 0){
			$divided = false;
			echo '<div class="cc-tagline">' . $lang['tagline'] . ': ';
			while($tag = $result->fetch_assoc()){
				if($divided) echo ", ";
				$divided = true;
				echo '<a href="' . $siteroot . $bloginfo['slug'] . "/search/" . $tag['tag'] . '">' . $tag['tag'] . '</a>';
			}
			echo '</div>';
		}
		
		switch($comments){
			case "displaycomments":
				echo '<div class="cc-blogcommentheader">Comments</div>';
				echo '<div class="cc-blogcommentbody">';
				?>
				<div id="disqus_thread"></div>
					<script type="text/javascript">
						var disqus_shortname = '<?=$disqusname?>'; 
						var disqus_identifier = '<?=$blogpost['commentid']?>';
						(function() {
							var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
							dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
							(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
						})();
					</script>
					<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
					<a href="http://disqus.com" class="dsq-brlink">blog comments powered by <span class="logo-disqus">Disqus</span></a>
				</div> 
				<?
				break;
			case "commentline":
				echo '<div class="cc-blogcommentlink"><a href="' . $siteroot . $bloginfo['slug'] . '/' . $blogpost['slug'] . '#disqus_thread" data-disqus-identifier="' . $blogpost['commentid'] . '">View/Post Comments</a></div>';
				?>
				<script type="text/javascript">
					var disqus_shortname = '<?=$disqusname?>'; 
					var disqus_identifier = '<?=$blogpost['commentid']?>';
					 (function () {
						var s = document.createElement('script'); s.async = true;
						s.type = 'text/javascript';
						s.src = 'http://' + disqus_shortname + '.disqus.com/count.js';
						(document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s);
					}());
				</script>
				<?
				break;
		}
	}
	
	//no post found, throw error
	else{
		echo '<div class="cc-nopost">' . $lang['thereisnopost'] . '</div>';
	}
	
}
function displayBlog($tableid,$perpage,$slug,$preview=false,$comments=false,$page=1){
	global $tableprefix;
	global $lang;
	global $siteroot;
	global $z;
	
	$slug = sanitizeSlug($slug);	
	$page = filterint($page);
	$perpage = filterint($perpage);
	$tableid = filterint($tableid);
	if($page == "") $page = 1;
	if($slug == "page") $slug == "";
	
	//search for slug
	$query = "SELECT * FROM cc_" . $tableprefix . "blogs WHERE slug LIKE '" . $slug . "'";
	if(!$preview){ $query .= " AND publishtime <=" . time(); }
	$query .= " LIMIT 1";
	$result = $z->query($query);
	
	//if slug found, display single blog post
	if($result->num_rows == 1){
		$row = $result->fetch_assoc();
		if($comments){
			$commentarg = "displaycomments";
		}else{
			$commentarg = "nocomments";
		}
		displaySinglePost($tableid,$row['id'],$commentarg);
	}
	
	//if slug not found, display blog page
	else{
		$query = "SELECT * FROM cc_" . $tableprefix . "blogs WHERE blog='" . $tableid . "'";
		if(!$preview){ $query .= " AND publishtime <=" . time(); }
		$allresults = $z->query($query);
		
		$pagecount = floor($allresults->num_rows / $perpage) + 1;
		
		//display posts
		echo '<div class="cc-blogpage">';
		displayPosts($tableid,($perpage*($page-1)),$perpage,$preview,$comments);
		echo '</div>';
		
		//display page navigations
		if($pagecount > 1){
		
			$query = "SELECT * FROM cc_" . $tableprefix . "modules WHERE id='" . $tableid . "' LIMIT 1";
			$bloginfo = fetch($query);
			
			echo '<div class="cc-blogprevnext">';
			if($page > 1){
				echo '<a href="' . $siteroot . $bloginfo['slug'] . "/page/" . ($page-1) . '">' . $lang['blogprev'] . '</a>';
			}
			echo '&nbsp;&nbsp;&nbsp;';
			if($page < $pagecount){
				echo '<a href="' . $siteroot . $bloginfo['slug'] . "/page/" . ($page+1) . '">' . $lang['blognext'] . '</a>';
			}
			echo '</div><div class="cc-blogpages">' . $lang['userpage'] . ' ';
			for($i=1;$i<=$pagecount;$i++){
				if($page != $i){
					echo '<a href="' . $siteroot . $bloginfo['slug'] . "/page/" . $i . '">';
				}
				echo $i;
				if($page != $i){
					echo '</a> ';
				}else{
					echo ' ';
				}
			}
			echo '</div>';
		}
	}
		
}
function displayPosts($tableid,$start,$numposts,$preview=false,$comments=false){
	global $disqusname;
	global $tableprefix;
	global $dateformat;
	global $timeformat;
	global $lang;
	global $z;
	
	$start = filterint($start);
	$numposts = filterint($numposts);
	$tableid = filterint($tableid);
	
	$query = "SELECT * FROM cc_" . $tableprefix . "blogs WHERE blog='" . $tableid . "'";
	if(!$preview){ $query .= " AND publishtime <=" . time(); }
	$query .= " ORDER BY publishtime DESC LIMIT " . $start . "," . $numposts;
	$result = $z->query($query);
	
	//throw error if no blog posts exist
	if($result->num_rows==0){
		echo '<div class="cc-nopost">' . $lang['noblogpostsuser'] . '</div>';
	}else{
		$divided = false;
		while($row = $result->fetch_assoc()){
			if($divided){
				echo '<div class="cc-postdivider"></div>';
			}
			else{
				$divided = true;
			}
			if($comments){
				$commentarg = "commentline";
			}else{
				$commentarg = "nocomments";
			}
			displaySinglePost($tableid,$row['id'],$commentarg);
		}
	}
}
//COMIC SEARCH
function comicSearch($comicid,$tag,$numresults,$page=1){
	global $z;
	global $tableprefix;
	global $lang;
	global $siteroot;

	$tag = str_replace("20"," ",$tag);
	$tag = sanitize($tag);	
	$page = filterint($page);
	$numresults = filterint($numresults);
	$comicid = filterint($comicid);
	if($page == "") $page = 1;

	$query = "SELECT * FROM cc_" . $tableprefix . "modules WHERE id='" . $comicid . "'";
	$comicinfo = fetch($query);
	$query = "SELECT DISTINCT comicid FROM cc_" . $tableprefix . "comics_tags WHERE tag='" . $tag . "' AND comic='" . $comicid . "' AND publishtime <=" . time();
	$allresults = $z->query($query);
	$query = "SELECT DISTINCT comicid FROM cc_" . $tableprefix . "comics_tags WHERE tag='" . $tag . "' AND comic='" . $comicid . "' AND publishtime <=" . time() . " ORDER BY publishtime ASC LIMIT " . ($numresults*($page-1)) . "," . $numresults . "";
	$result = $z->query($query);
	
	$pagecount = floor($allresults->num_rows / $numresults);
	if(($allresults->num_rows % $numresults) != 0) $pagecount++;
	
	echo '<div class="cc-searchheader">' . $lang['comicstagged'] . '"' . $tag . '" - ' . $lang['page'] . ' ' . $page . '</div><div class="cc-searchbody">';
	if($result->num_rows==0){
		echo $lang['noresultsfound'];
	}else{
		while($row = $result->fetch_assoc()){
			$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE id='" . $row['comicid'] . "' AND comic='" . $comicid . "' AND publishtime <= " . time();
			$comic = fetch($query);
			echo '<div class="cc-searchbox"><a href="' . $siteroot . $comicinfo['slug'] . '/' . $comic['slug'] . '"><div class="cc-searchcomicname">' . $comic['comicname'] . '</div><div class="cc-searchcomicimgbox"><img class="cc-searchcomicimage" src="' . $siteroot . 'comics/' . $comic['imgname'] . '" /></div></a></div>';
		}
	}
	echo '</div><div style="clear:both"></div>';
	
	if($pagecount > 1){
		echo '<div class="cc-searchprevnext">';
		if($page > 1){
			echo '<a href="' . $siteroot . $comicinfo['slug'] . "/search/" . $tag . "/" . ($page-1) . '">' . $lang['searchprev'] . '</a>';
		}
		echo '&nbsp;&nbsp;&nbsp;';
		if($page < $pagecount){
			echo '<a href="' . $siteroot . $comicinfo['slug'] . "/search/" . $tag . "/" . ($page+1) . '">' . $lang['searchnext'] . '</a>';
		}
		echo '</div><div class="cc-searchpages">' . $lang['page'] . ' ';
		for($i=1;$i<=$pagecount;$i++){
			echo ' ';
			if($page != $i){
				echo '<a href="' . $siteroot . $comicinfo['slug'] . "/search/" . $tag . "/" . $i . '">';
			}
			echo $i;
			if($page != $i){
				echo '</a>';
			}
		}
		echo '</div>';
	}
}
//BLOG SEARCH
function blogSearch($blogid,$tag,$numresults,$page=1){
	global $z;
	global $tableprefix;
	global $lang;
	global $siteroot;	
	global $dateformat;
	
	$tag = sanitizeAlphanumeric($tag);	
	$page = filterint($page);
	$numresults = filterint($numresults);
	$blogid = filterint($blogid);
	if($page == "") $page = 1;

	$query = "SELECT * FROM cc_" . $tableprefix . "modules WHERE id='" . $blogid . "'";
	$bloginfo = fetch($query);
	$query = "SELECT DISTINCT blogid FROM cc_" . $tableprefix . "blogs_tags WHERE tag='" . $tag . "' AND blog='" . $blogid . "' AND publishtime <=" . time();
	$allresults = $z->query($query);
	$query = "SELECT DISTINCT blogid FROM cc_" . $tableprefix . "blogs_tags WHERE tag='" . $tag . "' AND blog='" . $blogid . "' AND publishtime <=" . time() . " ORDER BY publishtime ASC LIMIT " . ($numresults*($page-1)) . "," . $numresults . "";
	$result = $z->query($query);
	
	$pagecount = floor($allresults->num_rows / $numresults);
	if(($allresults->num_rows % $numresults) != 0) $pagecount++;
	
	echo '<div class="cc-searchheader">' . $lang['blogstagged'] . '"' . $tag . '" - ' . $lang['page'] . ' ' . $page . '</div><div class="cc-searchbody">';
	if($result->num_rows==0){
		echo $lang['noresultsfound'];
	}else{
		while($row = $result->fetch_assoc()){
			$query = "SELECT * FROM cc_" . $tableprefix . "blogs WHERE id='" . $row['blogid'] . "' AND blog='" . $blogid . "' AND publishtime <= " . time();
			$blog = fetch($query);
			echo '<div class="cc-searchblogtitle"><a href="' . $siteroot . $bloginfo['slug'] . '/' . $blog['slug'] . '">' . $blog['title'] . ' - ' . date($dateformat,$blog['publishtime']) . '</a></div>';
		}
	}
	echo '</div><div style="clear:both"></div>';
	
	if($pagecount > 1){
		echo '<div class="cc-searchprevnext">';
		if($page > 1){
			echo '<a href="' . $siteroot . $bloginfo['slug'] . "/search/" . $tag . "/" . ($page-1) . '">' . $lang['searchprev'] . '</a>';
		}
		echo '&nbsp;&nbsp;&nbsp;';
		if($page < $pagecount){
			echo '<a href="' . $siteroot . $bloginfo['slug'] . "/search/" . $tag . "/" . ($page+1) . '">' . $lang['searchnext'] . '</a>';
		}
		echo '</div><div class="cc-searchpages">' . $lang['page'] . ' ';
		for($i=1;$i<=$pagecount;$i++){
			echo ' ';
			if($page != $i){
				echo '<a href="' . $siteroot . $bloginfo['slug'] . "/search/" . $tag . "/" . $i . '">';
			}
			echo $i;
			if($page != $i){
				echo '</a>';
			}
		}
		echo '</div>';
	}
}

?>