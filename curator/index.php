<?php


require_once('../../config.php'); //assumes the config file is placed immediately outside the imree-php folder

$page = new page("<div>template builder is working</div>", $page_title);
echo $page;
