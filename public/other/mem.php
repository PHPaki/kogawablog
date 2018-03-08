<?php
header('Content-type:text/html;charset=utf-8');
//引入memcached
$mem = new Memcache();
$mem -> connect('localhost', 11211);
$tag = $mem->get('tag');
if (!$tag) {
//查询数据库
$mysqli = new Mysqli('localhost', 'root', 'root', 'kogawa_blog');
if ($mysqli->connect_error) {
    die($mysqli->connect->error);
}
$mysqli->query('set names utf8');
$sql = "select name from tag limit 4";
$res = $mysqli->query($sql);
$tag = [];
if ($res) {
    while($row = $res->fetch_row()) {
	$tag[] = $row[0];
    }
    $res->free();
    $mem->add('tag', $tag, false, 8);
    echo "data is from mysql";
} else {
    die($mysqli->error);
}
$mysqli->close();
} else {
    echo "data is from memcache";
}

print_r($tag);	
