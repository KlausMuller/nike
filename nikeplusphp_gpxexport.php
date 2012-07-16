<?php
class NikePlusPHPGpxExport extends NikePlusPHP {
	/**
     * toGpx()
     * outputs a run object to a runtastic importable gpx document
     *
     * @param object $run output from run()
     *
     * @return string gpx string
     */
	public function toGpx($run) {     
		             
		$activity = $run->activity;
		$waypoints = $run->activity->geo->waypoints;
		$startTime = strtotime($run->activity->startTimeUtc);
		$heartRate = null;
		$distance = null;
		
		foreach($run->activity->history as $hi) {
			if($hi->type == 'HEARTRATE') $heartRate = $hi;
			if($hi->type == 'DISTANCE') $distance = $hi;
		}
				
		$b = new XMLWriter();
		$b->openMemory();  
		$b->setIndent(true);    
		$b->setIndentString("    ");
		$b->startDocument("1.0", "UTF-8");
		                   
		$b->startElement('gpx');  
		$b->writeAttribute('version', '0.1');
		$b->writeAttribute('creator', 'nikeplusphp');
		$b->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		$b->writeAttribute('xmlns', 'http://www.topografix.com/GPX/1/1');
		$b->writeAttribute('xsi:schemaLocation', 'http://www.topografix.com/GPX/1/1 http://www.topografix.com/gpx/1/1/gpx.xsd');
		$b->writeAttribute('xmlns:gpxtpx', 'http://www.garmin.com/xmlschemas/TrackPointExtension/v1'); 
		$b->writeAttribute('xmlns:gpxx', 'http://www.garmin.com/xmlschemas/GpxExtensions/v3');    
		
		// metadata
		$b->startElement('metadata');    
		$b->writeElement('name', 'metadataName ' . time());     
		// get min/max lat/lng                               
		$minLon = 10000;
		$maxLon = -10000;
		$minLat = 10000;
		$maxLat = -10000;
		foreach($waypoints as $wp) {
			if($wp->lon > $maxLon) $maxLon = $wp->lon;
			if($wp->lon < $minLon) $minLon = $wp->lon;
			if($wp->lat > $maxLat) $maxLat = $wp->lat;
			if($wp->lat < $minLat) $minLat = $wp->lat;
		}
		$b->startElement('bounds');
		$b->writeAttribute('maxlon', $maxLon);
		$b->writeAttribute('minlon', $minLon);
		$b->writeAttribute('maxlat', $maxLat);
		$b->writeAttribute('minlat', $minLat);
		$b->endElement(); // EO bounds        
		
		$b->endElement(); // EO metadata
		   
		// track
		$b->startElement('trk');    
		$b->writeElement('name', 'trkName ' . time());
		$b->writeElement('type', 'Run');         
		                   
		$b->startElement('trkseg');

		foreach($waypoints as $index => $wp) {
			$b->startElement('trkpt');
			$b->writeAttribute('lat', $wp->lat);
			$b->writeAttribute('lon', $wp->lon);
			$b->writeElement('ele', $wp->ele); 
			$b->writeElement('time', date('Y-m-d\TH:i:s', $startTime+$index));
            
			$b->startElement('extensions');        
			$b->startElement('gpxtpx:TrackPointExtension');     

			if($heartRate !== null) {
				$hrKey = (int) floor($index/$heartRate->intervalMetric);
				if(array_key_exists($hrKey, $heartRate->values)) {
					$b->writeElement('gpxtpx:hr', $heartRate->values[$hrKey]);
				}
			}
			$b->endElement(); // EO gpxtpx:TrackPointExtension
			$b->endElement(); // EO extensions    
			
			$b->endElement(); // EO trkpt
		}
		
		$b->endElement(); // EO trkseg
		$b->endElement(); // EO trk
		
		
		$b->endElement(); // EO gpx       
		                                 
		
		$b->endDocument();
		return $b->outputMemory();

	}
}