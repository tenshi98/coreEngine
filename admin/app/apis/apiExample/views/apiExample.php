<?php

echo '<p>token: '.$data['token'].'</p>';

echo '<pre>';
var_dump($data['decoded']);
echo '</pre>';

echo '<pre>';
echo $data['decoded']->iss;
echo '</pre>';
?>