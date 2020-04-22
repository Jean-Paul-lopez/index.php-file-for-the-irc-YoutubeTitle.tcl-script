<?php
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////
           //    A quick and dirty fix for the parameters needed for the YoutubeTitle.tcl script for the Eggdrop bot  //
          //              (https://github.com/DanielVoipan/black-tcl/blob/master/YoutubeTitle.tcl) based on          //
         //                the Google Youtube API (https://developers.google.com/youtube/v3/).                      //
        //                               A YT API key is needed to run this script!                                //
       //                                by JP Lopez (https://jptech.solutions/en/)                               //
      //                                                INSTRUCTIONS                                             //
     //                          rename the file to index.php, fill in your API key in $DEVELOPER_KEY           //
    //      Download (https://github.com/googleapis/google-api-php-client) and plae in directory "apiYT        //
   //       Format: http(s)//<your.url>/index.php?link=https://www.youtube.com/watch?v=jsV_YXq-1x4            //
  //                          replace url in the file YoutubeTitle.tcl on the line                           //    
 // set ipq [http::geturl "http://youtubesongname.000webhostapp.com/index.php?link=$link" -timeout 50000] : //
/////////////////////////////////////////////////////////////////////////////////////////////////////////////

error_reporting(0);
if ($_GET['link']) {
  // Call set_include_path() as needed to point to your client library.
  require_once ($_SERVER["DOCUMENT_ROOT"].'/apiYT/google-api-php-client/src/Google_Client.php');
  require_once ($_SERVER["DOCUMENT_ROOT"].'/apiYT/google-api-php-client/src/contrib/Google_YouTubeService.php');

  /* Set $DEVELOPER_KEY to the "API key" value from the "Access" tab of the
  Google APIs Console <http://code.google.com/apis/console#access>
  Please ensure that you have enabled the YouTube Data API for your project. 
  Fill in your YT API Key here          
  */
  $DEVELOPER_KEY = '<your API KEY here>'; 

  $client = new Google_Client();
  $client->setDeveloperKey($DEVELOPER_KEY);

  $link = $_GET['link']; 

  if (strpos($link, 'https://www.youtube.com/watch?v=') !== false) {
    $vidID = str_replace('https://www.youtube.com/watch?v=', "", $link);
  } 

  if (strpos($link, 'https://youtu.be/') !== false) { 
    $vidID = str_replace('https://youtu.be/', "", $link);
  }

// remove dash ("-") before ID, else it won't work.
  if ($vidID[0] == "-") {
    $teller = 1;
    $videoID = "";
    while ($vidID[$teller] != NULL) {
      $videoID.=$vidID[$teller];
      $teller++;
    }
    $vidID = $videoID;
  }
	
  $youtube = new Google_YoutubeService($client);

  try {
    $searchResponse = $youtube->search->listSearch('id,snippet', array(
      'q' => $vidID,
      'maxResults' => 1,
    ));
    $videos = '';
    $channels = '';

    foreach ($searchResponse['items'] as $searchResult) {
      switch ($searchResult['id']['kind']) {
        case 'youtube#video':
	  $vidID = $searchResult['id']['videoId'];
	  $vidTITLE = $searchResult['snippet']['title'];
	  $date = $searchResult['snippet']['publishedAt'];
	  $channelTitle = $searchResult['snippet']['channelTitle']; 
          // $videos .= sprintf('<li>%s (%s)</li>', $searchResult['snippet']['title'], 
          // $searchResult['id']['videoId']."<a href=http://www.youtube.com/watch?v=".$searchResult['id']['videoId']." target=_blank>   Watch This Video</a>");
          break;
        case 'youtube#channel':
          $channels .= sprintf('<li>%s (%s)</li>', $searchResult['snippet']['title'],
            $searchResult['id']['channelId']);
          break;
       }
    }

   } catch (Google_ServiceException $e) {
    $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
      htmlspecialchars($e->getMessage()));
  } catch (Google_Exception $e) {
    $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
      htmlspecialchars($e->getMessage()));
  }
}

$url="https://www.googleapis.com/youtube/v3/videos?id=$vidID&part=contentDetails&key=$DEVELOPER_KEY";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
// This is what solved the issue (Accepting gzip encoding)
curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
$response = curl_exec($ch);
curl_close($ch);
$response2 = json_decode($response);
$duration2 = $response2->items[0]->contentDetails->duration;

$url="https://www.googleapis.com/youtube/v3/videos?id=$vidID&part=statistics&key=$DEVELOPER_KEY";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
// This is what solved the issue (Accepting gzip encoding)
curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
$response = curl_exec($ch);
curl_close($ch);
$response2 = json_decode($response);
$views = $response2->items[0]->statistics->viewCount;
$Likes = $response2->items[0]->statistics->likeCount;
$DisLikes = $response2->items[0]->statistics->dislikeCount;                                                                                                                            
$Comments = $response2->items[0]->statistics->commentCount;
$datum = $date[0].$date[1].$date[2].$date[3]."-".$date[5].$date[6]."-".$date[8].$date[9];

$durationX = $duration2;

// Provide the right playtime format, only minutes and seconds
if (strpos($durationX, "H") !== false) {
   $stukkie = explode("H", $durationX); 
   $stukkie2 = explode("PT", $stukkie[0]);
   $hours = $stukkie2[1];
   $extramins = ($hours * 60);
   $stukkie3 = explode("M", $stukkie[1]);
   $mins = $stukkie3[0];            
   $playtime = "PT".($mins+$extramins)."M".$stukkie3[1];
} else {
   $playtime = $durationX;
}

// convert Likes/DisLikes into percentage
$percent = (($Likes+$DisLikes)/100);
$like_percentage = Round(($Likes/$percent),2);
$dislike_percentage = Round(($DisLikes/$percent),2);

// output for YoutubeTitle.tcl
echo "$vidTITLE\n";
echo "$views views\n";
echo "$like_percentage\n";
echo "$dislike_percentage\n";
echo "$channelTitle\n";
echo "$datum\n";
echo "$playtime"; 
?>

