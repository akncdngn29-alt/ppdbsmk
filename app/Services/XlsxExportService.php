<?php

namespace App\Services;

use ZipArchive;
use Symfony\Component\HttpFoundation\StreamedResponse;

class XlsxExportService
{
    /**
     * Membuat file Excel XLSX asli (OpenXML SpreadsheetML) secara native via ZipArchive.
     * Tidak membutuhkan package eksternal yang rentan inkompatibilitas PHP 8.3/Laravel 13.
     */
    public function export(array $headers, array $rows, string $filename = 'ekspor.xlsx'): StreamedResponse
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');

        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat file arsip XLSX temporer.');
        }

        // 1. [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // 2. _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        // 3. xl/_rels/workbook.xml.rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

        // 4. xl/workbook.xml
        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>'
            . '<sheet name="Data Pendaftar" sheetId="1" r:id="rId1"/>'
            . '</sheets>'
            . '</workbook>';
        $zip->addFromString('xl/workbook.xml', $workbook);

        // 5. xl/styles.xml (Font normal & Header Bold hijau toska)
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="3">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF059669"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="1">'
            . '<border><left/><right/><top/><bottom/></border>'
            . '</borders>'
            . '<cellStyleXfs count="1">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>'
            . '</cellStyleXfs>'
            . '<cellXfs count="2">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
            . '</cellXfs>'
            . '</styleSheet>';
        $zip->addFromString('xl/styles.xml', $styles);

        // 6. xl/worksheets/sheet1.xml
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>';

        $rowIdx = 1;

        // Tulis Baris Header
        if (!empty($headers)) {
            $sheetXml .= '<row r="' . $rowIdx . '">';
            $colIdx = 0;
            foreach ($headers as $headerText) {
                $cellRef = $this->getColumnLetter($colIdx) . $rowIdx;
                $escaped = htmlspecialchars((string)$headerText, ENT_XML1, 'UTF-8');
                $sheetXml .= '<c r="' . $cellRef . '" t="inlineStr" s="1"><is><t>' . $escaped . '</t></is></c>';
                $colIdx++;
            }
            $sheetXml .= '</row>';
            $rowIdx++;
        }

        // Tulis Baris Data
        foreach ($rows as $row) {
            $sheetXml .= '<row r="' . $rowIdx . '">';
            $colIdx = 0;
            foreach ($row as $val) {
                $cellRef = $this->getColumnLetter($colIdx) . $rowIdx;
                $escaped = htmlspecialchars((string)$val, ENT_XML1, 'UTF-8');
                $sheetXml .= '<c r="' . $cellRef . '" t="inlineStr" s="0"><is><t>' . $escaped . '</t></is></c>';
                $colIdx++;
            }
            $sheetXml .= '</row>';
            $rowIdx++;
        }

        $sheetXml .= '</sheetData></worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        $zip->close();

        return response()->streamDownload(function () use ($tempFile) {
            $stream = fopen($tempFile, 'rb');
            fpassthru($stream);
            fclose($stream);
            @unlink($tempFile);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function getColumnLetter(int $colIndex): string
    {
        $letter = '';
        $colIndex++;
        while ($colIndex > 0) {
            $rem = ($colIndex - 1) % 26;
            $letter = chr(65 + $rem) . $letter;
            $colIndex = intdiv($colIndex - $rem, 26);
        }
        return $letter;
    }
}

