<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="plantilla-finania.csv"');

$output = fopen('php://output', 'w');
fputs($output, "\xEF\xBB\xBF"); // BOM para UTF-8

// Encabezados
fputcsv($output, ['Fecha', 'Descripción', 'Monto']);

// Datos de ejemplo
fputcsv($output, ['15/10/2026', 'Compra supermercado Éxito', '-85000']);
fputcsv($output, ['14/10/2026', 'Salario mensual', '3500000']);
fputcsv($output, ['13/10/2026', 'Uber al trabajo', '-18000']);
fputcsv($output, ['12/10/2026', 'Netflix suscripción', '-45000']);
fputcsv($output, ['11/10/2026', 'Pago freelance diseño', '800000']);
fputcsv($output, ['10/10/2026', 'Almuerzo restaurante', '-32000']);
fputcsv($output, ['09/10/2026', 'Gasolina Terpel', '-120000']);
fputcsv($output, ['08/10/2026', 'Farmacia Cruz Verde', '-28000']);

fclose($output);
exit;