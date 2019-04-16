
<!DOCTYPE html>

<?php
date_default_timezone_set('Asia/Kolkata');
$file = $_GET['file'];
$disable_track = isset($_GET['disable_track']) ? $_GET['disable_track'] : 1;
$employee_id = $_GET['employee_id'];
$media_id  = $_GET['media_id'];
$employee_name  = $_GET['employee_name'];
$title  = $_GET['title'];
$media_type  = $_GET['media_type'];
?>

<html>
<head>
<meta charset="utf-8" />
<!-- Website Design By: www.happyworm.com -->
<title>Demo : jPlayer as an audio player</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="../../dist/skin/blue.monday/css/jplayer.blue.monday.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../../lib/jquery.min.js"></script>
<script type="text/javascript" src="../../dist/jplayer/jquery.jplayer.min.js"></script>
<script type="text/javascript">
//<![CDATA[
var file_path = "<?= $file; ?>";
var employee_id = '<?= $employee_id; ?>';
var employee_name = '<?= $employee_name; ?>';
var title = "<?= $title; ?>";
var media_type = '<?= $media_type; ?>';
var disable_track = '<?= $disable_track; ?>';
//var employee_id = '10027097';
var inserted_media_id = '<?= $media_id; ?>';
//var inserted_media_id = 'YkhPtmJhjJ';
var insert_id = "";
var media_id = "";
var start_time = "";
var total_play_duration = "";

$(document).ready(function(){

	$("#jquery_jplayer_1").jPlayer({
		ready: function (event) {
			$(this).jPlayer("setMedia", {
				title: "Recording",
				mp3: file_path
				
			});
		},
		swfPath: "../../dist/jplayer",
		supplied: "mp3",
		wmode: "window",
		useStateClassSkin: true,
		autoBlur: false,
		smoothPlayBar: true,
		keyEnabled: true,
		remainingDuration: true,
		toggleDuration: true,
                preload : 'auto',
                play: function () {
                    if(insert_id == "" && disable_track != 1) {
                        logTrackingInsert();
                    }
                    
                },
                pause: function () {
                    
                    if(insert_id != "" && disable_track != 1) {
                        logTrackingUpdate(1);
                    }
                    
                },
                ended: function() {
                    if(insert_id != "" && disable_track != 1) {
                        logTrackingUpdate(2);
                    }
                    
                }
	});
});

function logTrackingInsert() {
	//var isPaused = $('#jquery_jplayer_1').data().jPlayer.status.paused;

        var total_media_duration = $("#jquery_jplayer_1").data("jPlayer").status.duration;

        //if(isPaused == true) {
            
            start_time = new Date().toLocaleString();

            $.ajax({
                    type : 'GET',		
                    //url : "http://192.168.22.103:810/SSO/uimage/knowledge/logClickCount?employee_id=10027097&start_time=2017-04-25 17:54:12",
                    url : "services.php?action=insert",
                    data : {
                            "employee_id"       : employee_id,
                            "employee_name"     : employee_name,
                            "start_time"        : start_time,
                            "total_duration"    : total_media_duration,
                            "media_path"        : file_path,
                            "media_id"          : inserted_media_id,
                            "media_type"		: media_type,
                            "title"				: title
                    },
                    
                    async:false, 
                    dataType : 'jsonp',
   
                    success:function(response){  
                        
                        if(response.error.code == 0) {
                            
                            media_id = response.result.data[0].media_id;
                            insert_id = response.result.data[0]._id.$oid;
                            start_time = response.result.data[0].start_time;
                            total_play_duration = total_media_duration;

                        }
                        else {
                            media_id = "";
                            insert_id = "";
                            start_time = "";
                            total_play_duration = "";
                        }
                    },
                    error: function(data, status, error) {

                            alert("Something went wrong!");

                    },
                    complete: function() {

                    }	

            });	
            
        }
        //else {
function logTrackingUpdate(flag) {    
    
            var total_play_time = $("#jquery_jplayer_1").data("jPlayer").status.currentTime;
            
            var end_time = new Date().toLocaleString();
            
            $.ajax({
                    type : 'GET',		
                    //url : "http://192.168.22.103:810/SSO/uimage/knowledge/logClickCount?employee_id=10027097&start_time=2017-04-25 17:54:12",
                    url : "services.php?action=update",
                    data : {
                            "employee_id"               : employee_id,
                            "employee_name"             : employee_name,
                            "start_time"                : start_time,    
                            "end_time"                  : end_time,
                            "total_play_time"           : total_play_time,
                            "total_play_duration"       : total_play_duration,
                            "media_id"                  : media_id,
                            "flag"                      : flag // 1 - Pause ,2 - auto End
                    },
                    async:false, 
                    dataType : 'jsonp',
   
                    success:function(response){  
                        
                        
                    },
                    error: function(data, status, error) {

                            alert("Something went wrong!");

                    },
                    complete: function() {

                    }	

            });
            
        //}
}
//]]>
</script>
</head>
<body>
<div id="jquery_jplayer_1" class="jp-jplayer"></div>
<div id="jp_container_1" class="jp-audio" role="application" aria-label="media player">
	<div class="jp-type-single">
		<div class="jp-gui jp-interface">
			<div class="jp-controls">
				<button class="jp-play" role="button" tabindex="0">play</button>
				<button class="jp-stop" role="button" tabindex="0">stop</button>
			</div>
			<div class="jp-progress">
				<div class="jp-seek-bar">
					<div class="jp-play-bar"></div>
				</div>
			</div>
			<div class="jp-volume-controls">
				<button class="jp-mute" role="button" tabindex="0">mute</button>
				<button class="jp-volume-max" role="button" tabindex="0">max volume</button>
				<div class="jp-volume-bar">
					<div class="jp-volume-bar-value"></div>
				</div>
			</div>
			<div class="jp-time-holder">
				<div class="jp-current-time" role="timer" aria-label="time">&nbsp;</div>
				<div class="jp-duration" role="timer" aria-label="duration">&nbsp;</div>
				<div class="jp-toggles">
					<button class="jp-repeat" role="button" tabindex="0">repeat</button>
				</div>
			</div>
		</div>
		<div class="jp-details">
			<div class="jp-title" aria-label="title">&nbsp;</div>
		</div>
		<div class="jp-no-solution">
			<span>Update Required</span>
			To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
		</div>
	</div>
</div>
</body>

</html>

