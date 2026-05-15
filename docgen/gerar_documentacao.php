<?php
declare(strict_types=1);
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\Style\Table as TableStyle;

// ─── Constantes ──────────────────────────────────────────────────────────────
define('FONT_REG',  'C:/Windows/Fonts/arial.ttf');
define('FONT_BOLD', 'C:/Windows/Fonts/arialbd.ttf');
define('IMG_DIR', __DIR__ . '/imgs');

// Largura de imagem no documento (16 cm texto em escala PhpWord = 16/2.54*100)
define('DOC_IMG_W', 630);

// ─── Helpers GD ──────────────────────────────────────────────────────────────

function newCanvas(int $w, int $h, int $r = 255, int $g = 255, int $b = 255)
{
    $im = imagecreatetruecolor($w, $h);
    imagefill($im, 0, 0, imagecolorallocate($im, $r, $g, $b));
    return $im;
}

function col($im, string $hex): int
{
    $hex = ltrim($hex, '#');
    return imagecolorallocate($im,
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2))
    );
}

/** Divide texto em linhas respeitando largura máxima em pixels */
function wrapText(string $text, int $maxW, int $size, string $font): array
{
    $words = explode(' ', $text);
    $lines = [];
    $cur   = '';
    foreach ($words as $word) {
        $test = $cur === '' ? $word : "$cur $word";
        $bbox = imagettfbbox($size, 0, $font, $test);
        if (abs($bbox[2] - $bbox[0]) > $maxW && $cur !== '') {
            $lines[] = $cur;
            $cur = $word;
        } else {
            $cur = $test;
        }
    }
    if ($cur !== '') $lines[] = $cur;
    return $lines;
}

/** Desenha retângulo com texto centralizado (multi-linha) */
function drawBox($im, int $x, int $y, int $w, int $h,
                 string $fillHex, string $borderHex,
                 string $text, int $size, string $font, string $textHex): void
{
    $fill   = col($im, $fillHex);
    $border = col($im, $borderHex);
    $tc     = col($im, $textHex);
    imagefilledrectangle($im, $x, $y, $x + $w, $y + $h, $fill);
    imagerectangle($im, $x, $y, $x + $w, $y + $h, $border);
    imagerectangle($im, $x + 1, $y + 1, $x + $w - 1, $y + $h - 1, $border);

    $lines = wrapText($text, $w - 16, $size, $font);
    $lineH = (int)($size * 1.4);
    $totalH = count($lines) * $lineH;
    $startY = $y + ($h - $totalH) / 2 + $lineH * 0.8;
    foreach ($lines as $line) {
        $bbox = imagettfbbox($size, 0, $font, $line);
        $lw = abs($bbox[2] - $bbox[0]);
        imagettftext($im, $size, 0, (int)($x + ($w - $lw) / 2), (int)$startY, $tc, $font, $line);
        $startY += $lineH;
    }
}

/** Desenha losango (decisão) */
function drawDiamond($im, int $cx, int $cy, int $w, int $h,
                     string $fillHex, string $borderHex,
                     string $text, int $size, string $font, string $textHex): void
{
    $fill   = col($im, $fillHex);
    $border = col($im, $borderHex);
    $tc     = col($im, $textHex);
    $pts = [
        $cx,        $cy - $h / 2,
        $cx + $w / 2, $cy,
        $cx,        $cy + $h / 2,
        $cx - $w / 2, $cy,
    ];
    imagefilledpolygon($im, $pts, $fill);
    imagepolygon($im, $pts, $border);

    $lines = wrapText($text, $w - 30, $size, $font);
    $lineH = (int)($size * 1.4);
    $totalH = count($lines) * $lineH;
    $startY = $cy - $totalH / 2 + $lineH * 0.8;
    foreach ($lines as $line) {
        $bbox = imagettfbbox($size, 0, $font, $line);
        $lw = abs($bbox[2] - $bbox[0]);
        imagettftext($im, $size, 0, (int)($cx - $lw / 2), (int)$startY, $tc, $font, $line);
        $startY += $lineH;
    }
}

/** Desenha oval (início/fim) */
function drawOval($im, int $cx, int $cy, int $w, int $h,
                  string $fillHex, string $borderHex,
                  string $text, int $size, string $font, string $textHex): void
{
    $fill   = col($im, $fillHex);
    $border = col($im, $borderHex);
    $tc     = col($im, $textHex);
    imagefilledellipse($im, $cx, $cy, $w, $h, $fill);
    imageellipse($im, $cx, $cy, $w, $h, $border);
    $bbox = imagettfbbox($size, 0, $font, $text);
    $lw = abs($bbox[2] - $bbox[0]);
    imagettftext($im, $size, 0, (int)($cx - $lw / 2), (int)($cy + $size * 0.4), $tc, $font, $text);
}

/** Seta vertical de (x, y1) a (x, y2) */
function arrowDown($im, int $x, int $y1, int $y2, string $colorHex, string $label = ''): void
{
    $c = col($im, $colorHex);
    imagesetthickness($im, 2);
    imageline($im, $x, $y1, $x, $y2, $c);
    imagesetthickness($im, 1);
    $a = 8;
    imagefilledpolygon($im, [$x, $y2, $x - $a, $y2 - $a * 2, $x + $a, $y2 - $a * 2], $c);
    if ($label !== '') {
        $lc = col($im, '374151');
        imagettftext($im, 10, 0, $x + 6, (int)(($y1 + $y2) / 2), $lc, FONT_REG, $label);
    }
}

/** Seta horizontal de (x1, y) a (x2, y) */
function arrowRight($im, int $x1, int $y, int $x2, string $colorHex, string $label = ''): void
{
    $c = col($im, $colorHex);
    imagesetthickness($im, 2);
    imageline($im, $x1, $y, $x2, $y, $c);
    imagesetthickness($im, 1);
    $a = 8;
    imagefilledpolygon($im, [$x2, $y, $x2 - $a * 2, $y - $a, $x2 - $a * 2, $y + $a], $c);
    if ($label !== '') {
        $lc = col($im, '374151');
        imagettftext($im, 10, 0, (int)(($x1 + $x2) / 2), $y - 6, $lc, FONT_REG, $label);
    }
}

function savePng($im, string $name): string
{
    $path = IMG_DIR . "/$name.png";
    imagepng($im, $path, 6);
    return $path;
}

// ─── Gerador: Capa ───────────────────────────────────────────────────────────

function generateCoverImage(): string
{
    $w = 1588; $h = 2246;
    $im = imagecreatetruecolor($w, $h);

    // Gradiente de fundo escuro
    for ($y = 0; $y < $h; $y++) {
        $ratio = $y / $h;
        $c = imagecolorallocate($im,
            (int)(5  + (20 - 5)  * $ratio),
            (int)(5  + (20 - 5)  * $ratio),
            (int)(10 + (35 - 10) * $ratio)
        );
        imagefilledrectangle($im, 0, $y, $w, $y, $c);
    }

    $amber     = col($im, 'D97706');
    $white     = col($im, 'FFFFFF');
    $gray      = col($im, 'B0B0C0');
    $lightGray = col($im, '888899');
    $boxBg     = col($im, '0D0D1A');

    // Sigla GMP
    imagettftext($im, 110, 0, 160, 420, $amber, FONT_BOLD, 'GMP');

    // Linha separadora amber
    imagesetthickness($im, 5);
    imageline($im, 100, 470, $w - 100, 470, $amber);
    imagesetthickness($im, 1);

    // Nome do sistema
    imagettftext($im, 40, 0, 100, 560, $white, FONT_BOLD, 'Sistema de Gestão de');
    imagettftext($im, 40, 0, 100, 620, $white, FONT_BOLD, 'Manutenção Predial');

    // Subtítulo
    imagettftext($im, 24, 0, 100, 710, $gray, FONT_REG, 'Documentação Técnica do Sistema');

    // Linha
    imagesetthickness($im, 2);
    imageline($im, 100, 770, $w - 100, 770, $amber);
    imagesetthickness($im, 1);

    // Caixa de integrantes
    $bx1 = 100; $by1 = 850; $bx2 = $w - 100; $by2 = 1230;
    imagefilledrectangle($im, $bx1, $by1, $bx2, $by2, $boxBg);
    imagerectangle($im, $bx1, $by1, $bx2, $by2, $amber);
    imagerectangle($im, $bx1 + 2, $by1 + 2, $bx2 - 2, $by2 - 2, $amber);

    imagettftext($im, 22, 0, $bx1 + 45, $by1 + 65, $amber, FONT_BOLD, 'Integrantes:');
    $names = ['Jeferson Miguel', 'Lucas Terminiello', 'Matheus Malaman'];
    $ny = $by1 + 120;
    foreach ($names as $n) {
        imagettftext($im, 24, 0, $bx1 + 45, $ny, $white, FONT_REG, $n);
        $ny += 65;
    }
    imagettftext($im, 22, 0, $bx1 + 45, $by2 - 80, $gray,  FONT_BOLD, '3ºDEV  ·  SENAI');
    imagettftext($im, 20, 0, $bx1 + 45, $by2 - 40, $lightGray, FONT_REG, 'Maio de 2026');

    // Rodapé
    imagettftext($im, 19, 0, 260, $h - 80, $lightGray, FONT_REG, 'SENAI São Paulo  ·  2026  ·  Curso Técnico em Desenvolvimento de Sistemas');

    return savePng($im, 'capa');
}

// ─── Gerador: Diagrama ER ────────────────────────────────────────────────────

