<?php
echo "Checking http://localhost:8000/login headers:\n";
$headers = @get_headers("http://localhost:8000/login", 1);
if ($headers) {
    foreach ($headers as $key => $val) {
        if (is_array($val)) $val = implode(', ', $val);
        echo "$key: $val\n";
    }
} else {
    echo "Failed to connect to localhost:8000\n";
}

echo "\nChecking http://localhost/login headers:\n";
$headers2 = @get_headers("http://localhost/login", 1);
if ($headers2) {
    foreach ($headers2 as $key => $val) {
        if (is_array($val)) $val = implode(', ', $val);
        echo "$key: $val\n";
    }
} else {
    echo "Failed to connect to localhost\n";
}
