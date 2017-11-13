<?php 

set_time_limit(360);

$start          = '2017-00-00';
$end            = '1971-00-00';
$getRangeYear   = range(gmdate('Y', strtotime($start)), gmdate('Y', strtotime($end)));

$conn = new PDO('mysql:host=localhost;dbname=war_terror_data','root','root');

foreach ($getRangeYear as $value => $key) {
    $geoJson = ['type' => 'FeatureCollection', 'features' => []];
    $sql = "SELECT * FROM terror_attacks WHERE iyear = {$key}";

    $conn->query($sql);
    $getData = $conn->prepare($sql); 
    $getData->execute();

    while ($row = $getData->fetch(PDO::FETCH_ASSOC)) {
        $feature = array(
            'id' => $row['eventid'],
            'type' => 'Feature', 
            'geometry' => array(
                'type' => 'Point',
                # Pass Longitude and Latitude Columns here
                'coordinates' => array($row['longitude'], $row['latitude'])
            ),
            # Pass other attribute columns here
            'properties' => array(
                'group' => $row['gname'], 
                'motive' => $row['motive']
                )
            );
        # Add feature arrays to feature collection array
        array_push($geoJson['features'], $feature);
    }
    
    $path = __DIR__ . "/geoJson/{$key}-terror-attacks.json";
    
    if (! file_put_contents($path, json_encode($geoJson, JSON_NUMERIC_CHECK))) { // Try to write the file.
        throw new RuntimeException();
    }
} 
