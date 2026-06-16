<?php
require 'vendor/autoload.php';

echo "Checking classes...\n";

if (class_exists('Midtrans\Config')) {
    echo "- Midtrans class exists.\n";
} else {
    echo "- Midtrans class DOES NOT exist.\n";
}

if (class_exists('Dompdf\Dompdf')) {
    echo "- Dompdf class exists.\n";
} else {
    echo "- Dompdf class DOES NOT exist.\n";
}

if (class_exists('Endroid\QrCode\Builder\Builder')) {
    echo "- Endroid QrCode class exists.\n";
} else {
    echo "- Endroid QrCode class DOES NOT exist.\n";
}
