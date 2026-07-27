<?php

namespace App\Services;

use App\Models\NewsArticle;
use App\Models\PrintEdition;

class PrintEditionPdfService
{
    private const FORMATS = [
        'a4' => [595.28, 841.89],
        'tabloid' => [792, 1224],
        'magazine' => [612, 792],
    ];

    public function generate(PrintEdition $edition): string
    {
        $edition->loadMissing('site', 'template.adSlots', 'sections.items.article');
        [$trimWidth, $trimHeight] = self::FORMATS[$edition->pdf_format] ?? self::FORMATS['a4'];
        $bleed = ((float) $edition->bleed_mm) * 72 / 25.4;
        $pages = [];
        $firstArticle = $edition->sections->first()?->items->first()?->article;

        $pages[] = $this->coverPage($edition, $firstArticle, $trimWidth, $trimHeight, $bleed);
        foreach ($edition->sections as $section) {
            foreach ($section->items as $item) {
                $pages[] = $this->articlePage($edition, $section->name, $item->article, $trimWidth, $trimHeight, $bleed);
            }
        }
        if (filled($edition->template?->credits)) {
            $pages[] = $this->creditsPage($edition, $trimWidth, $trimHeight, $bleed);
        }

        return $this->compile($pages, $trimWidth, $trimHeight, $bleed, (bool) $edition->template?->show_page_numbers);
    }

    public function review(PrintEdition $edition): array
    {
        $edition->loadMissing('template', 'sections.items.article');
        $warnings = [];
        if (! $edition->template) {
            $warnings[] = ['level' => 'error', 'message' => 'Nenhum template foi selecionado.'];
        }
        if ($edition->sections->isEmpty()) {
            $warnings[] = ['level' => 'error', 'message' => 'A edição não possui seções ou notícias.'];
        }

        $columns = max(1, min(4, (int) ($edition->template?->internal_columns ?? 3)));
        $charactersPerPage = match ($columns) {
            1 => 5200,
            2 => 6200,
            3 => 7000,
            default => 7600,
        };
        foreach ($edition->sections as $section) {
            foreach ($section->items as $item) {
                $article = $item->article;
                $length = mb_strlen(trim(strip_tags(html_entity_decode((string) $article->body))));
                if ($length > $charactersPerPage) {
                    $warnings[] = [
                        'level' => 'warning',
                        'article_id' => $article->id,
                        'article_title' => $article->title,
                        'message' => 'Texto excedente: aproximadamente '.($length - $charactersPerPage).' caracteres não cabem na página.',
                    ];
                }
                if ($edition->high_resolution_images && $article->cover_image_path) {
                    $dimensions = $this->imageDimensions($article);
                    if ($dimensions && ($dimensions[0] < 1200 || $dimensions[1] < 800)) {
                        $warnings[] = [
                            'level' => 'warning',
                            'article_id' => $article->id,
                            'article_title' => $article->title,
                            'message' => "Imagem abaixo da resolução recomendada: {$dimensions[0]} × {$dimensions[1]} px.",
                        ];
                    }
                }
            }
        }
        $articlePages = $edition->sections->sum(fn ($section) => $section->items->count());
        $pageCount = 1 + $articlePages + (filled($edition->template?->credits) ? 1 : 0);

        return [
            'page_count' => $pageCount,
            'warnings' => $warnings,
            'has_errors' => collect($warnings)->contains('level', 'error'),
            'has_warnings' => collect($warnings)->contains('level', 'warning'),
        ];
    }

    private function coverPage(PrintEdition $edition, ?NewsArticle $article, float $width, float $height, float $bleed): array
    {
        $commands = [
            $this->fill(0, 0, $width + 2 * $bleed, $height + 2 * $bleed, '0.025 0.16 0.29'),
            $this->text($edition->site?->name ?? 'LuziCity', 42, 42 + $bleed, $height - 72 + $bleed, true, '1 1 1'),
            $this->text($edition->title, 20, 44 + $bleed, $height - 108 + $bleed, false, '0.72 0.88 1'),
            $this->text($edition->edition_date->format('d/m/Y'), 11, 45 + $bleed, $height - 132 + $bleed, false, '1 1 1'),
        ];
        $images = [];
        $image = $article ? $this->image($article, $edition->high_resolution_images ? 95 : 82) : null;
        if ($image) {
            $images[] = $image + ['x' => 44 + $bleed, 'y' => $height * .40 + $bleed, 'w' => $width - 88, 'h' => $height * .34];
        }
        if ($article) {
            $y = $height * .32 + $bleed;
            foreach ($this->wrap($article->title, 60) as $line) {
                $commands[] = $this->text($line, 24, 44 + $bleed, $y, true, '1 1 1');
                $y -= 29;
            }
            $commands[] = $this->text($this->excerpt($article, 120), 11, 45 + $bleed, $y - 8, false, '0.88 0.94 1');
        }
        $commands = [...$commands, ...$this->adSlotCommands($edition, 'cover', $width, $height, $bleed)];

        return compact('commands', 'images');
    }

