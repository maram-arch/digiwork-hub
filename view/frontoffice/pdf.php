<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controller/CandidatureController.php';

$dbTemp  = Config::getConnexion();
$id_user = (int)$dbTemp->query(
    "SELECT id_user FROM user ORDER BY id_user ASC LIMIT 1"
)->fetchColumn();
if ($id_user <= 0) $id_user = 14;

$controller = new CandidatureController();
$candidatures = $controller->getCandidaturesByUser($id_user);

$dateRecherche   = trim($_GET['date'] ?? '');
$statutRecherche = trim($_GET['statut'] ?? '');
$tri             = $_GET['tri'] ?? 'Date';
$ordre           = $_GET['ordre'] ?? 'desc';

if (!in_array($tri, ['Date', 'Statut'], true)) {
    $tri = 'Date';
}

if (!in_array($ordre, ['asc', 'desc'], true)) {
    $ordre = 'desc';
}

$candidatures = array_values(array_filter($candidatures, function ($c) use ($dateRecherche, $statutRecherche) {
    $matchDate = ($dateRecherche === '' || ($c['Date'] ?? '') === $dateRecherche);
    $matchStatut = ($statutRecherche === '' || stripos($c['Statut'] ?? '', $statutRecherche) !== false);
    return $matchDate && $matchStatut;
}));

usort($candidatures, function ($a, $b) use ($tri, $ordre) {
    if ($tri === 'Date') {
        $valueA = strtotime($a['Date'] ?? '') ?: 0;
        $valueB = strtotime($b['Date'] ?? '') ?: 0;
        $result = $valueA <=> $valueB;
    } else {
        $result = strcmp(strtolower($a['Statut'] ?? ''), strtolower($b['Statut'] ?? ''));
    }

    return $ordre === 'desc' ? -$result : $result;
});

function pdf_text($text)
{
    $text = html_entity_decode((string)$text, ENT_QUOTES, 'UTF-8');
    if (function_exists('iconv')) {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    }
    $text = preg_replace('/[^\x20-\x7E]/', '', $text);
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}

function pdf_rgb(array $rgb): string
{
    return sprintf('%.3F %.3F %.3F', $rgb[0] / 255, $rgb[1] / 255, $rgb[2] / 255);
}

function pdf_rect(float $x, float $top, float $w, float $h, array $fill, ?array $stroke = null): string
{
    $cmd = "q\n" . pdf_rgb($fill) . " rg\n";
    if ($stroke) {
        $cmd .= pdf_rgb($stroke) . " RG\n";
    }
    $cmd .= sprintf('%.2F %.2F %.2F %.2F re ', $x, $top - $h, $w, $h);
    $cmd .= $stroke ? "B\nQ\n" : "f\nQ\n";
    return $cmd;
}

function pdf_text_at(float $x, float $y, string $text, int $size = 10, array $color = [30, 37, 53], string $font = 'F1'): string
{
    return "BT\n/" . $font . " " . $size . " Tf\n" . pdf_rgb($color) . " rg\n" .
        sprintf('%.2F %.2F Td ', $x, $y) . '(' . pdf_text($text) . ") Tj\nET\n";
}

function pdf_status_style(string $statut): array
{
    $s = strtolower($statut);
    if (strpos($s, 'accept') !== false) {
        return ['label' => 'Accepte', 'bar' => [16, 185, 129], 'bg' => [236, 253, 245], 'text' => [6, 95, 70]];
    }
    if (strpos($s, 'refus') !== false) {
        return ['label' => 'Refuse', 'bar' => [239, 68, 68], 'bg' => [255, 241, 241], 'text' => [153, 27, 27]];
    }
    return ['label' => 'En attente', 'bar' => [245, 158, 11], 'bg' => [255, 251, 235], 'text' => [146, 64, 14]];
}

function pdf_short(string $text, int $max): string
{
    $text = trim(preg_replace('/\s+/', ' ', $text));
    return strlen($text) > $max ? substr($text, 0, $max - 3) . '...' : $text;
}

