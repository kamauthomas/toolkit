<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root=$argv[1];require $root.'/vendor/autoload.php';$app=require $root.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$dir=$root.'/storage/app/private/audit-2026.10.05.1';if (!is_dir($dir)) mkdir($dir,0700,true);$file=$dir.'/before-archive-migration.sql';
if (is_file($file)) { echo "RECEPTION_DATABASE_BACKUP_EXISTS\n";exit; }
$pdo=Illuminate\Support\Facades\DB::connection()->getPdo();if($pdo->getAttribute(PDO::ATTR_DRIVER_NAME)!=='mysql')throw new RuntimeException('Unexpected database driver');
$out=fopen($file.'.partial','xb');chmod($file.'.partial',0600);fwrite($out,"SET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\n");$tables=$pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
foreach($tables as $table){$quoted='`'.str_replace('`','``',$table).'`';$schema=$pdo->query('SHOW CREATE TABLE '.$quoted)->fetch(PDO::FETCH_NUM);fwrite($out,'DROP TABLE IF EXISTS '.$quoted.";\n".$schema[1].";\n");$rows=$pdo->query('SELECT * FROM '.$quoted);while($row=$rows->fetch(PDO::FETCH_ASSOC)){$cols=implode(',',array_map(fn($key)=>'`'.str_replace('`','``',$key).'`',array_keys($row)));$values=implode(',',array_map(fn($value)=>$value===null?'NULL':$pdo->quote((string)$value),array_values($row)));fwrite($out,'INSERT INTO '.$quoted.' ('.$cols.') VALUES ('.$values.");\n");}}
fwrite($out,"SET FOREIGN_KEY_CHECKS=1;\n");fclose($out);rename($file.'.partial',$file);echo json_encode(['database_backup'=>'private','tables'=>count($tables),'bytes'=>filesize($file),'sha256'=>hash_file('sha256',$file)]).PHP_EOL;
