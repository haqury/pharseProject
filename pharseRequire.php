<?php

function searchRequire($textFile, $regular)
{
    preg_match_all($regular, $textFile, $queries);
    return !empty($addQueries) ? array_merge($queries[1], $addQueries) : $queries[1];
}

function searchFiles($files, $regular)
{
    $results = [];
    $files = is_array($files) ? $files : [$files];
    for ($i = 0; $i < count($files); $i++) {
        $result = searchRequire(file_get_contents($files[$i]), $regular);
        $GLOBALS['requires'] = array_merge($GLOBALS['requires'], $result);
        $results = !empty($result) ? array_merge($results, [PHP_EOL], [$files[$i]], searchRequire(file_get_contents($files[$i]), $regular)) : $results;
    }
    return $results;
}


function get_files($dir = ".")
{
    $files = array();
    if ($handle = opendir($dir)) {
        while (false !== ($item = readdir($handle))) {
            if (is_file("$dir/$item")) {
                if(strstr($item, '.php'))
                $files[] = "$dir/$item";
            } elseif (is_dir("$dir/$item") && ($item != ".") && ($item != "..")) {
                $files = array_merge($files, get_files("$dir/$item"));
            }
        }
        closedir($handle);
    }
    return $files;
}

function saveFile($array)
{
    $fd = fopen("requires.txt", 'w+');
    fputs($fd, implode(PHP_EOL, $array));
}

function getNeedFiles($files)
{
    foreach ($files as $file) {
        preg_match_all('@/(.+\.php)@', $file, $name);
        $names[] =  $name[1][0];
        foreach ($GLOBALS['requires'] as $require) {
            if (strstr($require, $name[1][0])) {
                $needFiles[] = $name[1][0];
            }
        }
    }
    $notNeedFiles = array_diff($names, $needFiles);

    $fd = fopen("needFiles.txt", 'w+');
    fputs($fd, 'нужные файлы' . implode(PHP_EOL, $needFiles) . PHP_EOL . PHP_EOL . 'не нужные файлы' . implode(PHP_EOL, $notNeedFiles) );
}

$GLOBALS['requires'] = [];
$files = get_files(".");

$results = searchFiles($files, '@require.+?\((.+)\);@');

saveFile($results);
getNeedFiles($files);