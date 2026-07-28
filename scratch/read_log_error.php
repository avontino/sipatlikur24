<?php
$lines = file("c:/laragon/www/sinala3/storage/logs/laravel.log");
$total = count($lines);
$start = max(0, $total - 500);
for ($i = $start; $i < $total; $i++) {
    if (strpos($lines[$i], 'local.ERROR') !== false || strpos($lines[$i], 'Exception:') !== false || strpos($lines[$i], 'Error:') !== false) {
        for ($j = 0; $j < 12; $j++) {
            if (isset($lines[$i+$j])) {
                echo ($i+$j) . ": " . $lines[$i+$j];
            }
        }
        echo "====================================\n";
    }
}
