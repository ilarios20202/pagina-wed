<?php

function pdf_simple_output($filename, $lines) {
    $content = "BT\n/F1 12 Tf\n50 750 Td\n";
    foreach($lines as $line) {
        $safe = str_replace(')','\\)', str_replace('(','\\(', $line));
        $content .= "({$safe}) Tj\n0 -14 Td\n";
    }
    $content .= "ET\n";

    $objs = [];
    $objs[] = "%PDF-1.1\n";
    $objs[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    $objs[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
    $objs[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";
    $objs[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
    $stream = $content;
    $objs[] = "5 0 obj\n<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream\nendobj\n";

    $offsets = []; $pdf = '';
    foreach($objs as $o) { $offsets[] = strlen($pdf); $pdf .= $o; }
    $xref_pos = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objs)+1) . "\n0000000000 65535 f \n";
    foreach($offsets as $off) { $pdf .= sprintf("%010d 00000 n \n", $off); }
    $pdf .= "trailer\n<< /Size " . (count($objs)+1) . " /Root 1 0 R >>\nstartxref\n" . $xref_pos . "\n%%EOF";

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo $pdf; exit;
}
?>