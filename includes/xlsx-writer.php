<?php
/**
 * Simple XLSX Writer for Job Management System
 * Creates Excel files without external dependencies
 */

if (!defined('ABSPATH')) {
    exit;
}

class JMS_XLSX_Writer {
    
    private $data = array();
    private $headers = array();
    private $filename = '';
    private $status_filter = '';
    private $cv_links = array();
    
    public function __construct($filename = 'export.xlsx') {
        $this->filename = $filename;
    }
    
    public function setStatusFilter($status) {
        $this->status_filter = $status;
    }
    
    public function setHeaders($headers) {
        $this->headers = $headers;
    }
    
    public function addRow($row, $cv_link = '') {
        $this->data[] = $row;
        $this->cv_links[] = $cv_link;
    }
    
    public function download() {
        // Debug: Check if we have data
        if (empty($this->data)) {
            // If no data, create a simple CSV with error message
            $csv_content = "No data available for export\n";
            $csv_content .= "Export Date: " . date('Y-m-d H:i:s') . "\n";
            
            nocache_headers();
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . str_replace('.xlsx', '.csv', $this->filename) . '"');
            echo $csv_content;
            exit;
        }
        
        // Create temporary directory for XLSX files
        $temp_dir = sys_get_temp_dir() . '/jms_xlsx_' . uniqid();
        if (!mkdir($temp_dir)) {
            // Fallback to CSV if can't create temp directory
            $this->downloadCsv();
            return;
        }
        
        // Create XLSX structure
        $this->createXLSXStructure($temp_dir);
        
        // Create ZIP file (XLSX is essentially a ZIP)
        $zip_file = $temp_dir . '.xlsx';
        $this->createZip($temp_dir, $zip_file);
        
        // Check if file was created successfully
        if (!file_exists($zip_file) || filesize($zip_file) < 100) {
            // Cleanup and fallback to CSV
            $this->cleanup($temp_dir);
            $this->downloadCsv();
            return;
        }
        
        // Set headers and output file
        nocache_headers();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $this->filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($zip_file));
        
        // Output file
        readfile($zip_file);
        
        // Cleanup
        $this->cleanup($temp_dir);
        unlink($zip_file);
        
        exit;
    }
    
    private function downloadCsv() {
        $csv_content = '';
        
        // Add title and metadata
        $csv_content .= "Job Applications Export\n\n";
        if (!empty($this->status_filter)) {
            $csv_content .= "Job: " . ucfirst($this->status_filter) . "\n";
        }
        $csv_content .= "Export Date: " . date('Y-m-d H:i:s') . "\n";
        $csv_content .= "Total Applications: " . count($this->data) . "\n\n\n";
        
        // Add headers
        if (!empty($this->headers)) {
            $csv_content .= implode(',', array_map(array($this, 'escapeCsv'), $this->headers)) . "\n";
        }
        
        // Add data
        foreach ($this->data as $row) {
            $csv_content .= implode(',', array_map(array($this, 'escapeCsv'), $row)) . "\n";
        }
        
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . str_replace('.xlsx', '.csv', $this->filename) . '"');
        echo $csv_content;
        exit;
    }
    
    private function createXLSXStructure($temp_dir) {
        // Create directory structure
        mkdir($temp_dir . '/_rels');
        mkdir($temp_dir . '/docProps');
        mkdir($temp_dir . '/xl');
        mkdir($temp_dir . '/xl/_rels');
        mkdir($temp_dir . '/xl/worksheets');
        
        // Create [Content_Types].xml
        file_put_contents($temp_dir . '/[Content_Types].xml', $this->getContentTypesXML());
        
        // Create _rels/.rels
        file_put_contents($temp_dir . '/_rels/.rels', $this->getRelsXML());
        
        // Create docProps/app.xml
        file_put_contents($temp_dir . '/docProps/app.xml', $this->getAppXML());
        
        // Create docProps/core.xml
        file_put_contents($temp_dir . '/docProps/core.xml', $this->getCoreXML());
        
        // Create xl/workbook.xml
        file_put_contents($temp_dir . '/xl/workbook.xml', $this->getWorkbookXML());
        
        // Create xl/_rels/workbook.xml.rels
        file_put_contents($temp_dir . '/xl/_rels/workbook.xml.rels', $this->getWorkbookRelsXML());
        
        // Create xl/styles.xml
        file_put_contents($temp_dir . '/xl/styles.xml', $this->getStylesXML());
        
        // Create xl/worksheets/sheet1.xml
        file_put_contents($temp_dir . '/xl/worksheets/sheet1.xml', $this->getSheetXML());
    }
    
    private function createZip($source_dir, $zip_file) {
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($zip_file, ZipArchive::CREATE) === TRUE) {
                
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($source_dir, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );
                
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $file_path = $file->getRealPath();
                        $relative_path = substr($file_path, strlen($source_dir) + 1);
                        $relative_path = str_replace('\\', '/', $relative_path);
                        $zip->addFile($file_path, $relative_path);
                    }
                }
                
                $zip->close();
            } else {
                // If ZIP creation fails, fallback to CSV
                $this->createCsvFallback($zip_file);
            }
        } else {
            // Fallback: create a simple CSV if ZipArchive is not available
            $this->createCsvFallback($zip_file);
        }
    }
    
    private function createCsvFallback($csv_file) {
        $csv_content = '';
        
        // Add title and metadata
        $csv_content .= "Job Applications Export\n\n";
        if (!empty($this->status_filter)) {
            $csv_content .= "Job: " . ucfirst($this->status_filter) . "\n";
        }
        $csv_content .= "Export Date: " . date('Y-m-d H:i:s') . "\n";
        $csv_content .= "Total Applications: " . count($this->data) . "\n\n\n";
        
        // Add headers
        if (!empty($this->headers)) {
            $csv_content .= implode(',', array_map(array($this, 'escapeCsv'), $this->headers)) . "\n";
        }
        
        // Add data
        foreach ($this->data as $row) {
            $csv_content .= implode(',', array_map(array($this, 'escapeCsv'), $row)) . "\n";
        }
        
        file_put_contents($csv_file, $csv_content);
    }
    
    private function escapeCsv($field) {
        return '"' . str_replace('"', '""', $field) . '"';
    }
    
    private function cleanup($dir) {
        if (is_dir($dir)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            
            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            rmdir($dir);
        }
    }
    
    private function getContentTypesXML() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
    <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
    <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>';
    }
    
    private function getRelsXML() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
    <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>';
    }
    
    private function getAppXML() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
    <Application>Job Management System</Application>
    <DocSecurity>0</DocSecurity>
    <ScaleCrop>false</ScaleCrop>
    <Company>Your Company</Company>
    <LinksUpToDate>false</LinksUpToDate>
    <SharedDoc>false</SharedDoc>
    <HyperlinksChanged>false</HyperlinksChanged>
    <AppVersion>1.0</AppVersion>
