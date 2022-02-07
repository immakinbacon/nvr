<?php
$config=json_decode(file_get_contents('/nvr/scripts/config.json'),true);
foreach($config as $camera_id => $camera)
{
	$ffmpeg_string="ffmpeg -flags global_header -rtsp_transport tcp -r 3 -i ".$camera["source"]." -c:v copy -an -f segment -strftime 1 -reset_timestamps 1 -segment_time 300 ".$camera["path"].$camera_id."/".$camera["video_filename"]." -f image2 -s 1280x720 -framerate 2 -y -update 1 ".$camera["path"].$camera_id."/".$camera["still_filename"];
	$pid=exec('ps -ef | grep -v grep | grep "'.$ffmpeg_string.'" | awk \'{print $2}\'');
	$config[$camera_id]["pid"]=$pid;
	$config[$camera_id]["still_hash"]=hash_file("md5",$camera["path"].$camera_id."/".$camera["still_filename"]);
}
file_put_contents('/nvr/scripts/config.json',json_encode($config));
?>