function generateERDiagram(): string
{
    $w = 1400; $h = 800;
    $im = newCanvas($w, $h);

    // Legendas de tabelas e colunas
    $tables = [
        'empresas' => [
            'x' => 80, 'y' => 80, 'w' => 320, 'h' => 300,
            'cols' => ['id (PK)', 'nome', 'cnpj (UNIQUE)', 'email', 'telefone', 'cep / estado', 'cidade / bairro', 'rua / numero'],
        ],
        'users' => [
            'x' => 530, 'y' => 80, 'w' => 340, 'h' => 340,
            'cols' => ['id (PK)', 'name', 'email (UNIQUE)', 'cpf (UNIQUE)', 'nif (UNIQUE)', 'telefone', 'cargo', 'ativo', 'foto_perfil', 'especialidades (JSON)', 'empresa_id (FK)', 'password'],
        ],
        'setors' => [
            'x' => 1000, 'y' => 80, 'w' => 320, 'h' => 200,
            'cols' => ['id (PK)', 'nome', 'andar', 'bloco'],
        ],
        'patrimonios' => [
            'x' => 1000, 'y' => 420, 'w' => 320, 'h' => 240,
            'cols' => ['id (PK)', 'codigo (UNIQUE)', 'valor', 'data_aquisicao', 'imagem', 'setor_id (FK)'],
        ],
    ];

    $navy  = '1E3A5F';
    $blue  = 'DBEAFE';
    $green = 'D1FAE5';
    $darkT = '1E3A5F';

    foreach ($tables as $name => $t) {
        $fill = ($name === 'users' || $name === 'empresas') ? $blue : $green;
        // Header
        drawBox($im, $t['x'], $t['y'], $t['w'], 40, $navy, $navy, strtoupper($name), 13, FONT_BOLD, 'FFFFFF');
        // Rows
        $ry = $t['y'] + 40;
        $rowH = max(26, (int)(($t['h'] - 40) / count($t['cols'])));
        foreach ($t['cols'] as $col) {
            $isPK = str_contains($col, '(PK)');
            $isFK = str_contains($col, '(FK)');
            $bg = $isPK ? 'FEF3C7' : ($isFK ? 'FCE7F3' : 'F8FAFC');
            drawBox($im, $t['x'], $ry, $t['w'], $rowH, $bg, 'CBD5E1', $col, 10, FONT_REG, '1E293B');
            $ry += $rowH;
        }
        imagerectangle($im, $t['x'], $t['y'], $t['x'] + $t['w'], $t['y'] + $t['h'], col($im, $navy));
    }

    // Relacionamento empresas → users (1:N)
    $arrowC = col($im, '374151');
    imagesetthickness($im, 2);
    // empresas right edge to users left edge
    imageline($im, 400, 200, 530, 200, $arrowC);
    $a = 8;
    imagefilledpolygon($im, [530, 200, 514, 192, 514, 208], $arrowC);
    imagettftext($im, 11, 0, 430, 188, col($im, 'D97706'), FONT_BOLD, '1:N');
    imagettftext($im, 10, 0, 418, 216, col($im, '374151'), FONT_REG, 'empresa_id');

    // setors bottom edge to patrimonios top edge
    imageline($im, 1160, 280, 1160, 420, $arrowC);
    imagefilledpolygon($im, [1160, 420, 1152, 404, 1168, 404], $arrowC);
    imagettftext($im, 11, 0, 1170, 360, col($im, 'D97706'), FONT_BOLD, '1:N');
    imagettftext($im, 10, 0, 1170, 378, col($im, '374151'), FONT_REG, 'setor_id');

    imagesetthickness($im, 1);

    // Legenda
    $ly = $h - 90;
    imagefilledrectangle($im, 80, $ly, 160, $ly + 20, col($im, 'DBEAFE'));
    imagerectangle($im, 80, $ly, 160, $ly + 20, col($im, $navy));
    imagettftext($im, 11, 0, 170, $ly + 15, col($im, '1E293B'), FONT_REG, 'Usuários / Empresas');

    imagefilledrectangle($im, 80, $ly + 28, 160, $ly + 48, col($im, 'D1FAE5'));
    imagerectangle($im, 80, $ly + 28, 160, $ly + 48, col($im, $navy));
    imagettftext($im, 11, 0, 170, $ly + 43, col($im, '1E293B'), FONT_REG, 'Setores / Patrimônios');

    imagefilledrectangle($im, 500, $ly, 580, $ly + 20, col($im, 'FEF3C7'));
    imagerectangle($im, 500, $ly, 580, $ly + 20, col($im, $navy));
    imagettftext($im, 11, 0, 590, $ly + 15, col($im, '1E293B'), FONT_REG, 'Chave Primária (PK)');

    imagefilledrectangle($im, 500, $ly + 28, 580, $ly + 48, col($im, 'FCE7F3'));
    imagerectangle($im, 500, $ly + 28, 580, $ly + 48, col($im, $navy));
    imagettftext($im, 11, 0, 590, $ly + 43, col($im, '1E293B'), FONT_REG, 'Chave Estrangeira (FK)');

    return savePng($im, 'er_diagram');
}

// ─── Gerador: Fluxo de Login ─────────────────────────────────────────────────

function generateLoginFlow(): string
{
    $w = 900; $h = 1400;
    $im = newCanvas($w, $h, 249, 250, 252);

    $cx = 450; $bw = 380; $bh = 55;
    $gap = 35;
    $y = 50;

    // 1. Início
    drawOval($im, $cx, $y + 30, $bw, 55, 'D1FAE5', '10B981', 'INÍCIO: Usuário acessa /admin', 13, FONT_BOLD, '065F46');
    $y += 60;
    arrowDown($im, $cx, $y, $y + $gap, '374151');
    $y += $gap;

    // 2. Decisão sessão ativa
    drawDiamond($im, $cx, $y + 60, $bw, 100, 'FEF3C7', 'D97706', 'Sessão autenticada?', 13, FONT_BOLD, '92400E');
    // Seta SIM → direita
    arrowRight($im, $cx + $bw / 2, $y + 60, $cx + $bw / 2 + 160, '374151', 'SIM');
    drawBox($im, $cx + $bw / 2 + 160, $y + 37, 190, 46, 'DBEAFE', '1E3A5F', 'Dashboard /admin', 11, FONT_REG, '1E3A5F');
    $y += 120;
    arrowDown($im, $cx, $y, $y + $gap, '374151', 'NÃO');
    $y += $gap;

    // 3. Redireciona login
    drawBox($im, $cx - $bw / 2, $y, $bw, $bh, 'DBEAFE', '1E3A5F', 'Redireciona para /admin/login', 12, FONT_REG, '1E3A5F');
    $y += $bh;
    arrowDown($im, $cx, $y, $y + $gap, '374151');
    $y += $gap;

    // 4. Formulário
    drawBox($im, $cx - $bw / 2, $y, $bw, $bh, 'DBEAFE', '1E3A5F', 'Exibe formulário de login (layout animado)', 12, FONT_REG, '1E3A5F');
    $y += $bh;
    arrowDown($im, $cx, $y, $y + $gap, '374151');
    $y += $gap;

    // 5. Usuário preenche
    drawBox($im, $cx - $bw / 2, $y, $bw, $bh, 'DBEAFE', '1E3A5F', 'Usuário insere e-mail e senha', 12, FONT_REG, '1E3A5F');
    $y += $bh;
    arrowDown($im, $cx, $y, $y + $gap, '374151');
    $y += $gap;

    // 6. POST
    drawBox($im, $cx - $bw / 2, $y, $bw, $bh, 'EDE9FE', '7C3AED', 'POST /admin/login → Filament autentica', 12, FONT_REG, '4C1D95');
    $y += $bh;
    arrowDown($im, $cx, $y, $y + $gap, '374151');
    $y += $gap;

    // 7. Decisão credenciais
    drawDiamond($im, $cx, $y + 60, $bw, 100, 'FEF3C7', 'D97706', 'Credenciais válidas?', 13, FONT_BOLD, '92400E');
    arrowRight($im, $cx + $bw / 2, $y + 60, $cx + $bw / 2 + 160, '374151', 'SIM');
    drawBox($im, $cx + $bw / 2 + 160, $y + 37, 190, 46, 'D1FAE5', '10B981', 'Sessão iniciada → Dashboard', 11, FONT_REG, '065F46');
    $y += 120;
    arrowDown($im, $cx, $y, $y + $gap, '374151', 'NÃO');
    $y += $gap;

    // 8. Erro
    drawOval($im, $cx, $y + 30, $bw, 55, 'FEE2E2', 'DC2626', 'Exibe erro: credenciais inválidas', 12, FONT_REG, '7F1D1D');
    $y += 60;
    arrowDown($im, $cx, $y, $y + 30, '374151');
    $y += 30;

    // Feedback loop (seta de volta ao campo de email)
    $lc = col($im, 'DC2626');
    $midY = (int)(370 + $bh / 2);
    $lx   = (int)($cx - $bw / 2);
    imagesetthickness($im, 2);
    imageline($im, $cx, $y, $cx, $y + 18, $lc);
    imageline($im, $cx, $y + 18, 55, $y + 18, $lc);
    imageline($im, 55, $y + 18, 55, $midY, $lc);
    imageline($im, 55, $midY, $lx, $midY, $lc);
    $a = 8;
    imagefilledpolygon($im, [$lx, $midY, $lx + $a * 2, $midY - $a, $lx + $a * 2, $midY + $a], $lc);
    imagettftext($im, 10, 0, 58, $y + 14, $lc, FONT_REG, 'Tenta novamente');
    imagesetthickness($im, 1);

    return savePng($im, 'login_flow');
}

// ─── Gerador: Fluxo CEP ──────────────────────────────────────────────────────

