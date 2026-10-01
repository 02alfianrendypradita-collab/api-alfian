<?php

if (function_exists('curl_init')) {
    echo "✅ cURL AKTIF";
} else {
    echo "❌ cURL TIDAK AKTIF";
}

?>