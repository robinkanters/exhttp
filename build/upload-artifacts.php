<?php

require_once __DIR__.'/../vendor/autoload.php';

if($argc < 4) die('Not enough parameters');
$credentials = $argv[1];
$buildNumber = $argv[2];
$gitCommitId = $argv[3];

function build_curl($url, $httpMethod, $credentials)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($ch, CURLOPT_USERPWD, $credentials);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $httpMethod);

    return $ch;
}

function webdav_mkdir($uri, $credentials)
{
    $makeDirReq = build_curl($uri, 'MKCOL', $credentials);
    curl_exec($makeDirReq);
    curl_close($makeDirReq);
}

function webdav_upload_file($fileName, $uploadUri, $credentials)
{
    $fileSize = filesize($fileName);

    $fh = fopen($fileName, 'r');
    $ch = build_curl($uploadUri, 'PUT', $credentials);
    curl_setopt($ch, CURLOPT_INFILE, $fh);
    curl_setopt($ch, CURLOPT_INFILESIZE, $fileSize);
    curl_setopt($ch, CURLOPT_PUT, true);

    curl_exec($ch);
    fclose($fh);
}

$subdirName = $buildNumber . '_' . $gitCommitId . '_' . date('Ymd-His');
$uploadBaseUri = 'https://robinkanters.stackstorage.com/remote.php/webdav/Travis%20Build%20Artifacts/RobinKanters/EzHttp/';
$uploadUri = $uploadBaseUri . $subdirName . '/';

webdav_mkdir($uploadUri, $credentials);

$fileName = __DIR__.'/phpmetrics.html';

$remoteFilename = preg_replace('/(\.[a-z]+)$/i', sprintf('-%s$1', date('Y-m-d_H-i-s')), basename($fileName));

webdav_upload_file($fileName, $uploadUri . $remoteFilename, $credentials);
