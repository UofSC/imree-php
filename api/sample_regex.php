<?php

$some_content = "
<record>
  <alias>armstrong<alias>
  <pointer><![CDATA[168]]><pointer>
  <filetype><![CDATA[jp2]]><filetype>
  <parentobject><![CDATA[-1]]><parentobject>
  <find><![CDATA[156.jp2]]><find>
<record>
<record>
  <alias>armstrong<alias>
  <pointer><![CDATA[169]]><pointer>
  <filetype><![CDATA[jp2]]><filetype>
  <parentobject><![CDATA[-1]]><parentobject>
  <find><![CDATA[263.jp2]]><find>
<record>
<record>
  <alias>armstrong<alias>
  <pointer><![CDATA[170]]><pointer>
  <filetype><![CDATA[jp2]]><filetype>
  <parentobject><![CDATA[-1]]><parentobject>
  <find><![CDATA[238.jp2]]><find>
<record>
<record>
  <alias>bcp<alias>
  <pointer><![CDATA[10]]><pointer>
  <filetype><![CDATA[jpg]]><filetype>
  <parentobject><![CDATA[-1]]><parentobject>
  <find><![CDATA[11.jpg]]><find>
<record>
<record>
  <alias>bcp<alias>
  <pointer><![CDATA[11]]><pointer>
  <filetype><![CDATA[jpg]]><filetype>
  <parentobject><![CDATA[-1]]><parentobject>
  <find><![CDATA[12.jpg]]><find>
<record>
<record>
  <alias>bcp<alias>
  <pointer><![CDATA[12]]><pointer>
  <filetype><![CDATA[jpg]]><filetype>
  <parentobject><![CDATA[-1]]><parentobject>
  <find><![CDATA[13.jpg]]><find>
<record>";

$pointers = array();
$pattern = "/<pointer><!\[CDATA\[([0-9]*)\]\]>/";
preg_match_all($pattern, trim(preg_replace('/\s\s+/', ' ', $some_content)), $pointers);

$collections = array();
$pattern2 = "/<alias>([a-z]*)</";
preg_match_all($pattern2, trim(preg_replace('/\s\s+/', ' ', $some_content)), $collections);


for($i =0; $i<count($pointers[1]); $i++) {
	var_dump($collections[1][$i] . " -> " . $pointers[1][$i]);
}
	   
?>
