<?php
$c=new PDO('mysql:host=localhost;dbname=video_portfolio','root','');
$s=$c->query('DESCRIBE reels');
print_r($s->fetchAll(PDO::FETCH_ASSOC));
$s2=$c->query('DESCRIBE works');
print_r($s2->fetchAll(PDO::FETCH_ASSOC));
$s3=$c->query('DESCRIBE videos');
print_r($s3->fetchAll(PDO::FETCH_ASSOC));
