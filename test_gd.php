<?php
if (function_exists('imagecreatetruecolor')) {
    echo "✅ GD está activa";
} else {
    echo "❌ GD NO está activa";
}
?>