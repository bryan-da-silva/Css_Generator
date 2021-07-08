<?php

$folder = $argv[$argc - 1];
$recursive = 0;
$no_recursive = 0;
$name_image = 'sprite.png';
$name_style = 'style.css';
$name_html = 'index.html';
$padding = 0;

if($folder == "--help" || $folder == "-h") {
    echo file_get_contents(".css_generator.txt") . "\n";
    exit;
}

if(!is_dir($folder)) {
    echo "css_generator : mauvaise syntaxe. Voir 'css_generator.php --help'.\n";
    exit;
} elseif($argc == 2 && is_dir($folder)) {
    $no_recursive = 2;
    scan_repertoire_Recursive($folder);
} else {
    $shortopts = "r" . "i::" . "s::" . "H::" . "p::";
    $longopts = array(
        "recursive",
        "output-image::",
        "output-style::",
        "output-html::",
        "padding::"
    );
    $Options = getopt($shortopts, $longopts);
    $no_recursive = 2;
    for ($i = 1; $i < $argc - 1; $i++) {
        foreach ($Options as $key => $item) {
            if($key == "recursive" || $key == "r") $no_recursive = 0;
            if($key == "output-image" || $key == "i") $name_image = $item . ".png";
            if($key == "output-style" || $key == "s") $name_style = $item . ".css";
            if($key == "output-html" || $key == "H") $name_html = $item . ".html";
            if($key == "padding" || $key == "p") $padding = $item;
        }
    }
    scan_repertoire_Recursive($folder);
}

if ($recursive == 0 || $no_recursive == 0) {
    $recursive = 1;
    scan_repertoire_Recursive($folder);
}

function scan_repertoire_Recursive($folder) {
    $folder2 = opendir($folder);
    while($fichier = readdir($folder2)) {
        $sub = substr($fichier, -4);
        if ($fichier == "." || $fichier == "..") continue;
        if ($GLOBALS['no_recursive'] != 2)
            if (is_dir($folder . '/' . $fichier)) scan_repertoire_Recursive($folder . '/' . $fichier);
        if ($sub == ".png") my_Css_Generator($folder . "/" . $fichier);
    }
    closedir($folder2);
}

function my_Css_Generator($folder) {
    static $y = 0;
    static $x = 0;
    list($width, $height) = getimagesize($folder);
    $info[] = array('chemin' => $folder, 'width' => $width, 'height' => $height, "vertical" => $y, "horizontal" => $x);
    $y += $height;
    $x += $width + $GLOBALS["padding"];
    $image = $GLOBALS['name_image'];
    if ($GLOBALS['recursive'] == 0) {
        $background = imagecreatetruecolor($x, $y);
    } elseif ($GLOBALS['recursive'] == 1) {
        static $x2 = 0;
        $info2[] = array('chemin' => $folder, 'width' => $width, 'height' => $height, "horizontal" => $x2);
        $x2 += $width + $GLOBALS["padding"];
        static $nom = 1;
        static $jump = 0;
        static $margin_left = 0;
        $background = imagecreatefrompng($GLOBALS['name_image']);
        foreach ($info2 as $item) {
            if ($jump == 0) {
                file_put_contents($GLOBALS['name_style'], "body {\n    margin: 0;\n}\n\n.img" . $nom . " {\n    "
                    . "background: " . "url(\"$image\");\n" . "    width: $width" . "px;" . "\n    "
                    . "height: " . $height . "px;\n}\n\n");
                my_Html($jump, $nom);
                $nom++;
                $jump++;
            } else {
                $test = $x2 - $GLOBALS['padding'];
                file_put_contents($GLOBALS['name_style'], ".img" . $nom . " {\n    " . "background: " .
                    "url(\"$image\");\n" . "    width: $test" . "px;" . "\n    margin-left: -$margin_left" . "px;\n" .
                    "    " . "height: " . $height . "px;\n}\n\n", FILE_APPEND);
                my_Html($jump, $nom);
                $nom++;
            }
            $tmp = imagecreatefrompng($item['chemin']);
            imagecopy($background, $tmp, $item['horizontal'], 0, 0, 0, $item['width'], $item['height']);
            imagedestroy($tmp);
        }
        $margin_left += $width + $GLOBALS['padding'];
    }
    imagepng($background, $GLOBALS['name_image']);
}

function my_Html($jump, $nom) {
    $style = $GLOBALS['name_style'];
    if ($jump == 0) {
        file_put_contents($GLOBALS['name_html'], "<!doctype html>\n<html lang=\"fr\">\n<head>\n" .
            "    <meta charset=\"utf-8\">\n" . "    <link rel=\"stylesheet\" href=\"$style\">\n" .
            "    <title>CSS_GENERATOR</title>" . "\n</head>\n<body>" . "\n    <div class=\"img$nom\"></div>\n");
    } else {
        file_put_contents($GLOBALS['name_html'], "    <div class=\"img$nom\"></div>\n", FILE_APPEND);
    }
}

file_put_contents($GLOBALS['name_html'], "</body>\n</html>", FILE_APPEND);