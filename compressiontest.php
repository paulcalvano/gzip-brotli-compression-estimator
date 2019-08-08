<?php
	
	# Paul Calvano, Akamai Technologies
	# Gzip and Brotli Compression Estimator
	# Takes 1 parameter (q=) and outputs a JSON array containing gzip and brotli compression stats

	header("Cache-Control: no-store,no-cache");
	$ua = 'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36';
	$q = $argv[1];
	if (!$q) {
	  $q =  $_GET['q'];
	}

	# Unique filename for locally stored file
	$unique_id = uniqid();
	$path = '/tmp/brotli/';
	$original =$path . $unique_id . '.txt';
	$gzipped =$path . $unique_id . '_gz.txt';
	$brotli = $path . $unique_id . '_br.txt';

	# Get Original File, Uncompressed
	$command = 'curl  -H "' . $ua . '" -Lsko ' . $original . ' ' . $q;
	exec($command, $o);
	$original_size = filesize($original);
	
	
	# Get Original File, Gzip Compressed
	$command = 'curl  -H "' . $ua . '" -Lvko ' . $gzipped . ' -H "Accept-Encoding: gzip" ' . $q . ' 2>&1 | grep -E "< Content-Encoding"';
	exec($command, $o);
	$gzipped_size = filesize($gzipped);
	$gzipped_encoded=0;
	if (strpos($o[0], 'Content-Encoding: gzip')) {
		$gzipped_encoded=1;
	} 
	unlink($gzipped);

	# Get Original File, Brotli Compressed
	$command = 'curl  -H "' . $ua . '" -Lvko ' . $brotli . ' -H "Accept-Encoding: br" ' . $q . ' 2>&1 | grep -E "< Content-Encoding"';
	exec($command, $b);
	$brotli_size = filesize($brotli);	

	$brotli_encoded=0;
	if (strpos($b[0], 'Content-Encoding: br')) {
	  	$brotli_encoded=1;
	} 
	unlink($brotli);
	
	
		
  # Compress original.txt with Gzip using quality 1 - 9.
  $gzip_sizes = array();
  $gzip_quality_guess = 0;
  $gzip_quality_guess_size = 0;
  $guessed=0;
	
  for ($i=1;$i<=9;$i++) {
		$file =$path . $unique_id . '_gzip' . $i;
    $gzip_command = "gzip -c -" . $i . " " . $original . " > " . $file;        
  	exec($gzip_command, $o); 		
		$gz_size = filesize($file);
		unlink($file);
		
		$gzip_sizes[$i] = $gz_size;
		
		  if (($gzipped_size > $gz_size || $i==9) && $guessed==0 && $gzipped_encoded) {
    	  $gzip_quality_guess = $i;
      	$gzip_quality_guess_size = $gz_size;
        $guessed=1;
      }
  }	
	if (!$gzipped_encoded) {
		$gzip_quality_guess = "null";
	  $gzip_quality_guess_size = "null";
	  $gz_size = "null";
	}



  # Compress original.txt with Brotli using quality 1 - 11.
  $bro_sizes = array();
  $bro_quality_guess = 0;
  $bro_quality_guess_size = 0;
  $guessed=0;

  for ($i=1;$i<=11;$i++) {
  	$file =$path . $unique_id . '_br' . $i;
  	$bro_command = "/home/ubuntu/brotli/brotli --quality=" . $i . " --output=" . $file . " " . $original;                     
		exec($bro_command, $o); 		
		$br_size = filesize($file);
		unlink($file);
		
		$bro_sizes[$i] = $br_size;
		
		  if (($brotli_size > $br_size || $i==11) && $guessed==0) {
    	  $br_quality_guess = $i;
      	$br_quality_guess_size = $gz_size;
        $guessed=1;
      }
}

if (!$brotli_encoded) {
	$br_quality_guess = "null";
  $br_quality_guess_size = "null";
  $brotli_size = "null";
}

unlink($original);
	
# Output JSON 
print <<<END
{"url": "$q", "original_size": $original_size,"gzipped_encoded": $gzipped_encoded,"gzipped_size": $gzipped_size,"gzip_quality_guess": $gzip_quality_guess,"gzip_size_1": $gzip_sizes[1],"gzip_size_2": $gzip_sizes[2],"gzip_size_3": $gzip_sizes[3],"gzip_size_4": $gzip_sizes[4],"gzip_size_5": $gzip_sizes[5],"gzip_size_6": $gzip_sizes[6],"gzip_size_7": $gzip_sizes[7],"gzip_size_8": $gzip_sizes[8],"gzip_size_9": $gzip_sizes[9],"brotli_encoded": $brotli_encoded,"br_size": $brotli_size, "br_quality_guess": $br_quality_guess,"bro_size_1": $bro_sizes[1],"bro_size_2": $bro_sizes[2],"bro_size_3": $bro_sizes[3],"bro_size_4": $bro_sizes[4],"bro_size_5": $bro_sizes[5],"bro_size_6": $bro_sizes[6],"bro_size_7": $bro_sizes[7],"bro_size_8": $bro_sizes[8],"bro_size_9": $bro_sizes[9],"bro_size_10": $bro_sizes[10],"bro_size_11": $bro_sizes[11]}
END;
	

?>