</Properties>';
    }
    
    private function getCoreXML() {
        $now = date('c');
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <dc:creator>Job Management System</dc:creator>
    <dcterms:created xsi:type="dcterms:W3CDTF">' . $now . '</dcterms:created>
    <dcterms:modified xsi:type="dcterms:W3CDTF">' . $now . '</dcterms:modified>
    <dc:title>Job Applications Export</dc:title>
</cp:coreProperties>';
    }
    
    private function getWorkbookXML() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Applications" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>';
    }
    
    private function getWorkbookRelsXML() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';
    }
    
    private function getStylesXML() {
        // Note: According to the XLSX spec, fillId="1" is reserved for the gray125 pattern.
        // Using fillId="1" in cell styles makes Excel show a dotted/hatched background.
        // To avoid that, we define fills as: 0=none, 1=gray125 (reserved), 2=solid white,
        // and reference fillId="0" (none) for our cellXfs.
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <fonts count="2">
        <font>
            <sz val="11"/>
            <name val="Calibri"/>
        </font>
        <font>
            <sz val="11"/>
            <name val="Calibri"/>
            <b/>
        </font>
    </fonts>
    <fills count="3">
        <fill>
            <patternFill patternType="none"/>
        </fill>
        <fill>
            <patternFill patternType="gray125"/>
        </fill>
        <fill>
            <patternFill patternType="solid">
                <fgColor rgb="FFFFFFFF"/>
                <bgColor indexed="64"/>
            </patternFill>
        </fill>
    </fills>
    <borders count="2">
        <border>
            <left/>
            <right/>
            <top/>
            <bottom/>
        </border>
        <border>
            <left style="thin"/>
            <right style="thin"/>
            <top style="thin"/>
            <bottom style="thin"/>
        </border>
    </borders>
    <cellStyleXfs count="1">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
    </cellStyleXfs>
    <cellXfs count="3">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        <xf numFmtId="0" fontId="1" fillId="0" borderId="1" xfId="0"/>
        <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"/>
    </cellXfs>
</styleSheet>';
    }
    
    private function getSheetXML() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        
        // Sheet properties and views must come before sheetData
        $xml .= '<sheetPr filterMode="false" enableFormatConditionsCalculation="true">';
        $xml .= '<tabColor rgb="FFFFFFFF"/>';
        $xml .= '<pageSetUpPr autoPageBreaks="true" fitToPage="true"/>';
        $xml .= '</sheetPr>';
        $xml .= '<sheetViews><sheetView tabSelected="1" workbookViewId="0" showGridLines="false" showRowColHeaders="false" showRuler="false" showOutlineSymbols="false" defaultGridColor="false" rightToLeft="false" showFormulas="false" showZeros="true" view="normal" windowProtection="false" zoomScale="100" zoomScaleNormal="100" zoomScaleSheetLayoutView="100" zoomScalePageLayoutView="100"/></sheetViews>';
        $xml .= '<sheetFormatPr defaultRowHeight="15" customHeight="false" zeroHeight="false" thickTop="false" thickBottom="false"/>';
        
        $xml .= '<sheetData>';
        
        $row_num = 1;
        
        // Add title
        $xml .= '<row r="' . $row_num . '">';
        $xml .= '<c r="A' . $row_num . '" t="inlineStr"><is><t>Job Applications Export</t></is></c>';
        $xml .= '</row>';
        $row_num++;
        
        // Add empty row
        $row_num++;
        
        // Add job filter info if available
        if (!empty($this->status_filter)) {
            $xml .= '<row r="' . $row_num . '">';
            $xml .= '<c r="A' . $row_num . '" t="inlineStr"><is><t>Job: ' . $this->xmlEscape(ucfirst($this->status_filter)) . '</t></is></c>';
            $xml .= '</row>';
            $row_num++;
        }
        
        // Add export date
        $xml .= '<row r="' . $row_num . '">';
        $xml .= '<c r="A' . $row_num . '" t="inlineStr"><is><t>Export Date: ' . date('Y-m-d H:i:s') . '</t></is></c>';
        $xml .= '</row>';
        $row_num++;
        
        // Add empty row
        $row_num++;
        
        // Add total applications count
        $xml .= '<row r="' . $row_num . '">';
        $xml .= '<c r="A' . $row_num . '" t="inlineStr"><is><t>Total Applications: ' . count($this->data) . '</t></is></c>';
        $xml .= '</row>';
        $row_num++;
        
        // Add empty rows
        $row_num += 2;
        
        // Add headers
        if (!empty($this->headers)) {
            $xml .= '<row r="' . $row_num . '">';
            $col_num = 1;
            foreach ($this->headers as $header) {
                $cell_ref = $this->getCellReference($col_num, $row_num);
                $xml .= '<c r="' . $cell_ref . '" s="1" t="inlineStr"><is><t>' . $this->xmlEscape($header) . '</t></is></c>';
                $col_num++;
            }
            $xml .= '</row>';
            $row_num++;
        }
        
        // Add data rows
        foreach ($this->data as $row_data) {
            $xml .= '<row r="' . $row_num . '">';
            $col_num = 1;
            foreach ($row_data as $cell_value) {
                $cell_ref = $this->getCellReference($col_num, $row_num);
                $safe_value = $this->xmlEscape((string)$cell_value);
                
                // Special handling for CV Download column
                if ($col_num == 7 && $cell_value === 'Yes') {
                    $xml .= '<c r="' . $cell_ref . '" s="2" t="inlineStr"><is><t>Download Now</t></is></c>';
                } elseif (is_numeric($cell_value) && $cell_value != '') {
                    $xml .= '<c r="' . $cell_ref . '" s="2"><v>' . $safe_value . '</v></c>';
                } else {
                    $xml .= '<c r="' . $cell_ref . '" s="2" t="inlineStr"><is><t>' . $safe_value . '</t></is></c>';
                }
                $col_num++;
            }
            $xml .= '</row>';
            $row_num++;
        }
        
        $xml .= '</sheetData>';
        
        // Page setup elements must come after sheetData
        $xml .= '<printOptions gridLines="false" gridLinesSet="false" headings="false" horizontalCentered="false" verticalCentered="false"/>';
        $xml .= '<pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>';
        $xml .= '<pageSetup paperSize="9" orientation="portrait" fitToWidth="1" fitToHeight="1" blackAndWhite="false" draft="false" cellComments="none" useFirstPageNumber="true"/>';
        $xml .= '<headerFooter differentOddEven="false" differentFirst="false"/>';
        $xml .= '<drawing/>';
        
        $xml .= '</worksheet>';
        
        return $xml;
    }
    
    private function xmlEscape($string) {
        return htmlspecialchars($string, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
    
    private function getCellReference($col, $row) {
        $col_letter = '';
        while ($col > 0) {
            $col--;
            $col_letter = chr(65 + ($col % 26)) . $col_letter;
            $col = intval($col / 26);
        }
        return $col_letter . $row;
    }
}
