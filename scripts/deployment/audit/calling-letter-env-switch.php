<?php
// Private one-key atomic configuration switch; no values or credentials are printed.
[$unused, $root, $backup, $mode] = $argv;
try {
    if (!in_array($root,["/home/bfyigiln/reception-system","/home/bfyigiln/reception-system-demo"],true)) throw new RuntimeException("root");
    if (!preg_match("~^/home/bfyigiln/toolkit-private-storage/calling-letters-release-(?:demo|production)-[a-f0-9]{32}$~D",$backup)) throw new RuntimeException("backup");
    $path=$root."/.env";$old=file_get_contents($path);if($old===false)throw new RuntimeException("read");
    if(is_link($path)||realpath($root)!==$root)throw new RuntimeException("configuration_path");
    $permissions=fileperms($path)&0777;if($permissions&0022)throw new RuntimeException("writable_configuration");
    $pattern="/^[ \\t]*(?:export[ \\t]+)?CALLING_LETTERS_ENABLED[ \\t]*=.*$/m";
    preg_match_all($pattern,$old,$matches);if(count($matches[0])>1)throw new RuntimeException("ambiguous_flag");
    if($mode==="enable") {
        if(file_exists($backup)||is_link($backup))throw new RuntimeException("existing_backup");
        if(!mkdir($backup,0700,true)||!chmod($backup,0700))throw new RuntimeException("private_backup");
        if(file_put_contents($backup."/env.before",$old)!==strlen($old)||!chmod($backup."/env.before",0600))throw new RuntimeException("backup_write");
        if(is_file($root."/bootstrap/cache/config.php")) {if(!copy($root."/bootstrap/cache/config.php",$backup."/config-cache.before.php")||!chmod($backup."/config-cache.before.php",0600))throw new RuntimeException("cache_backup");}
    } elseif($mode!=="disable") throw new RuntimeException("mode");
    $line="CALLING_LETTERS_ENABLED=".($mode==="enable"?"true":"false");
    $new=count($matches[0])?preg_replace($pattern,$line,$old):$old.(str_ends_with($old,"\n")?"":"\n").$line."\n";
    $temporary=@tempnam($root,".env-letter-");
    if($temporary===false||realpath(dirname($temporary))!==realpath($root)){if(is_string($temporary)&&is_file($temporary))unlink($temporary);throw new RuntimeException("temporary");}
    try {
        if(!chmod($temporary,0600)||file_put_contents($temporary,$new)!==strlen($new))throw new RuntimeException("write");
        if(hash_file("sha256",$path)!==hash("sha256",$old))throw new RuntimeException("concurrent_configuration_change");
        if(!chmod($temporary,$permissions)||!rename($temporary,$path))throw new RuntimeException("rename");
    } finally {if(is_file($temporary))unlink($temporary);}
    echo json_encode(["native_letter_flag"=>$mode,"configuration_sha256"=>hash("sha256",$new)]),"\n";
} catch(Throwable $error) {echo json_encode(["configuration_switch"=>"failed"]),"\n";exit(1);}
