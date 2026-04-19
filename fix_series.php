<?php
$content = file_get_contents('series.php');
// Find the first occurrence of $pageScript = <<<'JSEND'
$pos1 = strpos($content, '$pageScript = <<<\'JSEND\'');
$pos2 = strpos($content, '$pageScript = <<<\'JSEND\'', $pos1 + 1);

if ($pos2 !== false) {
    // Cut the second one to the JSEND;
    $endPos = strpos($content, "JSEND;", $pos2) + 6;
    $content = substr($content, 0, $pos2) . substr($content, $endPos);
}

// Ensure ID is queried
$content = str_replace("SELECT title, release_year", "SELECT id, title, release_year", $content);

// Convert openModal routing
$content = preg_replace('/onclick="openModal\([^"]+\)"/', 'onclick="window.location.href=\'watch.php?id=<?= $m[\'id\'] ?>\'"', $content);

file_put_contents('series.php', $content);
echo "Series fixed!";
