<?php
function cmp($a, $b)
{
    if ($a->id == $b->id) {
        return 0;
    }
    return ($a->id < $b->id) ? -1 : 1;
}
$basePath = dirname(__DIR__);
if(!file_exists($basePath . '/list.json')) {
    $json = json_decode(file_get_contents('https://schoolplayground.tyc.edu.tw/api/v1/companies/0'));
    usort($json->companies, 'cmp');
    file_put_contents($basePath . '/list.json', json_encode($json->companies, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $json = $json->companies;
} else {
    $json = json_decode(file_get_contents($basePath . '/list.json'));
}

foreach($json AS $playground) {
    $rawPath = $basePath . '/raw/' . $playground->id;
    if(!file_exists($rawPath)) {
        mkdir($rawPath, 0777, true);
    }
    $rawFile = $rawPath . '/page.html';
    if(!file_exists($rawFile)) {
        file_put_contents($rawFile, file_get_contents('https://schoolplayground.tyc.edu.tw/playground/' . $playground->id));
    }
    $raw = file_get_contents($rawFile);

    $pos = strpos($raw, '<div class="swiper-slide">');
    while(false !== $pos) {
        $pos = strpos($raw, 'https://schoolplayground.tyc.edu.tw', $pos);
        $posEnd = strpos($raw, '"', $pos);
        $imgUrl = substr($raw, $pos, $posEnd - $pos);
        $p = pathinfo($imgUrl);
        $d = pathinfo($p['dirname']);
        $imgFile = $rawPath . '/' . $d['filename'] . '_' . str_replace(' ', '_', $p['basename']);
        if(!file_exists($imgFile)) {
            file_put_contents($imgFile, file_get_contents($p['dirname'] . '/' . rawurlencode($p['basename'])));
        }
        $pos = strpos($raw, '<div class="swiper-slide">', $posEnd);
    }
}