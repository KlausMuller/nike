<pre>  
strt importing gpx files ...

<?php 
	require_once 'lib/init.php'; // init $n stuff (login)

	$dir = "import";
	$runs = $n->activities();

	foreach($runs->activities as $a) {
		$runId = $a->activityId;
		$fileName = sprintf('%s/%s.gpx', $dir, $runId);
		if(!file_exists($fileName)) {                     
			echo "importing run " . $fileName . "\n";
			$run = $n->run($runId);	

			if($run === NULL) {
				echo "cannot import " . $runId . "\n";
				continue;                    
		    }
			file_put_contents($fileName, $n->toGpx($run)); 
		}
	}
?>

eof