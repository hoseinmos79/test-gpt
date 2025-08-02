<?php
if (!defined('ABSPATH')) {
    exit;
}

class SMSFB_Excel_Export {
    
    public function export_submissions($submissions, $form_name) {
        // Set headers for Excel download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="submissions_' . $form_name . '_' . date('Y-m-d_H-i-s') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        // Create Excel content
        $excel_content = $this->generate_excel_content($submissions, $form_name);
        
        // Output the Excel file
        echo $excel_content;
        exit;
    }
    
    private function generate_excel_content($submissions, $form_name) {
        // Excel XML structure
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
        $xml .= '<sheets><sheet name="Submissions" sheetId="1" r:id="rId1"/></sheets></workbook>';
        
        // Create the main content
        $content = $this->create_excel_main_content($submissions, $form_name);
        
        // Create the shared strings
        $shared_strings = $this->create_shared_strings($submissions);
        
        // Create the styles
        $styles = $this->create_styles();
        
        // Create the relationships
        $relationships = $this->create_relationships();
        
        // Create the ZIP file structure
        $zip = new ZipArchive();
        $temp_file = tempnam(sys_get_temp_dir(), 'excel');
        $zip->open($temp_file, ZipArchive::CREATE);
        
        // Add files to ZIP
        $zip->addFromString('[Content_Types].xml', $this->create_content_types());
        $zip->addFromString('_rels/.rels', $this->create_rels());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $relationships);
        $zip->addFromString('xl/workbook.xml', $xml);
        $zip->addFromString('xl/worksheets/sheet1.xml', $content);
        $zip->addFromString('xl/sharedStrings.xml', $shared_strings);
        $zip->addFromString('xl/styles.xml', $styles);
        
        $zip->close();
        
        // Read and return the ZIP content
        $excel_content = file_get_contents($temp_file);
        unlink($temp_file);
        
        return $excel_content;
    }
    
    private function create_excel_main_content($submissions, $form_name) {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<sheetData>';
        
        // Headers
        $headers = array('ردیف', 'نام', 'نام خانوادگی', 'شماره تماس', 'ایمیل', 'توضیح', 'استان', 'شهر', 'IP', 'تاریخ');
        $xml .= '<row r="1">';
        foreach ($headers as $index => $header) {
            $cell_ref = $this->get_cell_ref($index, 1);
            $xml .= '<c r="' . $cell_ref . '" t="s"><v>' . $index . '</v></c>';
        }
        $xml .= '</row>';
        
        // Data rows
        $row_number = 2;
        foreach ($submissions as $submission) {
            $form_data = json_decode($submission->form_data, true);
            $xml .= '<row r="' . $row_number . '">';
            
            // Row number
            $xml .= '<c r="' . $this->get_cell_ref(0, $row_number) . '"><v>' . ($row_number - 1) . '</v></c>';
            
            // Form data
            $fields = array('first_name', 'last_name', 'phone', 'email', 'description', 'province', 'city');
            foreach ($fields as $index => $field) {
                $value = isset($form_data[$field]) ? $form_data[$field] : '';
                $cell_ref = $this->get_cell_ref($index + 1, $row_number);
                $xml .= '<c r="' . $cell_ref . '"><v>' . htmlspecialchars($value) . '</v></c>';
            }
            
            // IP and date
            $xml .= '<c r="' . $this->get_cell_ref(8, $row_number) . '"><v>' . htmlspecialchars($submission->ip_address) . '</v></c>';
            $xml .= '<c r="' . $this->get_cell_ref(9, $row_number) . '"><v>' . htmlspecialchars($submission->created_at) . '</v></c>';
            
            $xml .= '</row>';
            $row_number++;
        }
        
        $xml .= '</sheetData>';
        $xml .= '</worksheet>';
        
        return $xml;
    }
    
    private function create_shared_strings($submissions) {
        $strings = array('ردیف', 'نام', 'نام خانوادگی', 'شماره تماس', 'ایمیل', 'توضیح', 'استان', 'شهر', 'IP', 'تاریخ');
        
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($strings) . '" uniqueCount="' . count($strings) . '">';
        
        foreach ($strings as $string) {
            $xml .= '<si><t>' . htmlspecialchars($string) . '</t></si>';
        }
        
        $xml .= '</sst>';
        return $xml;
    }
    
    private function create_styles() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<fonts count="1"><font><name val="Tahoma"/></font></fonts>';
        $xml .= '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>';
        $xml .= '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>';
        $xml .= '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>';
        $xml .= '<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>';
        $xml .= '</styleSheet>';
        return $xml;
    }
    
    private function create_relationships() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        $xml .= '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>';
        $xml .= '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>';
        $xml .= '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
        $xml .= '</Relationships>';
        return $xml;
    }
    
    private function create_content_types() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">';
        $xml .= '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>';
        $xml .= '<Default Extension="xml" ContentType="application/xml"/>';
        $xml .= '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
        $xml .= '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        $xml .= '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>';
        $xml .= '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        $xml .= '</Types>';
        return $xml;
    }
    
    private function create_rels() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        $xml .= '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>';
        $xml .= '</Relationships>';
        return $xml;
    }
    
    private function get_cell_ref($col, $row) {
        $letters = '';
        while ($col >= 0) {
            $letters = chr(65 + ($col % 26)) . $letters;
            $col = floor($col / 26) - 1;
        }
        return $letters . $row;
    }
}