function generateCEPFlow(): string
{
    $w = 900; $h = 1200;
    $im = newCanvas($w, $h, 249, 250, 252);

    $cx = 450; $bw = 400; $bh = 55; $gap = 35;
    $y = 50;

    drawOval($im, $cx, $y + 28, $bw, 52, 'D1FAE5', '10B981', 'Usuário digita o CEP no formulário', 13, FONT_BOLD, '065F46');
    $y += 58; arrowDown($im, $cx, $y, $y + $gap, '374151'); $y += $gap;

    drawBox($im, $cx - $bw / 2, $y, $bw, $bh, 'DBEAFE', '1E3A5F', 'Evento onBlur disparado no campo CEP', 12, FONT_REG, '1E3A5F');
    $y += $bh; arrowDown($im, $cx, $y, $y + $gap, '374151'); $y += $gap;

    drawBox($im, $cx - $bw / 2, $y, $bw, $bh, 'DBEAFE', '1E3A5F', 'Remove máscara (somente dígitos numéricos)', 12, FONT_REG, '1E3A5F');
    $y += $bh; arrowDown($im, $cx, $y, $y + $gap, '374151'); $y += $gap;

    // Decisão 8 dígitos
    drawDiamond($im, $cx, $y + 60, $bw, 100, 'FEF3C7', 'D97706', 'CEP tem exatamente 8 dígitos?', 13, FONT_BOLD, '92400E');
    arrowRight($im, $cx + $bw / 2, $y + 60, $cx + $bw / 2 + 155, '374151', 'NÃO');
    drawOval($im, $cx + $bw / 2 + 155 + 90, $y + 60, 165, 48, 'F3F4F6', '6B7280', 'Nenhuma ação', 11, FONT_REG, '374151');
    $y += 120; arrowDown($im, $cx, $y, $y + $gap, '374151', 'SIM'); $y += $gap;

    drawBox($im, $cx - $bw / 2, $y, $bw, $bh, 'EDE9FE', '7C3AED', 'GET viacep.com.br/ws/{cep}/json/', 12, FONT_REG, '4C1D95');
    $y += $bh; arrowDown($im, $cx, $y, $y + $gap, '374151'); $y += $gap;

    drawDiamond($im, $cx, $y + 60, $bw, 100, 'FEF3C7', 'D97706', 'Resposta OK e sem campo "erro"?', 13, FONT_BOLD, '92400E');
    arrowRight($im, $cx + $bw / 2, $y + 60, $cx + $bw / 2 + 155, '374151', 'NÃO');
    drawOval($im, $cx + $bw / 2 + 155 + 90, $y + 60, 165, 48, 'FEE2E2', 'DC2626', 'Campos em branco', 11, FONT_REG, '7F1D1D');
    $y += 120; arrowDown($im, $cx, $y, $y + $gap, '374151', 'SIM'); $y += $gap;

    drawBox($im, $cx - $bw / 2, $y, $bw, $bh + 10, 'D1FAE5', '10B981', 'Preenche: estado (uf), cidade (localidade), bairro, rua (logradouro)', 12, FONT_REG, '065F46');
    $y += $bh + 10; arrowDown($im, $cx, $y, $y + $gap, '374151'); $y += $gap;

    drawOval($im, $cx, $y + 28, $bw, 52, 'D1FAE5', '10B981', 'Usuário completa número e complemento', 13, FONT_BOLD, '065F46');

    return savePng($im, 'cep_flow');
}

// ─── Gerador: Fluxo CRUD ─────────────────────────────────────────────────────

function generateCRUDFlow(): string
{
    $w = 1400; $h = 900;
    $im = newCanvas($w, $h, 249, 250, 252);

    // Início
    drawOval($im, 700, 50, 500, 52, 'D1FAE5', '10B981', 'Usuário acessa a listagem (/admin/{recurso})', 13, FONT_BOLD, '065F46');
    arrowDown($im, 700, 76, 110, '374151');

    // Seleção de ação (losango)
    drawDiamond($im, 700, 175, 400, 100, 'FEF3C7', 'D97706', 'Seleciona ação', 14, FONT_BOLD, '92400E');

    // Branch CRIAR (esquerda)
    arrowRight($im, 500, 175, 360, '374151', 'CRIAR');
    drawBox($im, 200, 153, 160, 44, 'DBEAFE', '1E3A5F', 'Clica em "Novo"', 11, FONT_REG, '1E3A5F');
    arrowDown($im, 280, 197, 260, '374151');
    drawBox($im, 200, 260, 160, 44, 'DBEAFE', '1E3A5F', 'Form vazio', 11, FONT_REG, '1E3A5F');
    arrowDown($im, 280, 304, 360, '374151');
    drawDiamond($im, 280, 425, 180, 80, 'FEF3C7', 'D97706', 'Validação OK?', 11, FONT_BOLD, '92400E');
    arrowDown($im, 280, 465, 530, '374151', 'SIM');
    drawBox($im, 200, 530, 160, 44, 'D1FAE5', '10B981', 'INSERT no banco', 11, FONT_REG, '065F46');
    // NO branch
    arrowRight($im, 370, 425, 420, 'DC2626', 'NÃO');
    imagettftext($im, 10, 0, 425, 440, col($im, '7F1D1D'), FONT_REG, 'Exibe erros');

    // Branch EDITAR (centro)
    arrowDown($im, 700, 225, 260, '374151', 'EDITAR');
    drawBox($im, 610, 260, 180, 44, 'DBEAFE', '1E3A5F', 'Clica em Editar', 11, FONT_REG, '1E3A5F');
    arrowDown($im, 700, 304, 360, '374151');
    drawBox($im, 610, 360, 180, 44, 'DBEAFE', '1E3A5F', 'Form preenchido', 11, FONT_REG, '1E3A5F');
    arrowDown($im, 700, 404, 460, '374151');
    drawDiamond($im, 700, 525, 180, 80, 'FEF3C7', 'D97706', 'Validação OK?', 11, FONT_BOLD, '92400E');
    arrowDown($im, 700, 565, 620, '374151', 'SIM');
    drawBox($im, 610, 620, 180, 44, 'D1FAE5', '10B981', 'UPDATE no banco', 11, FONT_REG, '065F46');
    arrowRight($im, 790, 525, 840, 'DC2626', 'NÃO');
    imagettftext($im, 10, 0, 845, 540, col($im, '7F1D1D'), FONT_REG, 'Exibe erros');

    // Branch EXCLUIR (direita)
    arrowRight($im, 900, 175, 1040, '374151', 'EXCLUIR');
    drawBox($im, 1040, 153, 180, 44, 'DBEAFE', '1E3A5F', 'Seleciona registros', 11, FONT_REG, '1E3A5F');
    arrowDown($im, 1130, 197, 260, '374151');
    drawBox($im, 1040, 260, 180, 44, 'FEE2E2', 'DC2626', 'Confirma exclusão', 11, FONT_REG, '7F1D1D');
    arrowDown($im, 1130, 304, 360, '374151');
    drawBox($im, 1040, 360, 180, 44, 'FEE2E2', 'DC2626', 'DELETE no banco', 11, FONT_REG, '7F1D1D');
    arrowDown($im, 1130, 404, 620, '374151');

    // Convergência → flash message
    $fc = col($im, '374151');
    imagesetthickness($im, 2);
    imageline($im, 280, 574, 280, 720, $fc);
    imageline($im, 700, 664, 700, 720, $fc);
    imageline($im, 1130, 620, 1130, 720, $fc);
    imageline($im, 280, 720, 1130, 720, $fc);
    imageline($im, 700, 720, 700, 755, $fc);
    $a = 8;
    imagefilledpolygon($im, [700, 755, 692, 739, 708, 739], $fc);
    imagesetthickness($im, 1);

    drawBox($im, 510, 755, 380, 52, 'DBEAFE', '1E3A5F', 'Lista atualizada + flash message de sucesso', 12, FONT_REG, '1E3A5F');
    arrowDown($im, 700, 807, 850, '374151');
    drawOval($im, 700, 870, 300, 48, 'D1FAE5', '10B981', 'FIM', 13, FONT_BOLD, '065F46');

    return savePng($im, 'crud_flow');
}

// ─── Gerador: Hierarquia de Perfis ───────────────────────────────────────────

function generateRolesHierarchy(): string
{
    $w = 1200; $h = 800;
    $im = newCanvas($w, $h, 249, 250, 252);

    // Raiz: tabela users
    drawBox($im, 400, 30, 400, 55, '1E3A5F', '1E3A5F', 'Tabela "users" (model User)', 14, FONT_BOLD, 'FFFFFF');

    // Linha para baixo
    $lc = col($im, '374151');
    imagesetthickness($im, 2);
    imageline($im, 600, 85, 600, 130, $lc);
    imageline($im, 200, 130, 1000, 130, $lc);
    imageline($im, 200, 130, 200, 165, $lc);
    imageline($im, 600, 130, 600, 165, $lc);
    imageline($im, 1000, 130, 1000, 165, $lc);
    imagesetthickness($im, 1);

    // Branch 1: UserResource (admin/diretor/professor/suporte)
    drawBox($im, 30, 165, 340, 55, 'D97706', 'D97706', 'UserResource  (Administradores)', 12, FONT_BOLD, 'FFFFFF');
    $cargos1 = ['admin', 'diretor', 'professor', 'suporte'];
    $cy1 = 245;
    foreach ($cargos1 as $c) {
        $labels = ['admin' => 'Admin Geral', 'diretor' => 'Diretor', 'professor' => 'Professor', 'suporte' => 'Suporte'];
        drawBox($im, 30, $cy1, 340, 45, 'FEF3C7', 'D97706', "$c — {$labels[$c]}", 11, FONT_REG, '92400E');
        $cy1 += 52;
    }

    // Branch 2: ColaboradorResource
    drawBox($im, 430, 165, 340, 55, '1E3A5F', '1E3A5F', 'ColaboradorResource', 12, FONT_BOLD, 'FFFFFF');
    drawBox($im, 430, 245, 340, 45, 'DBEAFE', '1E3A5F', 'cargo = colaborador', 11, FONT_REG, '1E3A5F');
    $especialidades = ['hidraulica', 'eletrica', 'alvenaria', 'pintura', 'ar_condicionado', 'marcenaria', 'serralheria'];
    $eY = 297;
    imagettftext($im, 11, 0, 435, $eY + 14, col($im, '374151'), FONT_BOLD, 'Especialidades (JSON):');
    $eY += 24;
    $espLabels = ['Hidráulica', 'Elétrica', 'Alvenaria/Pedreiro', 'Pintura', 'Ar Condicionado', 'Marcenaria', 'Serralheria'];
    foreach ($espLabels as $e) {
        imagettftext($im, 10, 0, 445, $eY + 13, col($im, '1E3A5F'), FONT_REG, "• $e");
        $eY += 18;
    }

    // Branch 3: ResponsavelResource
    drawBox($im, 830, 165, 340, 55, '065F46', '065F46', 'ResponsavelResource', 12, FONT_BOLD, 'FFFFFF');
    drawBox($im, 830, 245, 340, 45, 'D1FAE5', '10B981', 'cargo = responsavel', 11, FONT_REG, '065F46');
    imagettftext($im, 11, 0, 835, 310, col($im, '374151'), FONT_BOLD, 'Campo exclusivo:');
    imagettftext($im, 11, 0, 845, 330, col($im, '065F46'), FONT_REG, '• nif (Nº de Identificação) — UNIQUE');
    imagettftext($im, 11, 0, 835, 358, col($im, '374151'), FONT_BOLD, 'Vinculado a:');
    imagettftext($im, 11, 0, 845, 378, col($im, '065F46'), FONT_REG, '• Empresa (empresa_id FK)');

    // Nota inferior
    $noteY = $h - 110;
    imagefilledrectangle($im, 30, $noteY, $w - 30, $h - 20, col($im, 'FFFBEB'));
    imagerectangle($im, 30, $noteY, $w - 30, $h - 20, col($im, 'D97706'));
    imagettftext($im, 12, 0, 45, $noteY + 22, col($im, '92400E'), FONT_BOLD, 'Obs:');
    imagettftext($im, 11, 0, 80, $noteY + 22, col($im, '374151'), FONT_REG, 'Todos os perfis compartilham a mesma tabela "users". A separação ocorre via coluna "cargo".');
    imagettftext($im, 11, 0, 45, $noteY + 44, col($im, '374151'), FONT_REG, 'Cada Resource aplica getEloquentQuery() com whereIn/where para filtrar os registros exibidos.');
    imagettftext($im, 11, 0, 45, $noteY + 66, col($im, '374151'), FONT_REG, 'Não há Laravel Gates/Policies implementadas — o controle é por filtro Eloquent no próprio Resource.');

    return savePng($im, 'roles_hierarchy');
}

