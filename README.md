# index.php-file-for-the-irc-YoutubeTitle.tcl-script
index.php file for the data required by the IRC YoutubeTitle.tcl script
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////
           //              A quick and dirty fix for the parameters needed for the YoutubeTitle.tcl script            //
          //              (https://github.com/DanielVoipan/black-tcl/blob/master/YoutubeTitle.tcl) based on          //
         //                the Google Youtube API (https://developers.google.com/youtube/v3/).                      //
        //                               A YT API key is needed to run this script!                                //
       //                                by JP Lopez (https://jptech.solutions/en/)                               //
      //                                                INSTRUCTIONS                                             //
     //                          rename the file to index.php, fill in your API key in $DEVELOPER_KEY           //
    //      Download (https://github.com/googleapis/google-api-php-client) and place in directory apiYT        //
   //       "Format: http(s)://<your.url>/index.php?link=https://www.youtube.com/watch?v=jsV_YXq-1x4          //
  //                               replace url in the file YoutubeTitle.tcl                                  //    
 // set ipq [http::geturl "http://youtubesongname.000webhostapp.com/index.php?link=$link" -timeout 50000] : //
///////////////////////////////////////////////////////////////////////////////////////////////////////////// 
