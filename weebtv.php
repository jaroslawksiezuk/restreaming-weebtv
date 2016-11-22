<?php

$login = "";
$password = "";
$channel_number = ""; // channel number from list (cid) or empty
$rtmp_out = "example.flv";       // file, rtmp URL or empty to watch in vlc
$quality = "";        // quality HI or empty

function getCurl($url, $postdata) {

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  'User-Agent: XBMC',
  'ContentType: application/x-www-form-urlencoded'
  ));

  $results = curl_exec($ch);
  curl_close($ch);

  return $results;

}

while (1 == 1) {

$url = "http://weeb.tv/api/getChannelList";

$data = http_build_query(
    array(
        'username' => $login,
        'userpassword' => $password
    )
);

$channels = getCurl($url, $data);

echo "===CHANNELS=== \n";

$channels_list = [];

foreach(json_decode($channels) as $channel)
{
   echo $channel->cid . " - " . $channel->channel_title . "\n";
   $channels_list[$channel->cid] = $channel->channel_title;
}

if(!$channel_number) {
  echo "Select channel cid: ";
  $channel_number = trim(fgets(STDIN));
}

if(!isset($channels_list[$channel_number])) {
  echo "Channel not found  \n";
}

$url = "http://weeb.tv/api/setPlayer";

$data = http_build_query(
    array(
        'platform' => 'XBMC',
        'channel' => $channel_number,
        'username' => $login,
        'userpassword' => $password
    )
);

$result = getCurl($url, $data);

parse_str(urldecode($result), $params);

$r = $params[10] . '/' . $params[11];

if($quality) {
  $r = $r . $quality;
}

$p = 'token';
$s = $params[73];

echo "===START=== \n";

if($rtmp_out) {
  // restream by ffmpeg
  $cmd = 'rtmpdump -q -v -r "' . $r . '" -s "' . $s . '" -p "' . $p . '" | ffmpeg -i - -acodec copy -vcodec copy -f flv '. $rtmp_out;
} else {
  // watch in VLC
  $cmd = 'rtmpdump -q -v -r "'.$r.'" -s "'.$s.'" -p "'.$p.'" | vlc -';
}

// start SHOW!
shell_exec($cmd);

// sleep from 5 to 30 seconds
sleep(rand(5, 30));

}
?>
