<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class LeadImportService
{
    /**
     * Import leads from Excel file
     */
    public function import($filePath, $userId)
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        // Remove header row
        $header = array_shift($rows);
        
        $imported = 0;
        $failed = 0;
        $errors = [];
        
        foreach ($rows as $index => $row) {
            $lineNumber = $index + 2; // +2 because index starts at 0 and we removed header
            
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }
            
            try {
                $data = $this->mapRowToData($row);
                
                // Validate data
                $validator = Validator::make($data, [
                    'name' => 'required|string|max:255',
                    'phone' => 'required|string',
                    'email' => 'nullable|email',
                    'company' => 'nullable|string',
                    'address' => 'nullable|string',
                    'notes' => 'nullable|string',
                ]);
                
                if ($validator->fails()) {
                    $failed++;
                    $errors[] = "Baris {$lineNumber}: " . implode(', ', $validator->errors()->all());
                    continue;
                }
                
                // Get or create default status (new_lead)
                $defaultStatus = \App\Models\LeadStatus::where('name', 'new_lead')->first();
                
                Lead::create([
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                    'company' => $data['company'],
                    'address' => $data['address'],
                    'notes' => $data['notes'],
                    'status_id' => $defaultStatus->id,
                    'assigned_to' => $userId,
                    'last_activity_at' => now(),
                ]);
                
                $imported++;
                
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Baris {$lineNumber}: " . $e->getMessage();
            }
        }
        
        return [
            'success' => true,
            'imported' => $imported,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * Map Excel row to lead data
     * Expected columns: Name, Phone, Email, Company, Address, Notes
     */
    private function mapRowToData($row)
    {
        return [
            'name' => $row[0] ?? '',
            'phone' => $row[1] ?? '',
            'email' => $row[2] ?? '',
            'company' => $row[3] ?? '',
            'address' => $row[4] ?? '',
            'notes' => $row[5] ?? '',
        ];
    }

    /**
     * Generate Excel template for import
     */
    public function generateTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $headers = ['Nama', 'Telepon', 'Email', 'Perusahaan', 'Alamat', 'Catatan'];
        $sheet->fromArray($headers, NULL, 'A1');
        
        // Add sample data
        $sampleData = [
            ['John Doe', '081234567890', 'john@example.com', 'PT Example', 'Jakarta', 'Prospek potensial'],
            ['Jane Smith', '081234567891', 'jane@example.com', 'CV Test', 'Bandung', ''],
        ];
        $sheet->fromArray($sampleData, NULL, 'A2');
        
        // Style header
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()
              ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB('FFE2E8F0');
        
        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        
        $filename = 'template-import-leads.xlsx';
        $tempPath = storage_path('app/temp/' . $filename);
        
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }
        
        $writer->save($tempPath);
        
        return $tempPath;
    }
}
