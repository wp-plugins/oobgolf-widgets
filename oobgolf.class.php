<?php


class widget_oobgolf {
	function getUrlContents($url) {
		$fileContents = file_get_contents($url);
		return $fileContents;
	}
	
	function getSession () {
 		$data = get_option('widget_oobgolf');

		$url = "https://www.oobgolf.com/api/get_session.php?username=" . $data['u'] . "&password=" . $data['p'] . "&dev=" . $data['apiKey'];
		$xml = widget_oobgolf::getUrlContents($url);
		
		if (strlen($xml) > 0) {
			$dom = new DOMDocument;
			$dom = DOMDocument::loadXML($xml);
			$sessionNode = $dom->getElementsByTagName("session"); 

			return $sessionNode->item(0)->nodeValue;
		} else {
			return '';
		}
	}

	function renderDevChart () {
 		$data = get_option('widget_oobgolf');
		?>
		<li class="sidebaritem widget_oobgolf">
			<div class="sidebarbox">			
				<h2 class="widgettitle">oobgolf Development</h2>
				<div id="oobgolfDevChartWidget">
					<DIV ID="overDiv" STYLE="position:absolute; visibility:hidden; z-index:1000; text-align: center;"></DIV>
					<IMG ID=devChartImg SRC='<?php echo $data['path']; ?>/renderDevChart.php?MapID=<?php echo $data['devChartId'];?>' WIDTH=<?php echo $data['devChartX']; ?> HEIGHT=<?php echo $data['devChartY']; ?> BORDER=0 OnMouseMove='getMousePosition(event);' OnMouseOut='nd();'>
					<div style="display: block; text-align: center; font-style: italic; font-size: 85%;">stats courtesy of <a href="http://www.oobgolf.com/">oobgolf.com</a></div>
					<SCRIPT>LoadImageMap("devChartImg","<?php echo $data['path']; ?>/renderDevChart.php?Action=GetImageMap&MapID=<?php echo $data['devChartId'] ?>");</SCRIPT>
				</div>
			</div>
		</li>
		<?php
	}
	
	function getRecentRounds ($session) {
		$rounds = '';
 		$data = get_option('widget_oobgolf');
		$localPath = dirname(__FILE__);

		$url = "https://www.oobgolf.com/api/get_scores.php?session=" . $session . "&dev=" . $data['apiKey'];
		$xml = widget_oobgolf::getUrlContents($url);
		
		$dom = new DOMDocument;
		$dom = DOMDocument::loadXML($xml);
		
		$scores = $dom->getElementsByTagName("score"); 

		$scoreCount = 0;
		
		$localDom = new DOMDocument;
		if (file_exists($localPath . '/Cache/oobgolfWidgets.xml')) {
			$localDom = DOMDocument::load($localPath . '/Cache/oobgolfWidgets.xml');
			$localScores = $localDom->getElementsByTagName("scores")->item(0);
		} else {
			$rootElement = $localDom->createElement("oobgolfWidgets");
			$localScores = $localDom->createElement("scores");
			$localDom->appendChild($rootElement);
			$rootElement->appendChild($localScores);
		}
		
		$localScoreCount = $localScores->getElementsByTagName("score")->length;
		$localXpath = new DOMXPath($localDom);
		
		foreach( $scores as $score ) 
		{ 
			$clubNode = $score->getElementsByTagName("club")->item(0);
			$courseNode = $clubNode->getElementsByTagName("course")->item(0);
			$teeNode = $courseNode->getElementsByTagName("tee")->item(0);
			
			$courseId = $courseNode->getElementsByTagName("id")->item(0)->nodeValue;
			$scoreId = $score->getElementsByTagName("id")->item(0)->nodeValue;
			$scoreUrl = $score->getElementsByTagName("url")->item(0)->nodeValue;
			$date = $score->getElementsByTagName("date")->item(0)->nodeValue;
			$grossScore = $score->getElementsByTagName("gross-score")->item(0)->nodeValue;
			$netScore = $score->getElementsByTagName("net-score")->item(0)->nodeValue;

			$courseName = $courseNode->getElementsByTagName("name")->item(0)->nodeValue;
			if ($courseName == "") { $courseName = $clubNode->getElementsByTagName("name")->item(0)->nodeValue; }
			
			$usgaSlope = $teeNode->getElementsByTagName("usgaSlope")->item(0)->nodeValue;
			$usgaRating = $teeNode->getElementsByTagName("usgaRating")->item(0)->nodeValue;
								
			if ($scoreCount < (int)$data['roundsToShow']) {
				$rounds .= "\t\t\t\t\t\t<tr class='oobgolfRoundItem'><td><a href=\"{$data['relativePath']}/oobgolfScoreDetail.php?courseId=$courseId&scoreId=$scoreId&height=240&width=650\" class=\"thickbox\">$date</a></td><td><a href=\"{$data['relativePath']}/oobgolfScoreDetail.php?courseId=$courseId&scoreId=$scoreId&height=240&width=650\" class=\"thickbox\" title='$date - $courseName'>$courseName</a></td><td class='grossScore' style='font-weight: bold;'>$grossScore</td></tr>\n"; 
				$scoreCount++;				
			}
			
			$localScoreNode = $localXpath->query("/oobgolfWidgets/scores/score[id=$scoreId]");
			
			if ($localScoreNode->length == 0) {
				// New Score, add it.
				
				$newScore = $localDom->createElement("score");
				$newScoreId = $localDom->createElement("id");
				$newScore->appendChild($newScoreId);
				$newText = $localDom->createTextNode($scoreId);
				$newScoreId->appendChild($newText);
				
				if ($localScoreCount > 0 && $data['tu'] . $data['tp'] != '') {
					// This isn't the first load - make tweet if enabled
					$twitter = new oobgolfWidgetsTwitter($data['tu'],$data['tp']);
					$status = $twitter->update('Entered a new score on oobgolf.com - ' . $grossScore . ' at ' . $courseName . '.  ' . $scoreUrl);
					if ($status) {
						$localScores->appendChild($newScore);
					}
				} else {
					// No previous scores, do the initial population (don't flood tweets when they first turn this on)
					$localScores->appendChild($newScore);
				}
			}
		}
		$localDom->save($localPath . '/Cache/oobgolfWidgets.xml');
		return $rounds;
	}
	
