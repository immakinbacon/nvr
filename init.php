<?php
error_reporting(0);
$config=json_decode(file_get_contents('/nvr/scripts/config.json'),true);
foreach($config as $camera_id => $camera)
{
	$ffmpeg_string="ffmpeg -flags global_header -rtsp_transport tcp -r 3 -i ".$camera["source"]." \\\n-c:v copy -an -f segment -strftime 1 -reset_timestamps 1 -segment_time 300 ".$camera["path"].$camera_id."/".$camera["video_filename"]." \\\n-f image2 -s 1280x720 -framerate 2 -y -update 1 ".$camera["path"].$camera_id."/".$camera["still_filename"];
	if (!file_exists("/proc/".$camera["pid"]) || $camera["pid"]=="") {
		if (!file_exists($camera["path"].$camera_id)) { mkdir($camera["path"].$camera_id,0777,true); }
		unlink("/nvr/logs/".$camera_id."_error.log");
		$descriptorspec = array(0 => array("pipe", "r"),1 => array("pipe", "w"),2 => array("file", "/nvr/logs/".$camera_id."_error.log", "a"));
		$proc=proc_open($ffmpeg_string.' &',$descriptorspec,$pipes);
		file_put_contents("/nvr/logs/init.log",$camera_id." was started. - ".date("Y-m-d H:i:s")."\n",FILE_APPEND);
	}elseif ($camera["still_hash"]==hash_file("md5",$camera["path"].$camera_id."/".$camera["still_filename"])) {
		file_put_contents("/nvr/logs/init.log",$camera_id." was killed due to file hash condition. - ".date("Y-m-d H:i:s")."\n",FILE_APPEND);
		exec("kill -9 ".$camera["pid"]);
	}elseif (exec('cat /nvr/logs/'.$camera_id.'_error.log |grep "MV errors" | wc -l')>0) {
		file_put_contents("/nvr/logs/init.log",$camera_id." was killed due to errors in log file. - ".date("Y-m-d H:i:s")."\n",FILE_APPEND);
		exec("kill -9 ".$camera["pid"]);
	}
}
include '/nvr/scripts/get_stream_info.php';
?>
