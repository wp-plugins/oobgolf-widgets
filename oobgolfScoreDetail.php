<html>
<head>
<title>oobgolfScoreDetail</title>
</head>
<body>
<center>
<?php

require('../../../wp-blog-header.php');
require_once('oobgolf.class.php');
$wpData = get_option('widget_oobgolf');
$cwd = $wpData['relativePath'];

$session = widget_oobgolf::getSession();
$scoreId = $_GET['scoreId'];
$courseId = $_GET['courseId'];
$url = "https://www.oobgolf.com/api/get_score_detail.php?id=$scoreId&session=$session&dev=" . $wpData['apiKey'];
$xml = file_get_contents($url);
$dom = new DOMDocument;
$dom = DOMDocument::loadXML($xml);

$xpath = new DOMXPath($dom);

$score = $dom->getElementsByTagName("score")->item(0);
$club = $score->getElementsByTagName("club")->item(0);
$course = $club->getElementsByTagName("course")->item(0);
$tee = $course->getElementsByTagName("tee")->item(0);

$scoreUrl = $score->getElementsByTagName("url")->item(0)->nodeValue;
$teeName = $tee->getElementsByTagName("name")->item(0)->nodeValue;
$teeRating = $tee->getElementsByTagName("usgaRating")->item(0)->nodeValue;
$teeSlope = $tee->getElementsByTagName("usgaSlope")->item(0)->nodeValue;
$teeColor = $tee->getElementsByTagName("color")->item(0)->nodeValue;

$courseName = $course->getElementsByTagName("name")->item(0)->nodeValue;
if ($courseName == "") { $courseName = $club->getElementsByTagName("name")->item(0)->nodeValue; }

$teeHoles = $tee->getElementsByTagName("hole");

$holeCount = $teeHoles->length;

$teeHoleData = array();