	function renderRecentRounds () {
		$session = widget_oobgolf::getSession();
		$data = get_option('widget_oobgolf');
		$localPath = dirname(__FILE__);
		$cacheFile = $localPath . '/Cache/recentRounds-' . md5(serialize($data));
		
		if(file_exists($cacheFile) && filectime($cacheFile) > (mktime() - ($data['oobgolfCacheMinutes'] * 60))) {
			echo file_get_contents($cacheFile);
		} else {
			// No Cache, make new cache
			
			if ($session != '')
				$rounds = widget_oobgolf::getRecentRounds($session);
			else
				$rounds = '<tr><td>Unable to contact oobgolf...</td></tr>';
				
			$widgetHTML = 
<<<WIDGETHTML
			
			<li class="sidebaritem widget_oobgolf">
				<div class="sidebarbox" style="display: block;">			
					<h2 class="widgettitle">oobgolf Rounds</h2>
					<div id="oobgolfRoundsWidget" style="display: block;">
						<table id="oobgolfRoundsList" style="width: 100%">
							<tbody>
$rounds
							</tbody>
						</table>
					</div>
				</div>
			</li>
WIDGETHTML;

			file_put_contents($cacheFile, $widgetHTML);
			echo $widgetHTML;
		}
	}	
	
	function controlRecentRounds () {
		$data = get_option('widget_oobgolf');
		if (isset($_POST['widget_oobgolf_recentrounds_toshow'])){
			$data['roundsToShow'] = attribute_escape($_POST['widget_oobgolf_recentrounds_toshow']);
			update_option('widget_oobgolf', $data);
			$data = get_option('widget_oobgolf');
		}
		?>
		<style>
			input.color { width: 5em; border: 1px solid #aaaaaa; margin-right: .5em; } 
			input.oobgolf { border: 1px solid #aaaaaa; margin-right: .5em; }
		</style>
		<p><label>
			<select name="widget_oobgolf_recentrounds_toshow">
				<option value="5">5</option>
				<option value="10">10</option>
				<option value="15">15</option>
				<option value="20">20</option>
				<option value="30">30</option>
			</select>Rounds to Show</label></p>
		<?php
	}
	function controlDevChart () {
		$data = get_option('widget_oobgolf');
		if (isset($_POST['widget_oobgolf_devChartX'])){
			$data['devChartX'] = attribute_escape($_POST['widget_oobgolf_devChartX']);
			$data['devChartY'] = attribute_escape($_POST['widget_oobgolf_devChartY']);
			$data['devChartBackground'] = attribute_escape($_POST['widget_oobgolf_devChartBackground']);
			$data['devChartScaleColor'] = attribute_escape($_POST['widget_oobgolf_devChartScaleColor']);
			$data['devChartGridColor'] = attribute_escape($_POST['widget_oobgolf_devChartGridColor']);
			$data['devChartLegendFontColor'] = attribute_escape($_POST['widget_oobgolf_devChartLegendFontColor']);
			$data['devChartLegendLocation'] = attribute_escape($_POST['widget_oobgolf_devChartLegendLocation']);
			$data['pngCompressionLevel'] = attribute_escape($_POST['widget_oobgolf_pngCompressionLevel']);

			update_option('widget_oobgolf', $data);
			$data = get_option('widget_oobgolf');
		}
		
		?>
		<style>
			input.color { width: 5em; height: 25px; border: 1px solid #aaaaaa; margin-right: .5em; } 
			input.oobgolf { border: 1px solid #aaaaaa; margin-right: .5em; }
			input.oobgolfxy { width: 3em; border: 1px solid #aaaaaa; margin-right: .5em; }
		</style>
		<p><label><input class="oobgolfxy" name="widget_oobgolf_devChartX" type="text" value="<?php echo $data['devChartX']; ?>" />DevChart Width</label></p>
		<p><label><input class="oobgolfxy" name="widget_oobgolf_devChartY" type="text" value="<?php echo $data['devChartY']; ?>" />DevChart Height</label></p>
		<p><label><input class="color" name="widget_oobgolf_devChartBackground" type="text" value="<?php echo $data['devChartBackground']; ?>" />DevChart BG</label></p>
		<p><label><input class="color" name="widget_oobgolf_devChartScaleColor" type="text" value="<?php echo $data['devChartScaleColor']; ?>" />DevChart Scale</label></p>
		<p><label><input class="color" name="widget_oobgolf_devChartGridColor" type="text" value="<?php echo $data['devChartGridColor']; ?>" />DevChart Grid</label></p>
		<p><label><input class="color" name="widget_oobgolf_devChartLegendFontColor" type="text" value="<?php echo $data['devChartLegendFontColor']; ?>" />DevChart Legend Font</label></p>
		<p><label>
			<select name="widget_oobgolf_devChartLegendLocation">
				<option value="tr" <?php if ($data['devChartLegendLocation'] == 'tr') { echo 'SELECTED'; } ?> >Top Right</option>
				<option value="tl" <?php if ($data['devChartLegendLocation'] == 'tl') { echo 'SELECTED'; } ?> >Top Left</option>
				<option value="br" <?php if ($data['devChartLegendLocation'] == 'br') { echo 'SELECTED'; } ?> >Bottom Right</option>
				<option value="bl" <?php if ($data['devChartLegendLocation'] == 'bl') { echo 'SELECTED'; } ?> >Bottom Left</option>
			</select>Legend Location</label></p>
		<p><label>
			<select name="widget_oobgolf_pngCompressionLevel">
				<option value="0" <?php if ($data['pngCompressionLevel'] == 0) { echo 'SELECTED'; } ?> >0 (None)</option>
				<option value="3" <?php if ($data['pngCompressionLevel'] == 3) { echo 'SELECTED'; } ?> >3</option>
				<option value="6" <?php if ($data['pngCompressionLevel'] == 6) { echo 'SELECTED'; } ?> >6</option>
				<option value="9" <?php if ($data['pngCompressionLevel'] == 9) { echo 'SELECTED'; } ?> >9 (Max)</option>
			</select>Chart Compression</label></p>

		<?php
	}

	function adminMenu () {
		add_options_page('oobgolf Settings', 'oobgolf Settings', 8, __FILE__, array('widget_oobgolf','oobgolfSettings'));
	}
	
	function oobgolfSettings () {
		$data = get_option('widget_oobgolf');
		?>
		<div class="wrap">
		<h2>oobgolf Settings</h2>

		<form method="post" action="">
		<?php wp_nonce_field('update-options'); ?>

		 <?php

			if ($_POST['action'] == "update")
			{
				$data['u'] = $_POST['u'];
				$data['p'] = $_POST['p'];
				$data['tu'] = $_POST['tu'];
				$data['tp'] = $_POST['tp'];
				$data['oobgolfCacheMinutes'] = (int)$_POST['ce'];
				update_option('widget_oobgolf', $data);
				$data = get_option('widget_oobgolf');
			 }
		 ?>
		
		<table>
			<tr><td>oobgolf User</td><td><input type="text" name="u" value="<?php echo $data['u']; ?>" /></td></tr>
			<tr><td>oobgolf Password</td><td><input type="password" name="p" value="<?php echo $data['p']; ?>" /></td></tr>
			<tr><td>twitter User</td><td><input type="text" name="tu" value="<?php echo $data['tu']; ?>" /></td></tr>
			<tr><td>twitter Password</td><td><input type="password" name="tp" value="<?php echo $data['tp']; ?>" /></td></tr>
			<tr><td>Cache Expiration (minutes)</td><td><input type="text" name="ce" value="<?php echo $data['oobgolfCacheMinutes']; ?>" /></td></tr>
		</table>
		 
		<input type="hidden" name="page_options" value="null" />
		<input type="hidden" name="action" value="update" />

		<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Update Options &raquo;') ?>" />
		</p>
		</form>
		</div>
		<?php
	}
	
	function register () {
		register_sidebar_widget('oobgolf Development', array('widget_oobgolf','renderDevChart'));
		register_sidebar_widget('oobgolf Rounds', array('widget_oobgolf','renderRecentRounds'));
		register_widget_control('oobgolf Development', array('widget_oobgolf','controlDevChart'));
		register_widget_control('oobgolf Rounds', array('widget_oobgolf','controlRecentRounds'));
		add_action('admin_menu', array('widget_oobgolf','adminMenu'));
	}

	function addHeaderCode(){
		$data = get_option('widget_oobgolf');
		echo '<script type="text/javascript" src="' . $data['path'] . '/jquery-1.3.2.min.js"></script>' . "\n";
		echo '<script type="text/javascript" src="' . $data['path'] . '/thickbox-compressed.js"></script>' . "\n";
		echo '<script type="text/javascript" src="' . $data['path'] . '/pChart/pMap.js"></script>' . "\n";
		echo '<script type="text/javascript" src="' . $data['path'] . '/pChart/overlib.js"></script>' . "\n";
		echo '<link type="text/css" rel="stylesheet" href="' . $data['path'] . '/thickbox.css" />' . "\n";
	}
	function addAdminHeaderCode(){
		$data = get_option('widget_oobgolf');
		echo '<script type="text/javascript" src="' . $data['path'] . '/jscolor/jscolor.js"></script>' . "\n";
	}

	function activate() {
		$data = array(
					"u"							=>'',
					"p"							=>'',
					"tu"						=>'',
					"tp"						=>'',
					"apiKey"					=>'K2XLS-C2DL7-MBASA-D23SG',
					"devChartId"				=> 'Map_devChart.map',
					"path"						=> get_settings('home').'/wp-content/plugins/'.dirname(plugin_basename(__FILE__)),
					"relativePath"				=> '/wp-content/plugins/'.dirname(plugin_basename(__FILE__)),
					"devChartX"					=> 300,
					"devChartY"					=> 230,
					"devChartBackground"		=> "000000",
					"devChartScaleColor"		=> "EBEBEB",
					"devChartGridColor"			=> "323232",
					"devChartLegendFontColor"	=> "FFFFFF",
					"roundsToShow"				=> 5,
					"devChartLegendLocation"	=> "tr",
					"oobgolfCacheMinutes"		=> 60,
					"pngCompressionLevel"		=> '9'
				);
		
		/*		// Pre-2.6 compatibility   http://codex.wordpress.org/Determining_Plugin_and_Content_Directories
		if ( ! defined( 'WP_CONTENT_URL' ) )
			  define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
		if ( ! defined( 'WP_CONTENT_DIR' ) )
			  define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
		if ( ! defined( 'WP_PLUGIN_URL' ) )
			  define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
		if ( ! defined( 'WP_PLUGIN_DIR' ) )
			  define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
		*/
		
		if ( ! get_option('widget_oobgolf')){
			add_option('widget_oobgolf' , $data);
		} else {
			// Propagate existing settings
			$oldData = get_option('widget_oobgolf');
			foreach ($oldData as $key => $value) {
				$data[$key] = $value;
			}
			update_option('widget_oobgolf' , $data);
		}
	}	
	
	function deactivate() {
		//delete_option('widget_oobgolf');
	}
}
