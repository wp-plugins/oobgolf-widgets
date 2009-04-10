<?php

if ( isset($_GET["Action"]) ) { $Action = $_GET["Action"]; } else { $Action = "Draw"; }
if ( isset($_GET["MapID"]) )  { $MapID  = $_GET["MapID"]; }

// Standard inclusions   
include("pChart/pData.class");
include("pChart/pChart.class");
include("pChart/pCache.class"); 

if ( $Action == "GetImageMap" )
{
$Test = new pChart(300,230);
$Test->getImageMap($MapID, FALSE);
}

require('../../../wp-blog-header.php');
require_once('oobgolf.class.php');
$wpData = get_option('widget_oobgolf');

function hexrgb($hexstr) {
	$int = hexdec($hexstr);
	return array(
		0xFF & $int >> 0x10,
		0xFF & ($int >> 0x8),
		0xFF & $int
		);
}

$url = "http://www.oobgolf.com/golfers/charting.php?type=development&username=" . $wpData["u"];
$xml = file_get_contents($url);
$dom = new DOMDocument;
$dom = DOMDocument::loadXML($xml);

$chart = $dom->getElementsByTagName("chart")->item(0);
$chartData = $chart->getElementsByTagName("chart_data")->item(0);
$rows = $chartData->getElementsByTagName("row");

// Dataset definition 
$DataSet = new pData;

$seriesCount = 0;

foreach ($rows as $row) 
{
	$elements = $row->getElementsByTagName("*");
	$name = $elements->item(0)->nodeValue;
	
	$points = array();
	for($i = 1; $i < $elements->length; $i++)
	{
		if ($seriesCount == 0)
			array_push($points, strtotime($elements->item($i)->nodeValue));
		else
			array_push($points, $elements->item($i)->nodeValue)	;	
	}
	
	$DataSet->AddPoint($points, "Serie" . $seriesCount);
	
	if ($seriesCount == 0)
		$DataSet->SetAbsciseLabelSerie("Serie" . $seriesCount);
	else
	{
		$DataSet ->AddSerie("Serie" . $seriesCount);
		$DataSet->SetSerieName($name ,"Serie" . $seriesCount);
	}
	
	$seriesCount += 1;
}

$DataSet->SetXAxisFormat("date");

$chartId = 	"devChart_" . 
			$wpData['devChartLegendFontColor'] . 
			$wpData['devChartGridColor'] . 
			$wpData['devChartScaleColor'] . 
			$wpData['devChartGraphAreaColor'] . 
			$wpData['devChartBackground'] . 
			$wpData['devChartX'] . 
			$wpData['devChartY'];

// Cache definition 
$Cache = new pCache();
$Cache->GetFromCache($chartId,$DataSet->GetData()); 

// Flush prevoius imageMap if it exists
if (file_exists('tmp/' . $MapID && $MapID != '')) { unlink ('tmp/' . $MapID); }

// Initialise the graph
$Test = new pChart($wpData['devChartX'],$wpData['devChartY']);
$Test->setImageMap(TRUE,$MapID);
$Test->setFontProperties("Fonts/tahoma.ttf",8);

$color = hexrgb($wpData['devChartBackground']);
$Test->drawBackground($color[0],$color[1],$color[2]);

$Test->setGraphArea(25,20,$wpData['devChartX']-10,$wpData['devChartY']-55);
$Test->setDateFormat("Y-m-d");

$color = hexrgb($wpData['devChartScaleColor']);
$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,$color[0],$color[1],$color[2],TRUE,45,0,TRUE);

$color = hexrgb($wpData['devChartGridColor']);
$Test->drawGraphAreaGradient($color[0],$color[1],$color[2],200);
$Test->drawGrid(1,TRUE,$color[0],$color[1],$color[2],5);
 
// Draw the bar chart
$Test->setLineStyle(2);
$Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());  
$color = hexrgb($wpData['devChartLegendFontColor']);
$Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,$color[0]-1,$color[1]-1,$color[2]-1);     

// Draw the legend
$Test->setFontProperties("Fonts/tahoma.ttf",8);
$Test->drawLegend($wpData['devChartX']-100,20,$DataSet->GetDataDescription(),0,0,0,0,0,0,$color[0],$color[1],$color[2],FALSE);

// Render the picture
$Cache->WriteToCache($chartId,$DataSet->GetData(),$Test); 
$Test->Stroke();
?>