for($i = 1 ; $i <= $holeCount; $i++)
{
	$holeNode = $xpath->query("//tee/hole[num=$i]");
	if ($holeNode->length == 1) {
		$distance = $holeNode->item(0)->getElementsByTagName("distance")->item(0)->nodeValue;
		$par = $holeNode->item(0)->getElementsByTagName("par")->item(0)->nodeValue;
		$handicap = $holeNode->item(0)->getElementsByTagName("handicap")->item(0)->nodeValue;
	}
	else
	{
		$distance = "N/A";
		$par = "N/A";
		$handicap = "N/A";
	}

	$holeNode = $xpath->query("/score/holes/hole[num=$i]");
	if ($holeNode->length == 1) {
		$score = $holeNode->item(0)->getElementsByTagName("score")->item(0)->nodeValue;
		$putts = $holeNode->item(0)->getElementsByTagName("putts")->item(0)->nodeValue;
		$fairways = $holeNode->item(0)->getElementsByTagName("fairways")->item(0)->nodeValue;
		$penalties = $holeNode->item(0)->getElementsByTagName("penalties")->item(0)->nodeValue;
		$sandShots = $holeNode->item(0)->getElementsByTagName("sand-shots")->item(0)->nodeValue;
		$teeDistance = $holeNode->item(0)->getElementsByTagName("tee-distance")->item(0)->nodeValue;
	}
	else
	{
		$score = "N/A";
		$putts = "N/A";
		$fairways = "N/A";
		$penalties = "N/A";
		$sandShots = "N/A";
		$teeDistance = "N/A";
	}

	array_push($teeHoleData, 
		array(
			"distance"=>$distance,
			"par"=>$par,
			"handicap"=>$handicap,
			"score"=>$score,
			"putts"=>$putts,
			"fairways"=>$fairways,
			"penalties"=>$penalties,
			"sand-shots"=>$sandShots,
			"tee-distance"=>$teeDistance
		)
	);
}		
		?>
		<style>
			.even { background-color: #EFEFEF; }
			.evensummary { background-color: #CFCFCF; font-weight: bold; }
			.odd  { background-color: #DDDDDD; }
			.oddsummary { background-color: #BBBBBB; font-weight: bold; }
			.header { background-color: #CCCCFF; }
			td { text-align: center; }
			.rowheader { text-align: left; }
			.bold { font-weight: bold; }
			.holeVCellDark { }
			.holeVCell { }
			.footer { font-size: 85%; font-style: italic; clear: both; }
		</style>
		<table cellpadding="2" cellspacing="1" class="holeTable smallText">
			<tr>
				<td width='150' class='holeHCellDark rowheader header bold'>HOLE</td>
				<?php
				
				for($i = 1 ; $i <= $holeCount; $i++)
				{	
					?>
					<td class='holeVCellDark bold header'><?php echo $i; ?></td>
					<?php if ($i == 9){ ?>
					<td class='holeVCellDark bold header'>OUT</td>
					<?php } ?>
					<?php if ($i == 18){ ?>
					<td class='holeVCellDark bold header'>IN</td>
					<?php } 
				}
				?>
				<td class='holeVCellDark bold header'>TOTAL</td>
			</tr>
			<tr>
				<td class='holeHCell odd'>
					<div style='float:right;font-size:7pt;padding-top:2px;padding-right:2px;font-weight:normal'><?php echo $teeRating;?>/<?php echo $teeSlope;?></div><div class='colorSquare' style='background-color: <?php echo $teeColor;?>;'></div>&nbsp;<?php echo $teeName;?>
				</td>
				<?php
				for($i = 1 ; $i <= $holeCount; $i++)
				{
					$distance = (int)$teeHoleData[$i-1]["distance"];
					$rt += $distance;
					?>
					<td class='holeVCellDark odd'><?php echo $distance; ?></td>
					<?php if ($i == 9)
						  { $front9 = $rt; 
						  ?>
					<td class='holeVCellDark oddsummary'><?php echo number_format($rt, 0); ?></td>
					<?php } ?>
					<?php if ($i == 18){ ?>
					<td class='holeVCellDark oddsummary'><?php echo number_format($rt-$front9, 0); ?></td>
					<?php } 
				}
				?>
				<td class='holeVCellDark oddsummary'><?php echo number_format($rt, 0); ?></td>
			</tr>
			<tr>
				<td class='holeHCell rowheader even'>Par</td>
				<?php
				$rt = 0;
				for($i = 1 ; $i <= $holeCount; $i++)
				{
					$par = (int)$teeHoleData[$i-1]["par"];
					$rt += $par;
					?>
					<td class='holeVCellDark even'><?php echo $par; ?></td>
					<?php if ($i == 9)
						  { $front9 = $rt;
							?>
					<td class='holeVCellDark evensummary'><?php echo $rt; ?></td>
					<?php } ?>
					<?php if ($i == 18)	{ ?>
					<td class='holeVCellDark evensummary'><?php echo $rt-$front9; ?></td>
					<?php } 
				}
				?>
				<td class='holeVCellDark evensummary'><?php echo $rt; ?></td>
			</tr>
			<tr>
				<td class='holeHCell rowheader odd' style='background-color: #FDFCBA;'>Score</td>
				<?php
				$rt = 0;
				for($i = 1 ; $i <= $holeCount; $i++)
				{
					$score = (int)$teeHoleData[$i-1]["score"];
					$diff = $score - (int)$teeHoleData[$i-1]["par"];
					if ($diff == -3)
						$bgcolor = "#DEA19B";
					elseif ($diff == -2)
						$bgcolor = "#9BBDDE";
					elseif ($diff == -1)
						$bgcolor = "#A2C488";
					elseif ($diff == 0)
						$bgcolor = "#FDFCBA";
					elseif ($diff == 1)
						$bgcolor = "#FED35F";
					elseif ($diff == 2)
						$bgcolor = "#D6B9DB";
					elseif ($diff >= 3)
						$bgcolor = "#CDC785";
					else
						$bgcolor = "#FFFFFF";
					
					$rt += $score;
					?>
					<td class='holeVCellDark odd' style='background-color: <?php echo $bgcolor; ?>;'><?php echo $score; ?></td>
					<?php if ($i == 9)
							{ $front9 = $rt;
								?>
					<td class='holeVCellDark oddsummary'><?php echo $rt; ?></td>
					<?php } ?>
					<?php if ($i == 18)	{ ?>
					<td class='holeVCellDark oddsummary'><?php echo $rt-$front9; ?></td>
					<?php } 
				}
				?>
				<td class='holeVCellDark oddsummary'><?php echo $rt; ?></td>
			</tr>
			<tr>
				<td class='holeHCell rowheader even'>+/-</td>
				<?php
				$rt = 0;
				for($i = 1 ; $i <= $holeCount; $i++)
				{
					$diff = (int)$teeHoleData[$i-1]["score"] - (int)$teeHoleData[$i-1]["par"];
					if ($diff > 0)
						$offset = "+$diff";
					else
						$offset = $diff;
					
					$rt += $diff;
					?>
					<td class='holeVCellDark even'><?php echo $offset; ?></td>
					<?php if ($i == 9)
							{ $front9 = $rt;
							?>
					<td class='holeVCellDark evensummary'><?php echo $rt; ?></td>
					<?php } ?>
					<?php if ($i == 18){ ?>
					<td class='holeVCellDark evensummary'><?php echo $rt-$front9; ?></td>
					<?php } 
				}
				?>
				<td class='holeVCellDark evensummary'><?php echo $rt; ?></td>
			</tr>
			<tr>
				<td class='holeHCellDark rowheader odd'>Fairway</td>
				
				<?php
				$rt = 0;
				for($i = 1 ; $i <= $holeCount; $i++)
				{
					$fairway = $teeHoleData[$i-1]["fairways"];
					if ($fairway == "2")
						$fairwayimage = "<img src='$cwd/images/arrow_right_red.png' width='16' height='16'/>";
					elseif ($fairway == "3")
						$fairwayimage = "<img src='$cwd/images/arrow_left_red.png' width='16' height='16'/>";
					elseif ($fairway == "0")
						$fairwayimage = "<img src='$cwd/images/x_red.png' width='16' height='16'/>";
					elseif ($fairway == "1")
					{
						$fairwayimage = "<img src='$cwd/images/check2.png' width='16' height='16'/>";
						$rt++;
					}
					else
						$fairwayimage = "$fairway";
					
					?>
					<td class='holeVCellDark odd'><?php echo $fairwayimage; ?></td>
					<?php if ($i == 9)
							{ $front9 = $rt;
							?>
					<td class='holeVCellDark oddsummary'><?php echo round(($rt/9)*100) . '%'; ?></td>
					<?php } ?>
					<?php if ($i == 18){ ?>
					<td class='holeVCellDark oddsummary'><?php echo round((($rt-$front9)/9)*100) . '%'; ?></td>
					<?php } 
				}
				?>
				
				<td class='holeVCellDark oddsummary'><?php echo round(($rt/18)*100) . '%'; ?></td>				
			</tr>
			<tr>
				<td class='holeHCell rowheader even'>Sand Shots</td>
				<?php
				$rt = 0;
				for($i = 1 ; $i <= $holeCount; $i++)
				{
					$sandShots = (int)$teeHoleData[$i-1]["sand-shots"];
					$rt += $sandShots;
					?>
					<td class='holeVCellDark even'><?php echo $sandShots; ?></td>
					<?php if ($i == 9)
						{ $front9 = $rt;
						?>
					<td class='holeVCellDark evensummary'><?php echo $rt; ?></td>
					<?php } ?>
					<?php if ($i == 18){ ?>
					<td class='holeVCellDark evensummary'><?php echo $rt-$front9; ?></td>
					<?php } 
				}
				?>
				<td class='holeVCellDark evensummary'><?php echo $rt; ?></td>
			</tr>
			<tr>
				<td class='holeHCellDark rowheader odd'>Putts</td>
				<?php
				$rt = 0;
				for($i = 1 ; $i <= $holeCount; $i++)
				{
					$putts = (int)$teeHoleData[$i-1]["putts"];
					$rt += $putts;
					?>
					<td class='holeVCellDark odd'><?php echo $putts; ?></td>
					<?php if ($i == 9)
						{ $front9 = $rt;
						?>
					<td class='holeVCellDark oddsummary'><?php echo $rt; ?></td>
					<?php } ?>
					<?php if ($i == 18){ ?>
					<td class='holeVCellDark oddsummary'><?php echo $rt-$front9; ?></td>
					<?php } 
				}
				?>
				<td class='holeVCellDark oddsummary'><?php echo $rt; ?></td>
			</tr>
			<tr>
				<td class='holeHCell rowheader even'>GIR</td>
				<?php
				$rt = 0;
				for($i = 1 ; $i <= $holeCount; $i++)
				{
					$toGreen = (int)$teeHoleData[$i-1]["score"] - (int)$teeHoleData[$i-1]["putts"];
					if ($toGreen <= (int)$teeHoleData[$i-1]["par"] - 2) 
					{
						$gir = "<img src='$cwd/images/check2.png' width='16' height='16'/>";
						$rt++;
					}
					else
						$gir = "<img src='$cwd/images/x_red.png' width='16' height='16'/>";
						
					?>
					<td class='holeVCellDark even'><?php echo $gir; ?></td>
					<?php if ($i == 9)
							{ $front9 = $rt;
							?>
					<td class='holeVCellDark evensummary'><?php echo round(($rt/9)*100) . '%'; ?></td>
					<?php } ?>
					<?php if ($i == 18){ ?>
					<td class='holeVCellDark evensummary'><?php echo round((($rt-$front9)/9)*100) . '%'; ?></td>
					<?php } 
				}
				?>
				<td class='holeVCellDark evensummary'><?php echo round(($rt/18)*100) . '%'; ?></td>
				
			</tr>
		</table>
<br/>
<style>
	.colorSquare {
		border: 1px solid black;
		width: 16px;
		height: 16px;
		float: left;
		}
</style>
<div style="height: 25px; text-align: center; display: inline;">
	<div class='colorSquare' style='background-color: #DEA19B;'></div>
	<div class="smallText" style="float:left;margin-left:10px; margin-right:20px;">Albatross</div>
	<div class='colorSquare' style='background-color: #9BBDDE;'></div>
	<div class="smallText" style="float:left;margin-left:10px; margin-right:20px;">Eagle</div>
	<div class='colorSquare' style='background-color: #A2C488;'></div>
	<div class="smallText" style="float:left;margin-left:10px; margin-right:20px;">Birdie</div>
	<div class='colorSquare' style='background-color: #FDFCBA;'></div>
	<div class="smallText" style="float:left;margin-left:10px; margin-right:20px;">Par</div>
	<div class='colorSquare' style='background-color: #FED35F;'></div>
	<div class="smallText" style="float:left;margin-left:10px; margin-right:20px;">Bogie</div>
	<div class='colorSquare' style='background-color: #D6B9DB;'></div>
	<div class="smallText" style="float:left;margin-left:10px; margin-right:20px;">Double Bogie</div>
	<div class='colorSquare' style='background-color: #CDC785;'></div>
	<div class="smallText" style="float:left;margin-left:10px; margin-right:20px;">Other</div>
</div>
<div class="footer">round stats courtesy of <a href="<?php echo $scoreUrl; ?>">oobgolf.com</a></div>
</center>
</body>
</html>