function build_pdf(array $candidatures): string
{
    $pages = [];
    $page = '';
    $cardTop = 700;
    $index = 0;

    $startPage = function () use (&$page, $candidatures) {
        $page = '';
        $page .= pdf_rect(0, 842, 595, 842, [240, 242, 249]);
        $page .= pdf_rect(0, 842, 595, 96, [67, 94, 190]);
        $page .= pdf_text_at(40, 790, 'Mes candidatures', 24, [255, 255, 255], 'F2');
        $page .= pdf_text_at(40, 770, 'DigiWork Hub - ' . count($candidatures) . ' candidature(s)', 11, [238, 241, 251]);
        $page .= pdf_rect(420, 805, 130, 32, [238, 241, 251]);
        $page .= pdf_text_at(438, 785, 'Export PDF', 12, [67, 94, 190], 'F2');
    };

    $startPage();

    if (count($candidatures) === 0) {
        $page .= pdf_rect(40, 690, 515, 120, [255, 255, 255], [232, 236, 246]);
        $page .= pdf_text_at(195, 625, 'Aucune candidature trouvee', 16, [67, 94, 190], 'F2');
        $pages[] = $page;
    } else {
        foreach ($candidatures as $c) {
            if ($index > 0 && $index % 4 === 0) {
                $pages[] = $page;
                $startPage();
            }

            $slot = $index % 4;
            $top = $cardTop - ($slot * 150);
            $style = pdf_status_style($c['Statut'] ?? 'en attente');
            $titre = pdf_short($c['titre_offre'] ?? 'Offre', 54);
            $type = pdf_short($c['type_offre'] ?? 'Offre', 18);
            $adresse = pdf_short($c['adresse'] ?? '-', 62);
            $lettre = pdf_short($c['Lettre'] ?? '', 100);

            $page .= pdf_rect(40, $top, 515, 130, [255, 255, 255], [232, 236, 246]);
            $page .= pdf_rect(40, $top, 515, 5, $style['bar']);
            $page .= pdf_rect(58, $top - 22, 76, 18, [238, 241, 251]);
            $page .= pdf_text_at(68, $top - 35, strtoupper($type), 8, [67, 94, 190], 'F2');
            $page .= pdf_text_at(58, $top - 60, $titre, 15, [30, 37, 53], 'F2');

            $page .= pdf_rect(430, $top - 22, 94, 22, $style['bg']);
            $page .= pdf_text_at(446, $top - 38, $style['label'], 10, $style['text'], 'F2');

            $page .= pdf_rect(58, $top - 80, 135, 22, [245, 247, 255], [232, 236, 246]);
            $page .= pdf_text_at(68, $top - 95, 'Postule le ' . ($c['Date'] ?? '-'), 9, [124, 141, 176]);
            $page .= pdf_rect(205, $top - 80, 155, 22, [245, 247, 255], [232, 236, 246]);
            $page .= pdf_text_at(215, $top - 95, 'Limite ' . ($c['date_limite'] ?? '-'), 9, [124, 141, 176]);

            $page .= pdf_text_at(58, $top - 118, 'Adresse: ' . $adresse, 9, [124, 141, 176]);
            if ($lettre !== '') {
                $page .= pdf_text_at(58, $top - 134, 'Lettre: ' . $lettre, 8, [124, 141, 176]);
            }

            $index++;
        }
        $pages[] = $page;
    }

    $countPages = count($pages);
    $fontRegularId = 3 + ($countPages * 2);
    $fontBoldId = $fontRegularId + 1;
    $objects = [];
    $kids = [];

    $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
    for ($i = 0; $i < $countPages; $i++) {
        $pageId = 3 + ($i * 2);
        $contentId = $pageId + 1;
        $kids[] = $pageId . ' 0 R';
        $objects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 $fontRegularId 0 R /F2 $fontBoldId 0 R >> >> /Contents $contentId 0 R >>";
        $objects[$contentId] = "<< /Length " . strlen($pages[$i]) . " >>\nstream\n" . $pages[$i] . "\nendstream";
    }
    $objects[2] = "<< /Type /Pages /Kids [" . implode(' ', $kids) . "] /Count $countPages >>";
    $objects[$fontRegularId] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
    $objects[$fontBoldId] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>";
    ksort($objects);

    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach ($objects as $number => $object) {
        $offsets[$number] = strlen($pdf);
        $pdf .= $number . " 0 obj\n" . $object . "\nendobj\n";
    }

    $xref = strlen($pdf);
    $size = max(array_keys($objects)) + 1;
    $pdf .= "xref\n0 " . $size . "\n";
    $pdf .= "0000000000 65535 f \n";
    for ($i = 1; $i < $size; $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
    }
    $pdf .= "trailer\n<< /Size " . $size . " /Root 1 0 R >>\n";
    $pdf .= "startxref\n" . $xref . "\n%%EOF";

    return $pdf;
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="mes_candidatures.pdf"');
echo build_pdf($candidatures);
exit;
?>
