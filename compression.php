/*
  @author Paul Calvano
  @license MIT
  @copyright 2019 Akamai Technologies, Inc.
*/

<html>
  <head>
    <meta http-equiv="Content-Language" content="en-us">
    <title>Gzip and Brotli Compression Level Estimator</title>
    <meta charset="utf-8">

    <script ype="text/javascript" src="//code.jquery.com/jquery-2.1.3.js"></script>
    <script type="text/javascript" src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/plug-ins/1.10.7/integration/bootstrap/3/dataTables.bootstrap.css">
    <script type="text/javascript" src="//cdn.datatables.net/plug-ins/1.10.7/integration/bootstrap/3/dataTables.bootstrap.js"></script>
 		<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <style>
				.testurl {
            border: 2px solid #CCCCCC;
            border-radius: 8px 8px 8px 8px;
            font-size: 24px;
            height: 45px;
            line-height: 30px;
            outline: medium none;
            padding: 8px 12px;
            width: 600px;
        }

        table {
    			border-collapse: collapse;
    			text-align: center;
				}
    </style>

		<script type="text/javascript">
			google.charts.load('current', {'packages':['corechart']});
			google.charts.load('current', {'packages':['bar']});

    	function getCompressionResults(url){
		    	var compressiontest_url = "/compressiontest.php?q=" + url;
			    return $.ajax({
			        type: "GET",
			        url: compressiontest_url,
			        cache: true,
			        async: false
			    }).responseText;
			}

			$(document).ready(function(){
	    	$('#compression-estimator-button').click(function(){
					var url = document.getElementById("testurl").value;
					if (!testurl == '') {
						var json = JSON.parse(getCompressionResults(url));

						// Update Summary table
						$( ".gzip-encoded" ).html( json.gzipped_encoded );
						$( ".gzip-size" ).html( json.gzipped_size );
						$( ".gzip-quality-guess" ).html( json.gzip_quality_guess );
						$( ".bro-encoded" ).html( json.brotli_encoded );
						$( ".bro-size" ).html( json.br_size );
						$( ".bro-quality-guess" ).html( json.br_quality_guess );
						$( ".original-size" ).html( json.original_size );

						// Update Gzip table
						for (i=1;i<=9;i++) {
							$( ".gzip-size-" + i ).html( json['gzip_size_' + i] );
							$( ".gzip-ratio-" + i ).html(  Math.round( (json.original_size / json['gzip_size_' + i])*100)/100 );
						}

						// Update Brotli table
						for (i=1;i<=11;i++) {
							$( ".bro-size-" + i ).html( json['bro_size_' + i] );
							$( ".bro-ratio-" + i ).html(  Math.round( (json.original_size / json['bro_size_' + i])*100)/100 );
							$( ".bro-vs-gzip-" + i ).html(Math.round((1- (json['bro_size_' + i] / json.gzipped_size))*100) + "%");
						}

						// Convert the JSON to an Array for charting
						var compression_graph_array = [['Compression Level', 'Gzip','Brotli']];
						//var a= ['N/A', json.original_size, json.original_size];
						//compression_graph_array.push(a);
						for (i=1;i<=11;i++) {
							var a= ['' + i, json['gzip_size_' + i], json['bro_size_' + i]];
						  compression_graph_array.push(a);
						}

		        // Create bar chart
		        var data = google.visualization.arrayToDataTable(compression_graph_array);
						var options = {
							title: 'Bytes Per Compression Level',
						  bars: 'horizontal',
						  theme: 'material',
						  legend: {position : 'bottom'},
						  vAxis: {
						  		title: 'Compression Level',
						  		gridlines: {count: 12}
						  		},
						  hAxis: {
						  		minValue: 0
						  		},
						  chartArea:{'height': '90%'}
						};

						var chart = new google.visualization.BarChart(document.getElementById('compression_graph_div'));
		        chart.draw(data, options);
					}
	      });
		  });

    </script>
	</head>
  <body>



    <div id="compression-estimator" class="compression-estimator">


    	<p>&nbsp;<p>

     	<table with=1000 valign="center">
     	  <tr>
	     		<td width=100>&nbsp</td>
	       	<td width=800 ><h1>Gzip and Brotli Compression Level Estimator!</h1></td>
	       	<td width=100>&nbsp</td>
        </tr>

      	<tr>
	     		<td width=100>&nbsp</td>
	       	<td width=800 >Enter a URL and click the submit button. I'll check to see if you are using Gzip and Brotli, and guesstimate what compression levels you are using based on the Content-Length.
	       	<td width=100>&nbsp</td>
        </tr>

     		<tr>
	     		<td width=100>&nbsp</td>
	       	<td width=800 ><input type="text" name="testurl" size="30" class="testurl" id="testurl" placeholder="Enter a URL"></td>
	       	<td width=100>&nbsp</td>
        </tr>

		  	<tr>
					<td width=100>&nbsp</td>
					<td align="center"><input type="button" id="compression-estimator-button" value="Compression Test"></td>
					<td width=100>&nbsp</td>
			  </tr>
  		</table>


	   <p>&nbsp<p>

    	 <table width=1000>
	       <tr>
	       	<td width=100>&nbsp</td>
	        <td width=800 align="center">

	        	<table id="summaryTable" class="table table-striped table-bordered" border="1">
	        			<tr>
	        					<th>Compression</th>
	        					<th>Encoded?</th>
	        					<th>Download Size (bytes)</th>
	        					<th>Compression Level Guess</th>
	        			</tr>
	        			<tr>
	        					<td>Gzip</td>
	        					<td><div class="gzip-encoded"></div></td>
	        					<td><div class="gzip-size"></div></td>
	        					<td><div class="gzip-quality-guess"></div></td>
	        			</tr>
	        			<tr>
	        					<td>Brotli</td>
	        					<td><div class="bro-encoded"></div></td>
	        					<td><div class="bro-size"></div></td>
	        					<td><div class="bro-quality-guess"></div></td>
	        			</tr>
	        			<tr>
	        					<td>Uncompressed</td>
	        					<td>N/A</td>
	        					<td><div class="original-size"></div></td>
	        					<td>N/A</td>
	        			</tr>
  					</table>

	        </td>
	        <td width=100>&nbsp</td>
				</tr>
	    </table>

	    <p>&nbsp;</p>
	    <table width=1400>
	       <tr>
	        <td align="center">

	        	<table width=1400>
	        		<tr>
	        			<td width=25>&nbsp</td>
	        			<td align=center width=350>

	        			<table id="GzipTable" class="table table-striped table-bordered"  border="1">
	        			<tr>
	        					<td colspan=3 align="center"><b>Gzip Compression</b></td>
	        			</tr>
	        			<tr>
	        					<th>Level</th>
	        					<th>Size (Bytes)</th>
	        					<th>Ratio<br>(vs Uncompressed)</th>
	        			</tr>
	        			<tr>
	        					<td>1</td>
	        					<td><div class="gzip-size-1"></div></td>
	        					<td><div class="gzip-ratio-1"></div></td>
	        			</tr>
	        			<tr>
	        					<td>2</td>
	        					<td><div class="gzip-size-2"></div></td>
	        					<td><div class="gzip-ratio-2"></div></td>
	        			</tr>
	        			<tr>
	        					<td>3</td>
	        					<td><div class="gzip-size-3"></div></td>
	        					<td><div class="gzip-ratio-4"></div></td>
	        			</tr>
	        			<tr>
	        					<td>4</td>
	        					<td><div class="gzip-size-4"></div></td>
	        					<td><div class="gzip-ratio-4"></div></td>
	        			</tr>
	        			<tr>
	        					<td>5</td>
	        					<td><div class="gzip-size-5"></div></td>
	        					<td><div class="gzip-ratio-5"></div></td>
	        			</tr>
	        			<tr>
	        					<td>6</td>
	        					<td><div class="gzip-size-6"></div></td>
	        					<td><div class="gzip-ratio-6"></div></td>
	        			</tr>
	        			<tr>
	        					<td>7</td>
	        					<td><div class="gzip-size-7"></div></td>
	        					<td><div class="gzip-ratio-7"></div></td>
	        			</tr>
	        			<tr>
	        					<td>8</td>
	        					<td><div class="gzip-size-8"></div></td>
	        					<td><div class="gzip-ratio-8"></div></td>
	        			</tr>
	        			<tr>
	        					<td>9</td>
	        					<td><div class="gzip-size-9"></div></td>
	        					<td><div class="gzip-ratio-9"></div></td>
	        			</tr>
  					</table>
  				</td>
  				<td width=100>&nbsp;&nbsp;</td>
  				<td width=525>

	        	<table id="BrotliTable"  class="table table-striped table-bordered" border="1">
	        			<tr>
	        					<td colspan=4 align="center"><b>Brotli Compression</b></td>
	        			</tr>
	        			<tr>
	        					<th>Level</th>
	        					<th>Size (Bytes)</th>
	        					<th>Ratio<br>(vs Uncompressed)</th>
	        					<th>% Improvement<br>over Gzip</th>
	        			</tr>
	        			<tr>
	        					<td>1</td>
	        					<td><div class="bro-size-1"></div></td>
	        					<td><div class="bro-ratio-1"></div></td>
	        					<td><div class="bro-vs-gzip-1"></div></td>
	        			</tr>
	        			<tr>
	        					<td>2</td>
	        					<td><div class="bro-size-2"></div></td>
	        					<td><div class="bro-ratio-2"></div></td>
	        					<td><div class="bro-vs-gzip-2"></div></td>
	        			</tr>
	        			<tr>
	        					<td>3</td>
	        					<td><div class="bro-size-3"></div></td>
	        					<td><div class="bro-ratio-4"></div></td>
	        					<td><div class="bro-vs-gzip-4"></div></td>
	        			</tr>
	        			<tr>
	        					<td>4</td>
	        					<td><div class="bro-size-4"></div></td>
	        					<td><div class="bro-ratio-4"></div></td>
	        					<td><div class="bro-vs-gzip-4"></div></td>
	        			</tr>
	        			<tr>
	        					<td>5</td>
	        					<td><div class="bro-size-5"></div></td>
	        					<td><div class="bro-ratio-5"></div></td>
	        					<td><div class="bro-vs-gzip-5"></div></td>
	        			</tr>
	        			<tr>
	        					<td>6</td>
	        					<td><div class="bro-size-6"></div></td>
	        					<td><div class="bro-ratio-6"></div></td>
	        					<td><div class="bro-vs-gzip-6"></div></td>
	        			</tr>
	        			<tr>
	        					<td>7</td>
	        					<td><div class="bro-size-7"></div></td>
	        					<td><div class="bro-ratio-7"></div></td>
	        					<td><div class="bro-vs-gzip-7"></div></td>
	        			</tr>
	        			<tr>
	        					<td>8</td>
	        					<td><div class="bro-size-8"></div></td>
	        					<td><div class="bro-ratio-8"></div></td>
	        					<td><div class="bro-vs-gzip-8"></div></td>
	        			</tr>
	        			<tr>
	        					<td>9</td>
	        					<td><div class="bro-size-9"></div></td>
	        					<td><div class="bro-ratio-9"></div></td>
	        					<td><div class="bro-vs-gzip-9"></div></td>
	        			</tr>
	        			<tr>
	        					<td>10</td>
	        					<td><div class="bro-size-10"></div></td>
	        					<td><div class="bro-ratio-10"></div></td>
	        					<td><div class="bro-vs-gzip-10"></div></td>
	        			</tr>
	        			<tr>
	        					<td>11</td>
	        					<td><div class="bro-size-11"></div></td>
	        					<td><div class="bro-ratio-11"></div></td>
	        					<td><div class="bro-vs-gzip-11"></div></td>
	        			</tr>
  					</table>
  				</td>
  				<td width="400">
  					<div id="compression_graph_div"  style="width: 400px; height: 600px;"></div>
  				</td>
  			</tr></table>
	        </td>
				</tr>

	    </table>


    </div>


  </body>
</html>
