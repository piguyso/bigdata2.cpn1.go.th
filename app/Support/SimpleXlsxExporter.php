<?php

namespace App\Support;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SimpleXlsxExporter
{
    public static function download(string $filename, array $headers, array $rows): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx_');

        if ($path === false) {
            abort(500, 'ไม่สามารถสร้างไฟล์ชั่วคราวสำหรับส่งออกข้อมูลได้');
        }

        $xlsxPath = $path.'.xlsx';
        @rename($path, $xlsxPath);

        self::write($xlsxPath, $headers, $rows);

        return response()->download(
            $xlsxPath,
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    public static function write(string $path, array $headers, array $rows): void
    {
        $timestamp = (new DateTimeImmutable())->format('Y-m-d\TH:i:s\Z');
        $files = [
            '[Content_Types].xml' => self::contentTypesXml(),
            '_rels/.rels' => self::rootRelsXml(),
            'docProps/app.xml' => self::appPropsXml(),
            'docProps/core.xml' => self::corePropsXml($timestamp),
            'xl/workbook.xml' => self::workbookXml(),
            'xl/_rels/workbook.xml.rels' => self::workbookRelsXml(),
            'xl/styles.xml' => self::stylesXml(),
            'xl/worksheets/sheet1.xml' => self::sheetXml($headers, $rows),
        ];

        self::writeZip($path, $files);
    }

    private static function contentTypesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
    <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
    <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>
XML;
    }

    private static function rootRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
    <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
XML;
    }

    private static function appPropsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
    <Application>Codex</Application>
</Properties>
XML;
    }

    private static function corePropsXml(string $timestamp): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <dc:creator>Codex</dc:creator>
    <cp:lastModifiedBy>Codex</cp:lastModifiedBy>
    <dcterms:created xsi:type="dcterms:W3CDTF">{$timestamp}</dcterms:created>
    <dcterms:modified xsi:type="dcterms:W3CDTF">{$timestamp}</dcterms:modified>
</cp:coreProperties>
XML;
    }

    private static function workbookXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Schools" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>
XML;
    }

    private static function workbookRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML;
    }

    private static function stylesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <fonts count="2">
        <font><sz val="11"/><name val="Calibri"/></font>
        <font><b/><sz val="11"/><name val="Calibri"/></font>
    </fonts>
    <fills count="2">
        <fill><patternFill patternType="none"/></fill>
        <fill><patternFill patternType="gray125"/></fill>
    </fills>
    <borders count="1">
        <border><left/><right/><top/><bottom/><diagonal/></border>
    </borders>
    <cellStyleXfs count="1">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
    </cellStyleXfs>
    <cellXfs count="2">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        <xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>
    </cellXfs>
    <cellStyles count="1">
        <cellStyle name="Normal" xfId="0" builtinId="0"/>
    </cellStyles>
</styleSheet>
XML;
    }

    private static function sheetXml(array $headers, array $rows): string
    {
        $xmlRows = [];
        $xmlRows[] = self::rowXml(1, $headers, true);

        foreach ($rows as $index => $row) {
            $xmlRows[] = self::rowXml($index + 2, $row, false);
        }

        $dimension = 'A1:'.self::columnName(max(count($headers), 1)).max(count($rows) + 1, 1);
        $sheetData = implode('', $xmlRows);

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <dimension ref="{$dimension}"/>
    <sheetViews>
        <sheetView workbookViewId="0"/>
    </sheetViews>
    <sheetFormatPr defaultRowHeight="15"/>
    <sheetData>{$sheetData}</sheetData>
</worksheet>
XML;
    }

    private static function rowXml(int $rowNumber, array $values, bool $header): string
    {
        $cells = [];

        foreach (array_values($values) as $index => $value) {
            $cellRef = self::columnName($index + 1).$rowNumber;
            $style = $header ? ' s="1"' : '';
            $escaped = self::escape((string) $value);
            $cells[] = '<c r="'.$cellRef.'" t="inlineStr"'.$style.'><is><t>'.$escaped.'</t></is></c>';
        }

        return '<row r="'.$rowNumber.'">'.implode('', $cells).'</row>';
    }

    private static function columnName(int $index): string
    {
        $name = '';

        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)).$name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    private static function escape(string $value): string
    {
        $clean = preg_replace('/[^\P{C}\t\n\r]/u', '', $value) ?? '';

        return htmlspecialchars($clean, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private static function writeZip(string $path, array $files): void
    {
        $handle = fopen($path, 'wb');

        if ($handle === false) {
            abort(500, 'ไม่สามารถสร้างไฟล์ Excel ได้');
        }

        $centralDirectory = '';
        $offset = 0;
        [$dosTime, $dosDate] = self::dosDateTime();

        foreach ($files as $name => $content) {
            $name = str_replace('\\', '/', $name);
            $data = (string) $content;
            $crc = self::unsignedCrc32($data);
            $size = strlen($data);
            $nameLength = strlen($name);

            $localHeader = pack(
                'VvvvvvVVVvv',
                0x04034b50,
                20,
                0,
                0,
                $dosTime,
                $dosDate,
                $crc,
                $size,
                $size,
                $nameLength,
                0
            ).$name;

            fwrite($handle, $localHeader.$data);

            $centralDirectory .= pack(
                'VvvvvvvVVVvvvvvVV',
                0x02014b50,
                20,
                20,
                0,
                0,
                $dosTime,
                $dosDate,
                $crc,
                $size,
                $size,
                $nameLength,
                0,
                0,
                0,
                0,
                0,
                $offset
            ).$name;

            $offset += strlen($localHeader) + $size;
        }

        $centralOffset = $offset;
        $centralSize = strlen($centralDirectory);
        fwrite($handle, $centralDirectory);
        fwrite($handle, pack(
            'VvvvvVVv',
            0x06054b50,
            0,
            0,
            count($files),
            count($files),
            $centralSize,
            $centralOffset,
            0
        ));
        fclose($handle);
    }

    private static function unsignedCrc32(string $data): int
    {
        return (int) sprintf('%u', crc32($data));
    }

    private static function dosDateTime(): array
    {
        $time = getdate();
        $dosTime = (($time['hours'] ?? 0) << 11)
            | (($time['minutes'] ?? 0) << 5)
            | (int) floor(($time['seconds'] ?? 0) / 2);
        $dosDate = ((($time['year'] ?? 1980) - 1980) << 9)
            | (($time['mon'] ?? 1) << 5)
            | ($time['mday'] ?? 1);

        return [$dosTime, $dosDate];
    }
}