    private function articlePage(PrintEdition $edition, string $section, NewsArticle $article, float $width, float $height, float $bleed): array
    {
        $margin = 42 + $bleed;
        $commands = [
            $this->text(mb_strtoupper($section), 10, $margin, $height - 48 + $bleed, true, '0 0.38 0.7'),
        ];
        $y = $height - 82 + $bleed;
        foreach ($this->wrap($article->title, 55) as $line) {
            $commands[] = $this->text($line, 23, $margin, $y, true);
            $y -= 28;
        }
        if ($article->subtitle) {
            foreach ($this->wrap($article->subtitle, 90) as $line) {
                $commands[] = $this->text($line, 11, $margin, $y - 4, false, '0.25 0.3 0.35');
                $y -= 15;
            }
        }

        $images = [];
        if ($image = $this->image($article, $edition->high_resolution_images ? 95 : 82)) {
            $imageHeight = min(230, $height * .28);
            $images[] = $image + ['x' => $margin, 'y' => $y - $imageHeight - 12, 'w' => $width - 84, 'h' => $imageHeight];
            $y -= $imageHeight + 28;
        }

        $columns = max(1, min(4, (int) ($edition->template?->internal_columns ?? 3)));
        $gap = 14;
        $columnWidth = ($width - 84 - (($columns - 1) * $gap)) / $columns;
        $fontSize = $columns >= 4 ? 8 : 9;
        $lineHeight = $fontSize + 3;
        $lines = $this->wrap($article->body, max(24, (int) ($columnWidth / ($fontSize * .52))));
        $linesPerColumn = max(1, (int) (($y - 58 - $bleed) / $lineHeight));
        foreach (array_slice($lines, 0, $linesPerColumn * $columns) as $index => $line) {
            $column = intdiv($index, $linesPerColumn);
            $row = $index % $linesPerColumn;
            $commands[] = $this->text($line, $fontSize, $margin + $column * ($columnWidth + $gap), $y - $row * $lineHeight);
        }
        $commands = [...$commands, ...$this->adSlotCommands($edition, 'internal', $width, $height, $bleed)];

        return compact('commands', 'images');
    }

    private function creditsPage(PrintEdition $edition, float $width, float $height, float $bleed): array
    {
        $commands = [$this->text('EXPEDIENTE E CREDITOS', 24, 52 + $bleed, $height - 72 + $bleed, true, '0 0.38 0.7')];
        $y = $height - 112 + $bleed;
        foreach ($this->wrap($edition->template->credits, 88) as $line) {
            $commands[] = $this->text($line, 11, 52 + $bleed, $y);
            $y -= 16;
        }

        return ['commands' => $commands, 'images' => []];
    }

