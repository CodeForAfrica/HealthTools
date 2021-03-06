<?php
namespace App\Http\Controllers;

class NHIFController extends Controller {

	public static function coverage($county_id, $gps, $county_name, $isSMS)
	{
        $found = true;

        $result = "";

        $key = config('custom_config.google_api_key');
        $table = config('custom_config.nhif_table');

        $url = "https://www.googleapis.com/fusiontables/v1/query?";

        if (is_int($county_id)){
            $sql = "SELECT * FROM ".$table." WHERE 'COUNTY ID'='".$county_id."'";
        } else if (!empty($county_name)){
            $sql = "SELECT * FROM ".$table." WHERE 'COUNTY NAME'='".strtoupper($county_name)."'";
        } else {
            $sql = "SELECT * FROM ".$table." LIMIT 10";
        }

        $options = array("sql"=>$sql, "key"=>$key, "sensor"=>"false");

        $url .= http_build_query($options,'','&');

        $page = file_get_contents($url);

        $data = json_decode($page, TRUE);


        if (!array_key_exists("rows", $data)) {
            $result = "No hospitals found. Please check that you've selected the right county.";
            $found = false;
        } else {
            $rows = $data['rows'];

            $i = 0;
            $result_array = array();

            foreach ($rows as $row) {
                $cname = ucwords(strtolower($row[3]));
                $county_name = ucwords(strtolower($row[5]));
                //$cname .= " KSH ".$row['8'];
                if (!$isSMS) {
                    $result_array[] = "<p>".$row[1].". <a target='_blank' href='https://www.google.com/maps/?q=".$cname.", ".$county_name.", Kenya'>".$cname."</a></p>";
                } else {
                    $i++;
                    if ($i < 6) {
                        $result_array[] = $i .". ". $cname . "\n";
                    }
                    if ($i == 6) {
                        $result_array[$i] = "\n".'Find the full list at http://health.the-star.co.ke';
                    }
                }
            }
            $glue = "";

            $result = implode($glue, $result_array);
            if ($gps == "0,0") {
                $result = 'Sorry, location could not be understood. Check for spelling mistakes.';
            }
        }


        if($isSMS && !$found){
            return false;
        }

        return $result;

	}
    public static function reverseGeocode($q){

        $q = urlencode($q);

        $geocode_url = "https://maps.googleapis.com/maps/api/geocode/json?address=".$q."&key=".Config::get('custom_config.google_api_key');

        $response = json_decode(file_get_contents($geocode_url));

        if($response->status =="OK"){
            return $response->results[0]->geometry->location;
        }else{
            return "0,0";
        }

    }
}