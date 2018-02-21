<?php 
$dir = "../";
$title_here = "Проигрыватель";
include("header.html");
?>
<div id="jquery_jplayer_1" class="jp-jplayer"></div>

<div id="jp_container_1" class="jp-audio">
  <div class="jp-type-single">

    <div class="jp-title">
      <ul>
        <li>TEDxPhoenix - Kelli Anderson - Disruptive Wonder for a Change</li>
      </ul>
    </div>

    <div class="jp-gui jp-interface">

        <ul class="jp-controls">
          <li><a href="javascript:;" class="jp-play" tabindex="1">?</a></li>
          <li><a href="javascript:;" class="jp-pause" tabindex="1">?</a></li>
          <li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">?</a></li>
          <li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">?</a></li>
        </ul>

        <div class="jp-progress">
          <div class="jp-seek-bar">
            <div class="jp-play-bar"></div>
          </div>
        </div>

        <div class="jp-time-holder">
          <div class="jp-current-time"></div>
        </div>

        <div class="jp-volume-bar">
          <div class="jp-volume-bar-value"></div>
        </div>

    <div class="jp-no-solution">
      <span>Update Required</span>
      To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
    </div>
  </div>
</div>

<?php require_once ("footer.html");?>