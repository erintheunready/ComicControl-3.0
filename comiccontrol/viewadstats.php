<?
//viewadstats.php
//view ad stats

//invoke header
include('includes/header.php'); 
?>
<script src="includes/chart.js"></script>
<?
echo '<h2>' . $lang['adstats'] . '</h2>';
$query = "SELECT * FROM (SELECT * FROM cc_" . $tableprefix . "adstats ORDER BY date DESC LIMIT 30) sub ORDER BY date ASC";
$labels = "";
$impdata = "";
$revdata = "";
$table = '<table><tr style="font-weight:bold"><td width="270px">' . $lang['date'] . '</td><td width="270px">' . $lang['impressions'] . '</td><td width="270px">' . $lang['estrevenue'] . '</td></tr>';
$result = $z->query($query);
$commaed = false;
$highestimp = 0;
$highestrev = 0;
while($row=$result->fetch_assoc()){
	$table.="<tr>";
	if($commaed){ 
		$labels .= ',';
		$impdata .= ',';
		$revdata .= ',';
	}
	$labels .= '"' . date("M d",strtotime($row['date'])) . '"';
	$table .= '<td>' . date("M d, Y",strtotime($row['date'])) . '</td>';
	$impdata .= $row['impressions'];
	$table .= '<td>' . number_format($row['impressions'],0,".",",") . '</td>'; 
	if($row['impressions'] > $highestimp) $highestimp = $row['impressions'];
	$revdata .= $row['revenue'];
	$table .= '<td>$' . number_format($row['revenue'],2,".",",") . '</td>';
	if($row['revenue'] > $highestrev) $highestrev = $row['revenue'];
	$commaed = true;
	$table.="</tr>";
}
$impfirst = $highestimp[0] + 1;
$zeroes = strlen($highestimp) - 1;
$y1top = $impfirst;
while(strlen($y1top) < $zeroes){
	$y1top .= "0";
}
$y1scale = $y1top;
$revfirst = $highestrev[0] + 1;
$zeroes = strlen(floor($highestrev)) - 1;
$y2top = $revfirst;
while(strlen($y2top) < $zeroes){
	$y2top .= "0";
}
$y2scale = $y2top/10;
$table .= '</table>';
?>
<canvas id="myChart" width="800" height="600"></canvas>
<script>
function setColor(area,data,config,i,j,animPct,value)
{
  if(value > 35)return("rgba(220,0,0,"+animPct);
  else return("rgba(0,220,0,"+animPct);
  
}

var charJSPersonnalDefaultOptions = { decimalSeparator : "," , thousandSeparator : ".", roundNumber : "none", graphTitleFontSize: 2 };
var startWithDataset =1;
var startWithData =1;
// Get the context of the canvas element we want to select
var data = {
    labels: [<?=$labels?>],
    datasets: [
        {
            title: "<?=$lang['adimpressions']?>",
			fillColor : "rgba(150,150,150,0.2)",
			strokeColor : "rgba(150,150,150,1)",
			pointColor : "rgba(150,150,150,1)",
			pointStrokeColor : "#fff",
			axis:1,
            data: [<?=$impdata?>]
        },
        {
            title: "<?=$lang['estimatedrevenue']?>",
			fillColor : "rgba(151,187,205,0.2)",
			strokeColor : "rgba(151,187,205,1)",
			pointColor : "rgba(151,187,205,1)",
			pointStrokeColor : "#fff",
			axis:2,
            data: [<?=$revdata?>]
        }
    ]
};

var opt = {
      animationStartWithDataset : startWithDataset,
      animationStartWithData : startWithData,
	  graphTitle : "<?=$lang['estadstats']?>",
	  legend : true,
      animationSteps : 50,
      canvasBorders : true,
      canvasBordersWidth : 3,
      canvasBordersColor : "black",
	  bezierCurve:false,
      annotateDisplay : true,
      graphTitleFontSize: 24,
	  scaleFontFamily:"'Verdana','Arial'",
	  inGraphDataFontFamily:"'Verdana','Arial'",
		graphTitleFontFamily: "'Verdana','Arial'",
		legendFontFamily: "'Verdana','Arial'",
		annotateFontFamily: "'Verdana','Arial'",
		crossTextFontFamily: ["'Verdana','Arial'"],
		yAxisFontFamily: "'Verdana','Arial'",
		xAxisFontFamily: "'Verdana','Arial'",
		yAxisUnitFontFamily: "'Verdana','Arial'",
		yAxisUnitFontSize:10,
		annotateFontSize:10,
      yAxisRight : true,
	  scaleOverride: true,
	  scaleSteps: 10,
	  scaleStartValue:0,
	  scaleStepWidth:<?=$y1scale?>,
	  scaleOverride2: true,
	  scaleSteps2: 10,
	  scaleStartValue2:0,
	  scaleStepWidth2:<?=$y2scale?>,
	  yAxisLabel:"<?=$lang['adimpressions']?>",
	  yAxisUnit:"<?=$lang['impressions']?>",
	  yAxisUnit2:"<?=$lang['dollars']?>",
	  yAxisLabel2: "<?=$lang['estrevenue']?>",
	  canvasBorders:false
}
window.onload = function() {
var myLineChart = new Chart(document.getElementById("myChart").getContext("2d")).Line(data,opt);
}
</script>

<?
echo $table;
include('includes/footer.php'); ?>
