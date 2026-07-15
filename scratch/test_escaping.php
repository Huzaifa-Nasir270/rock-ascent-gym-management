<?php
$str = "Classes\\\\r\\\\nYoga";
echo stripslashes($str) . "\n";
$str = str_replace(array('\r', '\n', '\\r', '\\n', '\\'), '', $str);
echo $str . "\n";

$str2 = "trainer\\\\\\\\\\\\\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\\\\\\\\\\\\\nZumba";
$str2 = str_replace(array('\r', '\n', '\\'), '', $str2);
echo $str2 . "\n";