// ─── Helpers PhpWord ─────────────────────────────────────────────────────────

function sty(PhpWord $pw): void
{
    $pw->setDefaultFontName('Times New Roman');
    $pw->setDefaultFontSize(12);

    $pw->addTitleStyle(1, ['name' => 'Times New Roman', 'size' => 14, 'bold' => true, 'color' => '1E3A5F'], ['alignment' => Jc::CENTER, 'spaceBefore' => 280, 'spaceAfter' => 160]);
    $pw->addTitleStyle(2, ['name' => 'Times New Roman', 'size' => 12, 'bold' => true, 'color' => '1E3A5F'], ['spaceBefore' => 240, 'spaceAfter' => 120]);
    $pw->addTitleStyle(3, ['name' => 'Times New Roman', 'size' => 12, 'bold' => true, 'italic' => true, 'color' => '374151'], ['spaceBefore' => 160, 'spaceAfter' => 80]);
}

function secStyle(): array
{
    return [
        'pageSizeW' => Converter::cmToTwip(21),
        'pageSizeH' => Converter::cmToTwip(29.7),
        'marginLeft'   => Converter::cmToTwip(3),
        'marginRight'  => Converter::cmToTwip(2),
        'marginTop'    => Converter::cmToTwip(3),
        'marginBottom' => Converter::cmToTwip(2),
    ];
}

$BF  = ['name' => 'Times New Roman', 'size' => 12];
$BP  = ['alignment' => Jc::BOTH, 'lineHeight' => 1.5, 'spaceAfter' => 120];
$CF  = ['name' => 'Courier New', 'size' => 10];
$CP  = ['alignment' => Jc::LEFT];

function body($section, string $text): void
{
    global $BF, $BP;
    $section->addText($text, $BF, $BP);
}

