<?php

class widget_oobgolf {
	function getSession () {
 		$data = get_option('widget_oobgolf');

		$url = "https://www.oobgolf.com/api/get_session.php?username=" . $data['u'] . "&password=" . $data['p'] . "&dev=" . $data['apiKey'];
		$xml = file_get_contents($url);

		$dom = new DOMDocument;
		$dom = DOMDocument::loadXML($xml);
		$sessionNode = $dom->getElementsByTagName("session"); 

		return $sessionNode->item(0)->nodeValue;
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
 		$data = get_option('widget_oobgolf');
		$url = "https://www.oobgolf.com/api/get_scores.php?session=" . $session . "&dev=" . $data['apiKey'];
		$xml = file_get_contents($url);
		
		$dom = new DOMDocument;
		$dom = DOMDocument::loadXML($xml);
		
		$scores = $dom->getElementsByTagName("score"); 

		
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
			
			echo "\t\t\t\t\t\t<tr class='oobgolfRoundItem'><td><a href=\"{$data['relativePath']}/oobgolfScoreDetail.php?courseId=$courseId&scoreId=$scoreId&height=240&width=650\" class=\"thickbox\">$date</a></td><td><a href=\"{$data['relativePath']}/oobgolfScoreDetail.php?courseId=$courseId&scoreId=$scoreId&height=240&width=650\" class=\"thickbox\" title='$date - $courseName'>$courseName</a></td><td class='grossScore' style='font-weight: bold;'>$grossScore</td></tr>\n"; 
		}
	}
	
	function renderRecentRounds () {
		$session = widget_oobgolf::getSession();
		
		?>
		<li class="sidebaritem widget_oobgolf">
			<div class="sidebarbox" style="display: block;">			
				<h2 class="widgettitle">oobgolf Rounds</h2>
				<div id="oobgolfRoundsWidget" style="display: block;">
					<table id="oobgolfRoundsList" style="width: 100%">
						<tbody>
		<?php
		
		$rounds = widget_oobgolf::getRecentRounds($session);
		
		?>
						</tbody>
					</table>
				</div>
			</div>
		</li>
		<?php
	}	
	
	function controlRecentRounds () {
		$data = get_option('widget_oobgolf');
		?>
		<style>
			input.color { width: 5em; border: 1px solid #aaaaaa; margin-right: .5em; } 
			input.oobgolf { border: 1px solid #aaaaaa; margin-right: .5em; }
		</style>
		<p><label><input class="oobgolf" name="widget_oobgolf_recentrounds_u" type="text" value="<?php echo $data['u']; ?>" />User</label></p>
		<p><label><input class="oobgolf" name="widget_oobgolf_recentrounds_p" type="password" value="<?php echo $data['p']; ?>" />Password</label></p>
		<?php
		if (isset($_POST['widget_oobgolf_recentrounds_u'])){
			$data['u'] = attribute_escape($_POST['widget_oobgolf_recentrounds_u']);
			$data['p'] = attribute_escape($_POST['widget_oobgolf_recentrounds_p']);
			update_option('widget_oobgolf', $data);
		}
	}
	function controlDevChart () {
		$data = get_option('widget_oobgolf');
		?>
		<style>
			input.color { width: 5em; height: 25px; border: 1px solid #aaaaaa; margin-right: .5em; } 
			input.oobgolf { border: 1px solid #aaaaaa; margin-right: .5em; }
			input.oobgolfxy { width: 3em; border: 1px solid #aaaaaa; margin-right: .5em; }
		</style>
		<p><label><input class="oobgolf" name="widget_oobgolf_u" type="text" value="<?php echo $data['u']; ?>" />User</label></p>
		<p><label><input class="oobgolf" name="widget_oobgolf_p" type="password" value="<?php echo $data['p']; ?>" />Password</label></p>
		<p><label><input class="oobgolfxy" name="widget_oobgolf_devChartX" type="text" value="<?php echo $data['devChartX']; ?>" />DevChart Width</label></p>
		<p><label><input class="oobgolfxy" name="widget_oobgolf_devChartY" type="text" value="<?php echo $data['devChartY']; ?>" />DevChart Height</label></p>
		<p><label><input class="color" name="widget_oobgolf_devChartBackground" type="text" value="<?php echo $data['devChartBackground']; ?>" />DevChart BG</label></p>
		<p><label><input class="color" name="widget_oobgolf_devChartScaleColor" type="text" value="<?php echo $data['devChartScaleColor']; ?>" />DevChart Scale</label></p>
		<p><label><input class="color" name="widget_oobgolf_devChartGridColor" type="text" value="<?php echo $data['devChartGridColor']; ?>" />DevChart Grid</label></p>
		<p><label><input class="color" name="widget_oobgolf_devChartLegendFontColor" type="text" value="<?php echo $data['devChartLegendFontColor']; ?>" />DevChart Legend Font</label></p>
		<?php
		if (isset($_POST['widget_oobgolf_u'])){
			$data['u'] = attribute_escape($_POST['widget_oobgolf_u']);
			$data['p'] = attribute_escape($_POST['widget_oobgolf_p']);
			$data['devChartX'] = attribute_escape($_POST['widget_oobgolf_devChartX']);
			$data['devChartY'] = attribute_escape($_POST['widget_oobgolf_devChartY']);
			$data['devChartBackground'] = attribute_escape($_POST['widget_oobgolf_devChartBackground']);
			$data['devChartScaleColor'] = attribute_escape($_POST['widget_oobgolf_devChartScaleColor']);
			$data['devChartGridColor'] = attribute_escape($_POST['widget_oobgolf_devChartGridColor']);
			$data['devChartLegendFontColor'] = attribute_escape($_POST['widget_oobgolf_devChartLegendFontColor']);

			update_option('widget_oobgolf', $data);
		}
	}

	function register () {
		register_sidebar_widget('oobgolf Development', array('widget_oobgolf','renderDevChart'));
		register_sidebar_widget('oobgolf Rounds', array('widget_oobgolf','renderRecentRounds'));
		register_widget_control('oobgolf Development', array('widget_oobgolf','controlDevChart'));
		register_widget_control('oobgolf Rounds', array('widget_oobgolf','controlRecentRounds'));
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
					"apiKey"					=>'K2XLS-C2DL7-MBASA-D23SG',
					"devChartId"				=> 'Map_devChart.map',
					"path"						=> get_settings('home').'/wp-content/plugins/'.dirname(plugin_basename(__FILE__)),
					"relativePath"				=> '/wp-content/plugins/'.dirname(plugin_basename(__FILE__)),
					"devChartX"					=> 300,
					"devChartY"					=> 230,
					"devChartBackground"		=> "000000",
					"devChartScaleColor"		=> "EBEBEB",
					"devChartGridColor"			=> "323232",
					"devChartLegendFontColor"	=> "FFFFFF"
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
			update_option('widget_oobgolf' , $data);
		}
	}	
	

	function deactivate() {
		//delete_option('widget_oobgolf');
	}
}