    private function compile(array $pages, float $trimWidth, float $trimHeight, float $bleed, bool $pageNumbers): string
    {
        $objects = [1 => '', 2 => ''];
        $pageIds = [];
        $fontRegular = 3;
        $fontBold = 4;
        $objects[$fontRegular] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[$fontBold] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';
        $nextId = 5;

        foreach ($pages as $pageIndex => $page) {
            $pageId = $nextId++;
            $contentId = $nextId++;
            $pageIds[] = $pageId;
            $xObjects = [];
            $imageDraw = [];
            foreach ($page['images'] as $index => $image) {
                $imageId = $nextId++;
                $name = 'Im'.($index + 1);
                $xObjects[] = "/{$name} {$imageId} 0 R";
                $objects[$imageId] = "<< /Type /XObject /Subtype /Image /Width {$image['pixel_width']} /Height {$image['pixel_height']} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ".strlen($image['data'])." >>\nstream\n{$image['data']}\nendstream";
                $imageDraw[] = "q {$image['w']} 0 0 {$image['h']} {$image['x']} {$image['y']} cm /{$name} Do Q";
            }
            $commands = [...$page['commands'], ...$imageDraw, ...$this->cropMarks($trimWidth, $trimHeight, $bleed)];
            if ($pageNumbers) {
                $commands[] = $this->text((string) ($pageIndex + 1), 8, $trimWidth / 2 + $bleed, 20 + $bleed, false, '0.35 0.35 0.35');
            }
            $stream = implode("\n", $commands);
            $mediaWidth = $trimWidth + 2 * $bleed;
            $mediaHeight = $trimHeight + 2 * $bleed;
            $resources = "/Font << /F1 {$fontRegular} 0 R /F2 {$fontBold} 0 R >>";
            if ($xObjects) {
                $resources .= ' /XObject << '.implode(' ', $xObjects).' >>';
            }
            $objects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$mediaWidth} {$mediaHeight}] /TrimBox [{$bleed} {$bleed} ".($bleed + $trimWidth).' '.($bleed + $trimHeight)."] /Resources << {$resources} >> /Contents {$contentId} 0 R >>";
            $objects[$contentId] = "<< /Length ".strlen($stream)." >>\nstream\n{$stream}\nendstream";
        }
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', array_map(fn ($id) => "{$id} 0 R", $pageIds)).'] /Count '.count($pageIds).' >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$object}\nendobj\n";
        }
        $xref = strlen($pdf);
        $maxId = max(array_keys($objects));
        $pdf .= "xref\n0 ".($maxId + 1)."\n0000000000 65535 f \n";
        for ($id = 1; $id <= $maxId; $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id] ?? 0);
        }

        return $pdf."trailer\n<< /Size ".($maxId + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }

    private function image(NewsArticle $article, int $quality): ?array
    {
        if (! $article->cover_image_path || ! function_exists('imagecreatefromstring')) {
            return null;
        }
        $path = $this->imagePath($article);
        if (! $path || ! $source = @imagecreatefromstring((string) file_get_contents($path))) {
            return null;
        }
        $width = imagesx($source);
        $height = imagesy($source);
        ob_start();
        imagejpeg($source, null, $quality);
        $data = (string) ob_get_clean();
        imagedestroy($source);

        return ['data' => $data, 'pixel_width' => $width, 'pixel_height' => $height];
    }

    private function imageDimensions(NewsArticle $article): ?array
    {
        $path = $this->imagePath($article);
        $dimensions = $path ? @getimagesize($path) : false;

        return $dimensions ? [$dimensions[0], $dimensions[1]] : null;
    }

    private function imagePath(NewsArticle $article): ?string
    {
        if (! $article->cover_image_path) {
            return null;
        }
        $relative = ltrim(parse_url($article->cover_image_path, PHP_URL_PATH) ?: '', '/');

        return collect([
            public_path($relative),
            storage_path('app/public/'.preg_replace('#^storage/#', '', $relative)),
        ])->first(fn ($candidate) => is_file($candidate));
    }

    private function adSlotCommands(PrintEdition $edition, string $pageType, float $width, float $height, float $bleed): array
    {
        $commands = [];
        foreach ($edition->template?->adSlots->where('page_type', $pageType) ?? [] as $slot) {
            $slotWidth = match ($slot->size) {'full' => $width - 84, 'half' => ($width - 98) / 2, 'quarter' => ($width - 126) / 4, default => $width - 84};
            $slotHeight = match ($slot->size) {'full' => $height - 120, 'half' => $height * .42, 'quarter' => $height * .2, default => 45};
            $x = 42 + $bleed;
            $y = match ($slot->placement) {'top' => $height - $slotHeight - 42 + $bleed, 'sidebar' => 70 + $bleed, default => 36 + $bleed};
            $commands[] = "q 0.55 G 0.6 w [3 3] 0 d {$x} {$y} {$slotWidth} {$slotHeight} re S Q";
            $commands[] = $this->text('PUBLICIDADE - '.$slot->name, 7, $x + 5, $y + 6, false, '0.4 0.4 0.4');
        }

        return $commands;
    }

    private function cropMarks(float $width, float $height, float $bleed): array
    {
        if ($bleed <= 0) {
            return [];
        }
        $b = $bleed;
        $right = $b + $width;
        $top = $b + $height;

        return ["q 0 G 0.25 w 0 {$b} ".max(0, $b - 2)." {$b} m {$b} {$b} l S {$right} 0 m {$right} {$b} l S 0 {$top} m {$b} {$top} l S {$right} {$top} m ".($right + $b)." {$top} l S Q"];
    }

    private function fill(float $x, float $y, float $w, float $h, string $color): string
    {
        return "q {$color} rg {$x} {$y} {$w} {$h} re f Q";
    }

    private function text(string $value, float $size, float $x, float $y, bool $bold = false, string $color = '0.08 0.1 0.12'): string
    {
        return "BT {$color} rg /".($bold ? 'F2' : 'F1')." {$size} Tf {$x} {$y} Td (".$this->escape($value).') Tj ET';
    }

    private function wrap(?string $value, int $length): array
    {
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode((string) $value))));

        return explode("\n", wordwrap($this->ascii($plain), $length));
    }

    private function excerpt(NewsArticle $article, int $length): string
    {
        $text = $article->excerpt ?: $article->subtitle ?: strip_tags($article->body);

        return mb_strimwidth($this->ascii($text), 0, $length, '...');
    }

    private function ascii(string $value): string
    {
        $value = strtr($value, [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'ç' => 'c', 'Ç' => 'C', 'ñ' => 'n', 'Ñ' => 'N',
            '–' => '-', '—' => '-', '“' => '"', '”' => '"', '’' => "'",
        ]);

        return iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    }

    private function escape(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $this->ascii($value));
    }
}