function bullet($section, string $text): void
{
    global $BF;
    $section->addListItem($text, 0, $BF, ['listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_BULLET_FILLED], ['spaceAfter' => 60]);
}

function addImg($section, string $path, string $caption = ''): void
{
    $section->addImage($path, ['width' => DOC_IMG_W, 'alignment' => Jc::CENTER]);
    if ($caption !== '') {
        $section->addText($caption, ['name' => 'Times New Roman', 'size' => 10, 'italic' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 200]);
    }
}

/**
 * Cria tabela estilizada. $headers = array of strings, $rows = array of arrays.
 * $widths = array de inteiros em twips (total ~9071 para margens padrão).
 */
function addStyledTable($section, array $headers, array $rows, array $widths): void
{
    $tbl = $section->addTable([
        'borderSize' => 6, 'borderColor' => 'CBD5E1',
        'cellMarginTop' => 60, 'cellMarginBottom' => 60,
        'cellMarginLeft' => 100, 'cellMarginRight' => 100,
    ]);

    // Header row
    $row = $tbl->addRow(500);
    foreach ($headers as $i => $h) {
        $cell = $row->addCell($widths[$i] ?? 2000, ['bgColor' => '1E3A5F', 'valign' => 'center']);
        $cell->addText($h, ['name' => 'Times New Roman', 'size' => 10, 'bold' => true, 'color' => 'FFFFFF'], ['alignment' => Jc::CENTER]);
    }

    // Data rows
    foreach ($rows as $ri => $row_data) {
        $bg = ($ri % 2 === 0) ? 'FFFFFF' : 'F8FAFC';
        $tr = $tbl->addRow();
        foreach ($row_data as $ci => $cell_val) {
            $cell = $tr->addCell($widths[$ci] ?? 2000, ['bgColor' => $bg, 'valign' => 'top']);
            $cell->addText((string)$cell_val, ['name' => 'Times New Roman', 'size' => 10], ['alignment' => Jc::LEFT]);
        }
    }

    $section->addTextBreak(1);
}

function codeBlock($section, string $code): void
{
    $tbl = $section->addTable(['borderSize' => 4, 'borderColor' => 'CBD5E1', 'cellMarginTop' => 80, 'cellMarginBottom' => 80, 'cellMarginLeft' => 120, 'cellMarginRight' => 120]);
    $row = $tbl->addRow();
    $cell = $row->addCell(9071, ['bgColor' => 'F1F5F9']);
    foreach (explode("\n", $code) as $line) {
        $cell->addText($line, ['name' => 'Courier New', 'size' => 9], ['alignment' => Jc::LEFT, 'spaceAfter' => 0]);
    }
    $section->addTextBreak(1);
}

// ─── Seções do documento ─────────────────────────────────────────────────────

function addCoverSection(PhpWord $pw, string $coverPath): void
{
    $sec = $pw->addSection([
        'pageSizeW' => Converter::cmToTwip(21),
        'pageSizeH' => Converter::cmToTwip(29.7),
        'marginLeft' => Converter::cmToTwip(0.5),
        'marginRight' => Converter::cmToTwip(0.5),
        'marginTop' => Converter::cmToTwip(0.5),
        'marginBottom' => Converter::cmToTwip(0.5),
    ]);
    // Imagem de capa a largura quase total
    $sec->addImage($coverPath, [
        'width' => 790,
        'alignment' => Jc::CENTER,
    ]);
}

function addIntroducao($sec): void
{
    $sec->addTitle('1. INTRODUÇÃO', 1);
    body($sec, 'O Sistema de Gestão de Manutenção Predial (GMP) é uma aplicação web desenvolvida para gerenciar e controlar as atividades de manutenção em instalações prediais do SENAI. O sistema centraliza o cadastro de empresas, setores, patrimônios (ativos físicos), colaboradores e responsáveis, proporcionando uma visão integrada de todos os recursos envolvidos na manutenção.');
    body($sec, 'O objetivo principal do sistema é organizar e digitalizar o controle patrimonial e de equipes de manutenção, substituindo processos manuais e descentralizados por um painel administrativo moderno, acessível via navegador web.');

    $sec->addTitle('1.1 Público-Alvo', 2);
    bullet($sec, 'Administradores e gestores do SENAI responsáveis pela manutenção predial');
    bullet($sec, 'Diretores e professores com acesso ao painel administrativo');
    bullet($sec, 'Responsáveis que acompanham ordens de serviço');
    bullet($sec, 'Colaboradores técnicos que executam as manutenções');

    $sec->addTitle('1.2 Contexto do Projeto', 2);
    body($sec, 'Este projeto foi desenvolvido como trabalho acadêmico pelos alunos da turma 3ºDEV do Curso Técnico em Desenvolvimento de Sistemas do SENAI São Paulo, sob orientação docente. A aplicação foi construída com tecnologias modernas do ecossistema PHP/Laravel, sendo o Laravel Filament responsável pela geração automática do painel administrativo CRUD.');
}

function addTecnologias($sec): void
{
    $sec->addTitle('2. TECNOLOGIAS UTILIZADAS', 1);
    body($sec, 'O sistema GMP foi construído utilizando um conjunto de tecnologias modernas e consolidadas no mercado de desenvolvimento web. A seguir, cada tecnologia é descrita com sua versão e finalidade dentro do projeto.');

    $headers = ['Tecnologia', 'Versão', 'Finalidade'];
    $widths  = [2000, 1200, 5871];
    $rows = [
        ['PHP', '8.2+', 'Linguagem de programação server-side. Base do Laravel e de toda a lógica de negócio.'],
        ['Laravel', '12.0', 'Framework PHP MVC. Gerencia rotas, autenticação, Eloquent ORM, migrações e serviços.'],
        ['Filament', '3.3', 'Pacote para Laravel que gera automaticamente painéis administrativos (CRUD) a partir de Resources PHP.'],
        ['MySQL', '8.0+', 'Sistema de gerenciamento de banco de dados relacional. Armazena todos os dados da aplicação.'],
        ['Tailwind CSS', '4.0', 'Framework CSS utility-first. Estiliza todos os componentes do painel Filament.'],
        ['Vite', '6.0', 'Bundler de front-end moderno. Compila e otimiza os assets CSS e JavaScript.'],
        ['Composer', '2.x', 'Gerenciador de dependências PHP. Instala e gerencia todos os pacotes do projeto.'],
        ['Node.js / npm', '20+', 'Runtime JavaScript e gerenciador de pacotes. Necessário para build do front-end.'],
        ['ViaCEP API', 'Pública', 'API REST pública para consulta de endereços brasileiros via CEP. Integrada no formulário de Empresa.'],
        ['Laravel Tinker', '2.x', 'Shell interativo para debug e manipulação direta dos modelos via CLI.'],
        ['PHPUnit', '11.x', 'Framework de testes automatizados PHP. Utilizado nos testes do projeto.'],
        ['Laravel Pint', '1.x', 'Formatador de código PHP baseado em PHP-CS-Fixer. Mantém estilo consistente.'],
        ['Laravel Sail', '1.x', 'Ambiente de desenvolvimento Docker para o Laravel.'],
    ];
    addStyledTable($sec, $headers, $rows, $widths);
}

function addArquitetura($sec): void
{
    $sec->addTitle('3. ARQUITETURA DO SISTEMA', 1);
    body($sec, 'O GMP segue o padrão arquitetural MVC (Model-View-Controller), implementado pelo framework Laravel. A camada de visão e controle é amplamente gerenciada pelo Filament, que gera automaticamente as interfaces e rotas a partir das classes Resource definidas pelo desenvolvedor.');

    $sec->addTitle('3.1 Camadas da Aplicação', 2);
    $headers = ['Camada', 'Tecnologia', 'Responsabilidade'];
    $widths  = [2000, 2000, 5071];
    $rows = [
        ['Model',      'Eloquent ORM', 'Representa as entidades do banco de dados (User, Empresa, Setor, Patrimonio). Define relacionamentos e atributos.'],
        ['View',       'Blade + Filament', 'Filament gera automaticamente as views de listagem, criação e edição via Livewire. Login usa view Blade customizada.'],
        ['Controller', 'Filament Resources', 'Cada Resource (UserResource, EmpresaResource, etc.) atua como controller, definindo formulários, tabelas e ações.'],
        ['Rotas',      'Laravel + Filament', 'web.php define a rota raiz (/). Filament gera automaticamente todas as rotas /admin/*.'],
        ['Banco',      'MySQL + Migrations', 'Estrutura definida via migrations Laravel. Eloquent ORM abstrai as queries SQL.'],
        ['Sessão',     'Database Driver', 'Sessões armazenadas na tabela "sessions" do banco de dados MySQL.'],
        ['Cache',      'Database Driver', 'Cache armazenado na tabela "cache" do banco de dados MySQL.'],
    ];
    addStyledTable($sec, $headers, $rows, $widths);

    $sec->addTitle('3.2 Padrão de Organização dos Arquivos', 2);
    body($sec, 'A estrutura do projeto segue as convenções do Laravel, com adições específicas do Filament:');
    bullet($sec, 'app/Models/ — Modelos Eloquent (User, Empresa, Setor, Patrimonio)');
    bullet($sec, 'app/Filament/Resources/ — Resources Filament (um por entidade gerenciada)');
    bullet($sec, 'app/Filament/Pages/ — Páginas customizadas (Login)');
    bullet($sec, 'app/Providers/Filament/ — Configuração do painel administrativo');
    bullet($sec, 'database/migrations/ — Arquivos de migração do banco de dados');
    bullet($sec, 'resources/views/filament/ — Views Blade customizadas do Filament');
    bullet($sec, 'routes/web.php — Única rota web explícita (/)');
}

function addBancoDeDados($sec, string $erPath): void
{
    $sec->addTitle('4. BANCO DE DADOS', 1);
    body($sec, 'O banco de dados do GMP é MySQL, acessado via Eloquent ORM do Laravel. A estrutura é definida por migrations versionadas. A seguir, apresenta-se o schema completo de cada tabela.');

    // users
    $sec->addTitle('4.1 Tabela: users', 2);
    body($sec, 'Tabela central do sistema. Armazena todos os tipos de usuários (admin, diretor, professor, suporte, colaborador, responsavel) diferenciados pela coluna "cargo".');
    $h = ['Coluna', 'Tipo', 'Restrições', 'Descrição'];
    $w = [2000, 1800, 2000, 3271];
    addStyledTable($sec, $h, [
        ['id',              'BIGINT UNSIGNED',  'PK, AUTO_INCREMENT, NOT NULL',         'Identificador único'],
        ['name',            'VARCHAR(255)',      'NOT NULL',                             'Nome completo do usuário'],
        ['email',           'VARCHAR(255)',      'UNIQUE, NOT NULL',                     'Endereço de e-mail para acesso'],
        ['email_verified_at','TIMESTAMP',       'NULLABLE',                             'Data de verificação do e-mail'],
        ['password',        'VARCHAR(255)',      'NOT NULL',                             'Hash bcrypt da senha'],
        ['cpf',             'VARCHAR(255)',      'UNIQUE, NULLABLE',                     'CPF do usuário (máscara 999.999.999-99)'],
        ['nif',             'VARCHAR(255)',      'UNIQUE, NULLABLE',                     'Número de Identificação Funcional (responsáveis)'],
        ['telefone',        'VARCHAR(255)',      'NULLABLE',                             'Telefone/WhatsApp'],
        ['cargo',           'VARCHAR(255)',      'DEFAULT "admin", NOT NULL',            'Perfil: admin | diretor | professor | suporte | colaborador | responsavel'],
        ['ativo',           'BOOLEAN',          'DEFAULT true, NOT NULL',               'Indica se o usuário está ativo no sistema'],
        ['foto_perfil',     'VARCHAR(255)',      'NULLABLE',                             'Caminho do arquivo de avatar no storage'],
        ['empresa_id',      'BIGINT UNSIGNED',  'FK→empresas.id, NULLABLE, SET NULL',   'Empresa vinculada ao usuário'],
        ['especialidades',  'JSON',             'NULLABLE',                             'Array de especialidades (somente colaboradores)'],
        ['remember_token',  'VARCHAR(100)',      'NULLABLE',                             'Token da opção "lembrar-me"'],
        ['created_at',      'TIMESTAMP',        'NULLABLE',                             'Data de criação (Eloquent)'],
        ['updated_at',      'TIMESTAMP',        'NULLABLE',                             'Data de atualização (Eloquent)'],
    ], $w);

    // empresas
    $sec->addTitle('4.2 Tabela: empresas', 2);
    addStyledTable($sec, $h, [
        ['id',          'BIGINT UNSIGNED', 'PK, AUTO_INCREMENT', 'Identificador único'],
        ['nome',        'VARCHAR(255)',    'NOT NULL',            'Razão social ou nome fantasia'],
        ['cnpj',        'VARCHAR(255)',    'UNIQUE, NOT NULL',    'CNPJ (máscara 99.999.999/9999-99)'],
        ['email',       'VARCHAR(255)',    'NULLABLE',            'E-mail de contato'],
        ['telefone',    'VARCHAR(255)',    'NULLABLE',            'Telefone de contato'],
        ['cep',         'VARCHAR(255)',    'NULLABLE',            'CEP (máscara 99999-999)'],
        ['estado',      'VARCHAR(2)',      'NULLABLE',            'UF do estado (2 caracteres)'],
        ['cidade',      'VARCHAR(255)',    'NULLABLE',            'Cidade'],
        ['bairro',      'VARCHAR(255)',    'NULLABLE',            'Bairro'],
        ['rua',         'VARCHAR(255)',    'NULLABLE',            'Logradouro/rua'],
        ['numero',      'VARCHAR(255)',    'NULLABLE',            'Número do endereço'],
        ['complemento', 'VARCHAR(255)',    'NULLABLE',            'Complemento do endereço'],
        ['created_at',  'TIMESTAMP',      'NULLABLE',            'Data de criação'],
        ['updated_at',  'TIMESTAMP',      'NULLABLE',            'Data de atualização'],
    ], $w);

    // setors
    $sec->addTitle('4.3 Tabela: setors', 2);
    body($sec, 'Representa os setores físicos do prédio (salas, corredores, laboratórios) organizados por andar e bloco.');
    addStyledTable($sec, $h, [
        ['id',         'BIGINT UNSIGNED', 'PK, AUTO_INCREMENT', 'Identificador único'],
        ['nome',       'VARCHAR(255)',    'NOT NULL',            'Nome do setor (ex: Sala A, Recepção, TI)'],
        ['andar',      'VARCHAR(255)',    'NOT NULL',            'Andar (ex: Térreo, 1º Andar, 2º Andar)'],
        ['bloco',      'VARCHAR(255)',    'NOT NULL',            'Bloco do prédio: A, B, C ou D'],
        ['created_at', 'TIMESTAMP',      'NULLABLE',            'Data de criação'],
        ['updated_at', 'TIMESTAMP',      'NULLABLE',            'Data de atualização'],
    ], $w);

    // patrimonios
    $sec->addTitle('4.4 Tabela: patrimonios', 2);
    body($sec, 'Registra os bens patrimoniais (equipamentos, móveis, instalações) com seu valor, localização (setor) e imagem.');
    addStyledTable($sec, $h, [
        ['id',            'BIGINT UNSIGNED', 'PK, AUTO_INCREMENT',       'Identificador único'],
        ['codigo',        'VARCHAR(255)',    'UNIQUE, NOT NULL',          'Código de identificação do patrimônio'],
        ['valor',         'DECIMAL(10,2)',   'NULLABLE',                  'Valor monetário do bem (R$)'],
        ['data_aquisicao','DATE',           'NULLABLE',                  'Data de aquisição do bem'],
        ['imagem',        'VARCHAR(255)',    'NULLABLE',                  'Caminho da imagem no storage (pasta patrimonios-imagens)'],
        ['setor_id',      'BIGINT UNSIGNED', 'FK→setors.id, SET NULL',   'Setor onde o patrimônio está localizado'],
        ['created_at',    'TIMESTAMP',      'NULLABLE',                  'Data de criação'],
        ['updated_at',    'TIMESTAMP',      'NULLABLE',                  'Data de atualização'],
    ], $w);

    // Tabelas do sistema
    $sec->addTitle('4.5 Tabelas de Suporte do Laravel', 2);
    body($sec, 'O Laravel cria automaticamente tabelas auxiliares para gerenciar cache, sessões, filas de jobs e redefinição de senhas:');
    $hs = ['Tabela', 'Finalidade'];
    $ws = [2500, 6571];
    addStyledTable($sec, $hs, [
        ['cache',                'Armazena dados de cache da aplicação (driver: database)'],
        ['cache_locks',          'Controla locks para cache atômico'],
        ['sessions',             'Armazena sessões de usuário autenticados (driver: database)'],
        ['password_reset_tokens','Tokens temporários para redefinição de senha'],
        ['jobs',                 'Fila de trabalhos assíncronos (queue driver: database)'],
        ['job_batches',          'Controle de lotes de jobs'],
        ['failed_jobs',          'Registro de jobs que falharam na execução'],
    ], $ws);

    // Relacionamentos
    $sec->addTitle('4.6 Relacionamentos entre Entidades', 2);
    $hr = ['Modelo', 'Tipo', 'Modelo Relacionado', 'Chave', 'Comportamento'];
    $wr = [1500, 1500, 2000, 2000, 2071];
    addStyledTable($sec, $hr, [
        ['User',      'belongsTo', 'Empresa',    'users.empresa_id',      'ON DELETE SET NULL — ao excluir empresa, usuário permanece sem empresa'],
        ['Empresa',   'hasMany (implícito)', 'User', 'users.empresa_id',  'Uma empresa pode ter muitos usuários vinculados'],
        ['Patrimonio','belongsTo', 'Setor',      'patrimonios.setor_id',  'ON DELETE SET NULL — ao excluir setor, patrimônio permanece sem setor'],
        ['Setor',     'hasMany (implícito)', 'Patrimonio', 'patrimonios.setor_id', 'Um setor pode ter muitos patrimônios'],
    ], $wr);

    // ER Diagram
    $sec->addTitle('4.7 Diagrama Entidade-Relacionamento', 2);
    addImg($sec, $erPath, 'Figura 1 — Diagrama Entidade-Relacionamento do Sistema GMP');
}

function addRotas($sec): void
{
    $sec->addTitle('5. ROTAS DO SISTEMA', 1);
    body($sec, 'O sistema GMP possui dois grupos de rotas: as rotas web explícitas definidas em routes/web.php e as rotas do painel administrativo geradas automaticamente pelo Filament a partir dos Resources registrados no AdminPanelProvider.');

    $sec->addTitle('5.1 Rotas Web (routes/web.php)', 2);
    $h = ['Método', 'URI', 'Destino', 'Descrição'];
    $w = [1000, 1500, 3000, 3571];
    addStyledTable($sec, $h, [
        ['GET', '/', "view('welcome')", 'Página inicial de boas-vindas. Redireciona para /admin se autenticado.'],
    ], $w);

    $sec->addTitle('5.2 Rotas do Painel Filament (/admin)', 2);
    body($sec, 'Todas as rotas abaixo são geradas automaticamente pelo Filament com base nos Resources registrados. O prefixo é /admin (configurado no AdminPanelProvider).');
    $h2 = ['Método(s)', 'URI', 'Resource / Ação', 'Descrição'];
    $w2 = [1200, 2500, 2500, 2871];
    addStyledTable($sec, $h2, [
        ['GET',           '/admin',                          'Dashboard',                       'Painel principal com widgets de conta e informações'],
        ['GET, POST',     '/admin/login',                    'Login (Auth\\Login)',              'Página de autenticação com layout animado customizado'],
        ['POST',          '/admin/logout',                   'Logout',                          'Encerra a sessão do usuário autenticado'],
        ['GET',           '/admin/users',                    'UserResource@index',               'Lista todos os administradores'],
        ['GET, POST',     '/admin/users/create',             'UserResource@create',              'Formulário de criação de administrador'],
        ['GET, PUT/PATCH','/admin/users/{id}/edit',          'UserResource@edit',                'Formulário de edição de administrador'],
        ['GET',           '/admin/colaboradors',             'ColaboradorResource@index',         'Lista todos os colaboradores'],
        ['GET, POST',     '/admin/colaboradors/create',      'ColaboradorResource@create',        'Formulário de criação de colaborador'],
        ['GET, PUT/PATCH','/admin/colaboradors/{id}/edit',   'ColaboradorResource@edit',          'Formulário de edição de colaborador'],
        ['GET',           '/admin/empresas',                 'EmpresaResource@index',             'Lista todas as empresas'],
        ['GET, POST',     '/admin/empresas/create',          'EmpresaResource@create',            'Formulário de criação de empresa (com CEP auto-fill)'],
        ['GET, PUT/PATCH','/admin/empresas/{id}/edit',       'EmpresaResource@edit',              'Formulário de edição de empresa'],
        ['GET',           '/admin/setors',                   'SetorResource@index',               'Lista todos os setores'],
        ['GET, POST',     '/admin/setors/create',            'SetorResource@create',              'Formulário de criação de setor'],
        ['GET, PUT/PATCH','/admin/setors/{id}/edit',         'SetorResource@edit',                'Formulário de edição de setor'],
        ['GET',           '/admin/patrimonios',              'PatrimonioResource@index',          'Lista todos os patrimônios com imagem e setor'],
        ['GET, POST',     '/admin/patrimonios/create',       'PatrimonioResource@create',         'Formulário de criação de patrimônio'],
        ['GET, PUT/PATCH','/admin/patrimonios/{id}/edit',    'PatrimonioResource@edit',           'Formulário de edição de patrimônio'],
        ['GET',           '/admin/responsavels',             'ResponsavelResource@index',         'Lista todos os responsáveis'],
        ['GET, POST',     '/admin/responsavels/create',      'ResponsavelResource@create',        'Formulário de criação de responsável'],
        ['GET, PUT/PATCH','/admin/responsavels/{id}/edit',   'ResponsavelResource@edit',          'Formulário de edição de responsável'],
    ], $w2);
}

function addSeguranca($sec): void
{
    $sec->addTitle('6. SEGURANÇA', 1);

    $sec->addTitle('6.1 Autenticação', 2);
    body($sec, 'O sistema utiliza a autenticação nativa do Laravel combinada com o sistema de autenticação do Filament. Ao acessar qualquer rota protegida em /admin/*, o middleware Authenticate verifica se existe uma sessão válida. Caso contrário, o usuário é redirecionado para /admin/login.');
    body($sec, 'A classe App\\Filament\\Pages\\Auth\\Login estende a classe base de login do Filament (Filament\\Pages\\Auth\\Login) e utiliza uma view Blade customizada com layout animado (descrito na Seção 10.1).');

    $sec->addTitle('6.2 Stack de Middleware', 2);
    body($sec, 'Todas as rotas do painel /admin/* passam pelo seguinte conjunto de middlewares, configurado no AdminPanelProvider:');
    $h = ['Middleware', 'Finalidade'];
    $w = [3500, 5571];
    addStyledTable($sec, $h, [
        ['EncryptCookies',              'Criptografa todos os cookies antes de enviá-los ao navegador'],
        ['AddQueuedCookiesToResponse',  'Adiciona cookies enfileirados à resposta HTTP'],
        ['StartSession',                'Inicia a sessão PHP/Laravel'],
        ['AuthenticateSession',         'Revalida a sessão a cada requisição (proteção contra sessões roubadas)'],
        ['ShareErrorsFromSession',      'Disponibiliza erros de validação da sessão anterior nas views'],
        ['VerifyCsrfToken',             'Verifica o token CSRF em requisições POST/PUT/PATCH/DELETE'],
        ['SubstituteBindings',          'Resolve os Route Model Bindings (parâmetros como {id} → objeto Model)'],
        ['DisableBladeIconComponents',  'Otimização do Filament: desabilita processamento desnecessário de ícones Blade'],
        ['DispatchServingFilamentEvent','Dispara o evento FilamentServingEvent para extensibilidade'],
        ['Authenticate (auth)',         'Exige autenticação. Redireciona para /admin/login se não autenticado'],
    ], $w);

    $sec->addTitle('6.3 Proteção CSRF', 2);
    body($sec, 'O middleware VerifyCsrfToken protege todos os formulários do sistema contra ataques Cross-Site Request Forgery. O Filament usa Livewire para submissão de formulários, que inclui o token CSRF automaticamente via meta tag e headers HTTP.');

    $sec->addTitle('6.4 Hashing de Senhas', 2);
    body($sec, 'As senhas dos usuários nunca são armazenadas em texto plano. O modelo User aplica o cast "hashed" à coluna password, utilizando o algoritmo bcrypt com 12 rounds (padrão Laravel). Nos formulários de edição, a senha só é re-hashada e salva se o campo for preenchido (via dehydrated(fn($state) => filled($state))).');

    $sec->addTitle('6.5 Sessão e Cookies', 2);
    body($sec, 'O driver de sessão configurado é database (SESSION_DRIVER=database no .env). As sessões são armazenadas na tabela "sessions" com os campos: id, user_id, ip_address, user_agent, payload (criptografado) e last_activity. O tempo de vida da sessão é de 120 minutos (SESSION_LIFETIME=120).');

    $sec->addTitle('6.6 Login Customizado — Layout Animado', 2);
    body($sec, 'A página de login utiliza um layout split-screen customizado (filament.components.layout.login-layout). O painel esquerdo apresenta um canvas HTML5 com sistema de partículas animadas: 70 partículas brancas estáticas e 28 partículas vermelhas com efeito de pulso e glow, conectadas por linhas quando a distância entre elas é menor que 160px. O movimento segue a posição do mouse (efeito parallax). O fundo é um gradiente radial de #05050A a #12121C. O painel direito exibe o formulário Filament padrão sobre fundo branco.');
}

function addPermissoes($sec): void
{
    $sec->addTitle('7. PERMISSÕES E CONTROLE DE ACESSO', 1);
    body($sec, 'O GMP implementa controle de acesso baseado na coluna "cargo" da tabela "users". Não há Laravel Gates ou Policies implementadas — a separação de acesso é realizada pelo método getEloquentQuery() em cada Resource Filament, que filtra os registros exibidos com base no valor de "cargo". Todos os usuários autenticados têm acesso ao painel /admin.');

    $sec->addTitle('7.1 Perfis de Usuário (Cargos)', 2);
    $h = ['Cargo (valor BD)', 'Label', 'Resource de Gestão', 'Filtro Eloquent', 'Especialidades'];
    $w = [1700, 1500, 2000, 2500, 1371];
    addStyledTable($sec, $h, [
        ['admin',       'Administrador Geral', 'UserResource',       "whereIn('cargo', ['admin','diretor','professor','suporte'])", 'N/A'],
        ['diretor',     'Diretor',             'UserResource',       'Mesmo filtro acima',                                         'N/A'],
        ['professor',   'Professor',           'UserResource',       'Mesmo filtro acima',                                         'N/A'],
        ['suporte',     'Suporte/Manutenção',  'UserResource',       'Mesmo filtro acima',                                         'N/A'],
        ['colaborador', 'Colaborador',         'ColaboradorResource', "where('cargo','colaborador')",                               'hidraulica, eletrica, alvenaria, pintura, ar_condicionado, marcenaria, serralheria'],
        ['responsavel', 'Responsável',         'ResponsavelResource', "where('cargo','responsavel')",                              'N/A — identificado por NIF único'],
    ], $w);

    $sec->addTitle('7.2 Separação de Dados por Resource', 2);
    body($sec, 'Embora todos os perfis sejam armazenados na tabela "users", cada Resource apresenta apenas o subconjunto de usuários que lhe pertence. Por exemplo, a listagem em /admin/colaboradors mostra apenas usuários com cargo = "colaborador", enquanto /admin/users mostra apenas cargo IN (admin, diretor, professor, suporte). Isso garante que cada área do painel gerencie apenas seus respectivos registros.');

    $sec->addTitle('7.3 Badges de Cargo na Interface', 2);
    body($sec, 'Na tabela de Administradores, o cargo é exibido como badge colorido: admin = vermelho (danger), diretor = laranja (warning), professor = azul (info), suporte = verde (success). Isso facilita a identificação visual rápida do perfil de cada usuário na listagem.');
}

function addModulos($sec): void
{
    $sec->addTitle('8. MÓDULOS DO SISTEMA', 1);
    body($sec, 'O sistema é composto por 6 módulos (Resources Filament), cada um responsável pelo gerenciamento CRUD de uma entidade. Todos os módulos possuem páginas de Listagem, Criação e Edição, com ações de exclusão individual e em massa.');

    // ── UserResource
    $sec->addTitle('8.1 Módulo de Administradores (UserResource)', 2);
    body($sec, 'Gerencia usuários com perfis administrativos. Filtro: cargo IN (admin, diretor, professor, suporte). Rota base: /admin/users.');
    body($sec, 'Campos do formulário:');
    $h = ['Campo (name)', 'Componente Filament', 'Validação / Configuração', 'Descrição'];
    $w = [2000, 2000, 2500, 2571];
    addStyledTable($sec, $h, [
        ['foto_perfil', 'FileUpload', 'image, circular, directory: avatares', 'Foto de perfil/avatar do administrador'],
        ['name',        'TextInput',  'required, max:255',                    'Nome completo'],
        ['email',       'TextInput',  'required, email, unique (ignoreRecord)','E-mail de acesso ao sistema'],
        ['cpf',         'TextInput',  'required, unique (ignoreRecord), mask: 999.999.999-99', 'CPF do usuário'],
        ['telefone',    'TextInput',  'required, mask: (99) 99999-9999',      'Telefone ou WhatsApp'],
        ['cargo',       'Select',     'required, options: admin|diretor|professor|suporte', 'Perfil de acesso'],
        ['ativo',       'Toggle',     'default: true',                        'Ativar/desativar o usuário'],
        ['password',    'TextInput',  'required na criação; opcional na edição; revealable', 'Senha de acesso'],
    ], $w);

    // ── ColaboradorResource
    $sec->addTitle('8.2 Módulo de Colaboradores (ColaboradorResource)', 2);
    body($sec, 'Gerencia colaboradores técnicos de manutenção. Filtro: cargo = colaborador. Rota base: /admin/colaboradors.');
    body($sec, 'Campos do formulário:');
    addStyledTable($sec, $h, [
        ['foto_perfil',   'FileUpload',   'image, circular, directory: perfil-usuarios',            'Foto de perfil'],
        ['cargo',         'Hidden',       'default: colaborador',                                    'Definido automaticamente'],
        ['name',          'TextInput',    'required',                                                'Nome completo'],
        ['email',         'TextInput',    'required, email, unique (ignoreRecord)',                  'E-mail de acesso'],
        ['cpf',           'TextInput',    'required, unique (ignoreRecord), mask: 999.999.999-99',   'CPF'],
        ['telefone',      'TextInput',    'required, mask: (99) 99999-9999',                         'Telefone'],
        ['empresa_id',    'Select',       'required, relationship: empresa→nome, searchable, preload','Empresa vinculada'],
        ['especialidades','CheckboxList', '3 colunas; opções: hidraulica, eletrica, alvenaria, pintura, ar_condicionado, marcenaria, serralheria', 'Especialidades técnicas'],
        ['password',      'TextInput',    'required na criação; opcional na edição; revealable',     'Senha'],
    ], $w);

    // ── EmpresaResource
    $sec->addTitle('8.3 Módulo de Empresas (EmpresaResource)', 2);
    body($sec, 'Gerencia o cadastro de empresas contratantes. Destaque: integração com a API ViaCEP para preenchimento automático de endereço. Rota base: /admin/empresas.');
    body($sec, 'Seção "Dados Principais":');
    addStyledTable($sec, $h, [
        ['nome',     'TextInput', 'required, columnSpanFull',                         'Razão social ou nome fantasia'],
        ['cnpj',     'TextInput', 'required, unique (ignoreRecord), mask: 99.999.999/9999-99', 'CNPJ'],
        ['telefone', 'TextInput', 'mask: (99) 99999-9999',                            'Telefone'],
        ['email',    'TextInput', 'email, columnSpanFull',                            'E-mail de contato'],
    ], $w);
    body($sec, 'Seção "Endereço" (com auto-fill via ViaCEP):');
    addStyledTable($sec, $h, [
        ['cep',         'TextInput', 'mask: 99999-999; live(onBlur: true); dispara consulta ViaCEP', 'CEP (preenchimento automático)'],
        ['estado',      'TextInput', 'required, length:2, placeholder: SP',            'UF (2 letras)'],
        ['cidade',      'TextInput', 'required, columnSpan:2',                         'Cidade'],
        ['bairro',      'TextInput', 'columnSpan:2',                                   'Bairro'],
        ['rua',         'TextInput', 'columnSpan:2',                                   'Logradouro/rua'],
        ['numero',      'TextInput', 'columnSpan:1',                                   'Número'],
        ['complemento', 'TextInput', 'columnSpan:1',                                   'Complemento'],
    ], $w);

    // ── SetorResource
    $sec->addTitle('8.4 Módulo de Setores (SetorResource)', 2);
    body($sec, 'Gerencia os setores físicos do prédio (salas, laboratórios, corredores) organizados por andar e bloco. Rota base: /admin/setors.');
    addStyledTable($sec, $h, [
        ['nome',  'TextInput', 'required, placeholder: Ex: Sala A, Recepção, TI',    'Nome identificador do setor'],
        ['andar', 'TextInput', 'required, placeholder: Ex: Térreo, 2º Andar',        'Andar onde o setor está localizado'],
        ['bloco', 'Select',    'required, opções: A | B | C | D',                    'Bloco do prédio'],
    ], $w);

    // ── PatrimonioResource
    $sec->addTitle('8.5 Módulo de Patrimônios (PatrimonioResource)', 2);
    body($sec, 'Gerencia os bens patrimoniais do SENAI. Permite upload de imagem com editor integrado e vinculação ao setor. Rota base: /admin/patrimonios. Coluna "valor" é formatada como moeda BRL na listagem.');
    addStyledTable($sec, $h, [
        ['codigo',        'TextInput',  'required, unique (ignoreRecord)',                           'Código único de identificação'],
        ['data_aquisicao','DatePicker', 'format: d/m/Y',                                            'Data de aquisição do bem'],
        ['valor',         'TextInput',  'numeric, prefix: R$',                                      'Valor monetário do patrimônio'],
        ['setor_id',      'Select',     'required, relationship: setor→nome, searchable, preload',   'Setor de localização'],
        ['imagem',        'FileUpload', 'image, imageEditor, directory: patrimonios-imagens, columnSpanFull', 'Foto do patrimônio'],
    ], $w);

    // ── ResponsavelResource
    $sec->addTitle('8.6 Módulo de Responsáveis (ResponsavelResource)', 2);
    body($sec, 'Gerencia responsáveis por setores ou ordens de serviço. Campo exclusivo "nif" (Número de Identificação Funcional) diferencia este perfil dos demais. Rota base: /admin/responsavels.');
    addStyledTable($sec, $h, [
        ['foto_perfil', 'FileUpload', 'image, circular, directory: perfil-usuarios',             'Foto de perfil'],
        ['cargo',       'Hidden',    'default: responsavel',                                     'Definido automaticamente'],
        ['name',        'TextInput', 'required',                                                 'Nome completo'],
        ['email',       'TextInput', 'required, email, unique (ignoreRecord)',                   'E-mail de acesso'],
        ['cpf',         'TextInput', 'required, unique (ignoreRecord), mask: 999.999.999-99',    'CPF'],
        ['nif',         'TextInput', 'required, unique (ignoreRecord)',                          'Número de Identificação Funcional'],
        ['telefone',    'TextInput', 'required, mask: (99) 99999-9999',                         'Telefone'],
        ['empresa_id',  'Select',    'required, relationship com filtro, searchable, preload',    'Empresa vinculada'],
        ['password',    'TextInput', 'required na criação; opcional na edição',                  'Senha de acesso'],
    ], $w);
}

function addIntegracoes($sec): void
{
    $sec->addTitle('9. INTEGRAÇÕES EXTERNAS', 1);

    $sec->addTitle('9.1 API ViaCEP', 2);
    body($sec, 'A API ViaCEP é um serviço público brasileiro que fornece dados de endereço (logradouro, bairro, cidade e UF) a partir de um CEP válido. No sistema GMP, ela é utilizada no formulário de cadastro e edição de Empresas para preencher automaticamente os campos de endereço quando o usuário digita o CEP.');

    $h = ['Atributo', 'Valor'];
    $w = [2500, 6571];
    addStyledTable($sec, $h, [
        ['Endpoint',      'https://viacep.com.br/ws/{cep}/json/'],
        ['Método HTTP',   'GET'],
        ['Formato',       'JSON'],
        ['Autenticação',  'Nenhuma (API pública e gratuita)'],
        ['Trigger',       'Evento onBlur do campo CEP (live(onBlur: true))'],
        ['Cliente HTTP',  'Illuminate\\Support\\Facades\\Http (Laravel HTTP Client)'],
        ['Campos mapeados', 'uf → estado | localidade → cidade | bairro → bairro | logradouro → rua'],
    ], $w);

    $sec->addTitle('9.2 Implementação no EmpresaResource', 2);
    body($sec, 'O código abaixo (extraído de app/Filament/Resources/EmpresaResource.php) mostra a implementação completa da integração com ViaCEP:');
    codeBlock($sec, "TextInput::make('cep')\n    ->label('CEP')\n    ->mask('99999-999')\n    ->live(onBlur: true)\n    ->afterStateUpdated(function (?string \$state, Set \$set) {\n        if (blank(\$state)) return;\n\n        \$cep = preg_replace('/[^0-9]/', '', \$state);\n\n        if (strlen(\$cep) !== 8) return;\n\n        \$response = Http::get(\"https://viacep.com.br/ws/{\$cep}/json/\");\n\n        if (\$response->successful() && !isset(\$response['erro'])) {\n            \$dados = \$response->json();\n            \$set('estado', \$dados['uf']         ?? null);\n            \$set('cidade', \$dados['localidade'] ?? null);\n            \$set('bairro', \$dados['bairro']     ?? null);\n            \$set('rua',    \$dados['logradouro'] ?? null);\n        }\n    })");

    body($sec, 'Validações realizadas antes de chamar a API: (1) o campo não pode estar em branco; (2) o CEP deve ter exatamente 8 dígitos após remoção da máscara. A API retorna um campo "erro": true quando o CEP não existe — tratado na condição !isset($response["erro"]).');
}

function addFluxogramas($sec, array $imgs): void
{
    $sec->addTitle('10. FLUXOGRAMAS', 1);
    body($sec, 'Esta seção apresenta os principais fluxogramas do sistema GMP, ilustrando os processos de autenticação, consulta de CEP, operações CRUD e a hierarquia de perfis de usuário. O Diagrama Entidade-Relacionamento encontra-se na Seção 4.7.');

    $sec->addTitle('10.1 Fluxo de Autenticação (Login)', 2);
    body($sec, 'O fluxo abaixo descreve o processo completo de autenticação no sistema, desde o acesso inicial até o tratamento de credenciais inválidas.');
    addImg($sec, $imgs['login'], 'Figura 2 — Fluxo de Autenticação no Sistema GMP');

    $sec->addTitle('10.2 Fluxo de Consulta de CEP (ViaCEP)', 2);
    body($sec, 'Demonstra como o sistema realiza o preenchimento automático de endereço no cadastro de empresas ao detectar um CEP válido.');
    addImg($sec, $imgs['cep'], 'Figura 3 — Fluxo de Preenchimento Automático de Endereço via ViaCEP');

    $sec->addTitle('10.3 Fluxo CRUD Genérico', 2);
    body($sec, 'Representa o fluxo padrão de operações de criação, edição e exclusão aplicável a todos os módulos do sistema (Administradores, Colaboradores, Empresas, Setores, Patrimônios, Responsáveis).');
    addImg($sec, $imgs['crud'], 'Figura 4 — Fluxo Padrão de Operações CRUD nos Módulos');

    $sec->addTitle('10.4 Hierarquia de Perfis de Usuário', 2);
    body($sec, 'Ilustra a separação de perfis de acesso, os Resources responsáveis por cada grupo e as características exclusivas de cada cargo no sistema.');
    addImg($sec, $imgs['roles'], 'Figura 5 — Hierarquia e Separação de Perfis de Usuário');
}

function addInstalacao($sec): void
{
    $sec->addTitle('11. INSTALAÇÃO E CONFIGURAÇÃO', 1);
    body($sec, 'Esta seção descreve o processo completo de instalação e configuração do sistema GMP em ambiente de desenvolvimento local.');

    $sec->addTitle('11.1 Pré-requisitos', 2);
    bullet($sec, 'PHP 8.2 ou superior com extensões: pdo_mysql, mbstring, xml, zip, gd');
    bullet($sec, 'Composer 2.x (gerenciador de dependências PHP)');
    bullet($sec, 'Node.js 20+ e npm (para build do front-end)');
    bullet($sec, 'MySQL 8.0+ ou MariaDB 10.6+');
    bullet($sec, 'Git (para clonar o repositório)');

    $sec->addTitle('11.2 Passo a Passo', 2);

    $steps = [
        ['Clonar o repositório', "git clone <URL_DO_REPOSITORIO>\ncd Gestao-de-Manutencao-Predial/SenaiGMP"],
        ['Instalar dependências PHP', "composer install"],
        ['Copiar arquivo de configuração', "cp .env.example .env"],
        ['Gerar chave da aplicação', "php artisan key:generate"],
        ['Configurar o banco de dados no .env', "DB_CONNECTION=mysql\nDB_HOST=127.0.0.1\nDB_PORT=3306\nDB_DATABASE=senai_gmp\nDB_USERNAME=root\nDB_PASSWORD=sua_senha"],
        ['Executar as migrações', "php artisan migrate"],
        ['Instalar dependências JavaScript', "npm install"],
        ['Compilar assets (produção)', "npm run build"],
        ['Criar link simbólico de storage', "php artisan storage:link"],
        ['Criar usuário administrador (via Tinker)', "php artisan tinker\n>>> App\\Models\\User::create([\n...   'name'     => 'Administrador',\n...   'email'    => 'admin@senai.br',\n...   'password' => 'senha_aqui',\n...   'cargo'    => 'admin',\n... ]);"],
        ['Iniciar o servidor de desenvolvimento', "php artisan serve\n# ou para iniciar todos os serviços:\ncomposer run dev"],
        ['Acessar o sistema', "http://localhost:8000/admin"],
    ];

    $hn = ['Passo', 'Descrição', 'Comando'];
    $wn = [500, 2500, 6071];
    $rows = [];
    foreach ($steps as $i => [$desc, $cmd]) {
        $rows[] = [(string)($i + 1), $desc, $cmd];
    }
    addStyledTable($sec, $hn, $rows, $wn);

    $sec->addTitle('11.3 Configurações Importantes do .env', 2);
    $h = ['Variável', 'Valor Padrão (dev)', 'Descrição'];
    $w = [2500, 2000, 4571];
    addStyledTable($sec, $h, [
        ['APP_NAME',         'Laravel',       'Nome da aplicação'],
        ['APP_ENV',          'local',          'Ambiente: local | staging | production'],
        ['APP_DEBUG',        'true',           'Exibir erros detalhados (false em produção)'],
        ['APP_URL',          'http://localhost','URL base da aplicação'],
        ['APP_LOCALE',       'pt',             'Idioma da aplicação (português)'],
        ['DB_CONNECTION',    'mysql',          'Driver do banco de dados'],
        ['DB_DATABASE',      'senai_gmp',      'Nome do banco de dados'],
        ['SESSION_DRIVER',   'database',       'Armazenar sessões no banco de dados'],
        ['CACHE_STORE',      'database',       'Armazenar cache no banco de dados'],
        ['QUEUE_CONNECTION', 'database',       'Processar filas no banco de dados'],
        ['FILESYSTEM_DISK',  'local',          'Disco de armazenamento de arquivos'],
        ['MAIL_MAILER',      'log',            'Em dev: escreve e-mails no log (não envia)'],
    ], $w);
}

// ─── Construtor principal do documento ───────────────────────────────────────

function buildDocument(array $imgs): PhpWord
{
    $pw = new PhpWord();
    sty($pw);

    // Capa (seção sem margens)
    addCoverSection($pw, $imgs['cover']);

    // Sumário
    $toc = $pw->addSection(secStyle());
    $toc->addTitle('SUMÁRIO', 1);
    $toc->addTOC(['name' => 'Times New Roman', 'size' => 12], ['tabLeader' => 'dot'], 1, 3);
    $toc->addPageBreak();

    // Seção de conteúdo único
    $sec = $pw->addSection(secStyle());

    addIntroducao($sec);
    $sec->addPageBreak();
    addTecnologias($sec);
    $sec->addPageBreak();
    addArquitetura($sec);
    $sec->addPageBreak();
    addBancoDeDados($sec, $imgs['er']);
    $sec->addPageBreak();
    addRotas($sec);
    $sec->addPageBreak();
    addSeguranca($sec);
    $sec->addPageBreak();
    addPermissoes($sec);
    $sec->addPageBreak();
    addModulos($sec);
    $sec->addPageBreak();
    addIntegracoes($sec);
    $sec->addPageBreak();
    addFluxogramas($sec, $imgs);
    $sec->addPageBreak();
    addInstalacao($sec);

    return $pw;
}

// ─── Main ─────────────────────────────────────────────────────────────────────

function main(): void
{
    if (!is_dir(IMG_DIR)) mkdir(IMG_DIR, 0755, true);

    echo "Gerando imagens...\n";
    $imgs = [
        'cover' => generateCoverImage(),
        'er'    => generateERDiagram(),
        'login' => generateLoginFlow(),
        'cep'   => generateCEPFlow(),
        'crud'  => generateCRUDFlow(),
        'roles' => generateRolesHierarchy(),
    ];
    echo "  ✓ " . count($imgs) . " imagens geradas em " . IMG_DIR . "\n";

    echo "Construindo documento Word...\n";
    $pw = buildDocument($imgs);

    $outPath = __DIR__ . '/../Documentacao_GMP.docx';
    $writer  = IOFactory::createWriter($pw, 'Word2007');
    $writer->save($outPath);

    echo "  ✓ Documento salvo em: " . realpath($outPath) . "\n";
    echo "  → Abra no Word e pressione Ctrl+A → F9 para atualizar o sumário.\n";
}

main();
