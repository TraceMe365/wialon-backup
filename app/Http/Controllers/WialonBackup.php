<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class WialonBackup extends Controller
{
    protected $token    = "01b9f13200bb9d799a94cf73247a2c4bDBDB5172FB18A7EAD946CA8A1C94CC38D1293CDB";
    protected $sid      = "";
    public function __construct(){
        ini_set ( 'max_execution_time', 3600); // 1 hour
    }

    public function getSid(){
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://hst-api.wialon.com/wialon/ajax.html?svc=token/login&params={"token":"'.$this->token.'"}',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response,1);
            return $response['eid'];
        } catch (Exception $th) {
            $th->getMessage();
        }
    }
    public function getIds(){
        try {
            $sid = $this->getSid();
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params={"spec":{"itemsType":"avl_unit","propName":"sys_name","propValueMask":"*","sortType":"sys_name"},"force":1,"flags":1025,"from":0,"to":0}&sid='.$sid,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $response     = json_decode($response,1);
            $vehicle_list = $response['items'];
            $vehicles     = [];
            foreach($vehicle_list as $vehicle){
                $veh        = [];
                $veh['nm']  = $vehicle['nm'];
                $veh['id']  = $vehicle['id'];
                $vehicles[] = $veh;
            }
            return $vehicles;
        } catch (Exception $th) {
            echo $th->getMessage();
        }
    }

    public function download(){
        $sid      = $this->getSid();
        $vehicles = $this->getIds();
        foreach($vehicles as $veh){
            try {
                $filename = $veh['nm'] . "_data.zip";
                $save_path = __DIR__ . "/downloads/" . $filename;
                if(!file_exists($save_path)){
                    sleep(1);
                    $url = "https://hst-api.wialon.com/wialon/ajax.html?svc=exchange/export_messages&params=" . urlencode(json_encode([
                        "layerName" => "",
                        "format"    => "wln",
                        "itemId"    => $veh['id'],
                        "timeFrom"  => 1730399400,
                        "timeTo"    => 1738898864,
                        "compress"  => 1
                    ])) . "&sid=".$sid;
        
                    $fp = fopen($save_path, 'w'); // Open file for writing

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_FILE, $fp); // Write directly to file
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 300); // Increase timeout for large files
                    
                    $success = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    fclose($fp);

                    if ($http_code == 200 && $success) {
                        file_put_contents($save_path, $success);
                        echo "Success ".$veh['nm'];
                    } else {
                        die("Failed to download the file.");
                    }
                }
            } catch (Exception $th) {
                echo $th->getMessage();
            }
        }
    }
}
