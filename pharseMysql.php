<?php

function searchQuery($textFile, $regular)
{
    $addQuery = [];
    preg_match_all($regular, $textFile, $queries);
    if(!empty($queries[0])) {
        foreach ($queries[1] as $oneQuery) {
            if (stristr($oneQuery, '($query)') && empty($addQuery)) {
                $addQueries = searchQuery($textFile, '@\$query.?=([^;]+)@');
                foreach ($addQueries as &$addQuery) {
                    $addQuery = preg_replace('#/n#', '', $addQuery);
                }
                break;
            }
        }
    }
    $queries[1] = array_diff($queries[1], ['($query)']);
    return !empty($addQueries) ? array_merge($queries[1], $addQueries) : $queries[1];
}

function searchFiles($files, $regular)
{
    $results = [];
    $files = is_array($files) ? $files : [$files];
    for ($i = 0; $i < count($files); $i++) {
        $result = searchQuery(file_get_contents($files[$i]), $regular);
        $results = !empty($result) ? array_merge($results, [$files[$i]], searchQuery(file_get_contents($files[$i]), $regular)) : $results;
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
    $fd = fopen("queryes.txt", 'w+');
    fputs($fd, implode(PHP_EOL, $array));
}

$files = get_files(".");

$results = searchFiles($files, '@mysql_query.?([^;]+)@');
saveFile($results);