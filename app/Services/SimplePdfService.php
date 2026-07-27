<?php

namespace App\Services;

class SimplePdfService
{
    public function document(string $title, array $lines): string
    {
        $pages = array_chunk($lines, 42);
        $pages = $pages ?: [[]];
        $objects = [];
        $pageIds = [];
        $fontId = 3 + (count($pages) * 2);

        foreach ($pages as $index => $pageLines) {
            $pageId = 3 + ($index * 2);
            $contentId = $pageId + 1;
            $pageIds[] = $pageId;
            $stream = $this->stream($title, $pageLines, $index + 1, count($pages));
            $objects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 {$fontId} 0 R >> >> /Contents {$contentId} 0 R >>";
            $objects[$contentId] = '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream";
        }

        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', array_map(fn ($id) => "{$id} 0 R", $pageIds)).'] /Count '.count($pages).' >>';
        $objects[$fontId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$object}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        for ($id = 1; $id <= count($objects); $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id]);
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }

    private function stream(string $title, array $lines, int $page, int $total): string
    {
        $commands = ['BT', '/F1 22 Tf', '50 790 Td', '('.$this->escape($title).') Tj', '0 -34 Td', '/F1 11 Tf'];
        foreach ($lines as $line) {
            foreach (explode("\n", wordwrap($this->ascii((string) $line), 88)) as $wrapped) {
                $commands[] = '('.$this->escape($wrapped).') Tj';
                $commands[] = '0 -17 Td';
            }
        }
        $commands[] = 'ET';
        $commands[] = "BT /F1 9 Tf 50 28 Td (LuziCity - Pagina {$page} de {$total}) Tj ET";

        return implode("\n", $commands);
    }

    private function ascii(string $value): string
    {
        return iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    }

    private function escape(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $this->ascii($value));
    }
}
