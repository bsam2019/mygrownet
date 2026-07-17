<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Repositories\CompanyRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportController extends Controller
{
    public function __construct(
        private CompanyRepositoryInterface $companyRepository,
    ) {}

    private function getCompanyId(Request $request): int
    {
        return $request->session()->get('stockflow_company_id');
    }

    // --- Template Downloads ---

    public function downloadItemTemplate()
    {
        $headers = [['name', 'sku', 'category', 'unit', 'unit_price', 'system_quantity', 'bin_name', 'expiry_date', 'notes']];
        return $this->csvResponse('item-import-template.csv', $headers);
    }

    public function downloadSupplierTemplate()
    {
        $headers = [['name', 'contact_person', 'email', 'phone', 'address', 'payment_terms']];
        return $this->csvResponse('supplier-import-template.csv', $headers);
    }

    public function downloadBinTemplate()
    {
        $headers = [['name', 'label', 'description', 'department_name']];
        return $this->csvResponse('bin-import-template.csv', $headers);
    }

    private function csvResponse(string $filename, array $rows)
    {
        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // --- CSV Imports ---

    public function importItems(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);

        $companyId = $this->getCompanyId($request);
        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle);

        $imported = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);

            try {
                $binId = null;
                if (!empty($data['bin_name'])) {
                    $bin = DB::table('sa_bins')->where('sa_company_id', $companyId)
                        ->where(function ($q) use ($data) {
                            $q->where('name', $data['bin_name'])->orWhere('label', $data['bin_name']);
                        })->first();
                    $binId = $bin?->id;
                }

                DB::table('sa_items')->insert([
                    'sa_company_id' => $companyId,
                    'name' => $data['name'] ?? '',
                    'sku' => $data['sku'] ?? null,
                    'category' => $data['category'] ?? null,
                    'unit' => $data['unit'] ?? 'pcs',
                    'unit_price' => $data['unit_price'] ?? 0,
                    'system_quantity' => $data['system_quantity'] ?? 0,
                    'sa_bin_id' => $binId,
                    'is_expirable' => !empty($data['expiry_date']),
                    'expiry_date' => $data['expiry_date'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($imported + 2) . ": " . $e->getMessage();
            }
        }

        fclose($handle);

        return redirect()->back()->with('success', "Imported {$imported} items." . ($errors ? ' Errors: ' . implode('; ', $errors) : ''));
    }

    public function importSuppliers(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);

        $companyId = $this->getCompanyId($request);
        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle);

        $imported = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);

            try {
                DB::table('sa_suppliers')->insert([
                    'sa_company_id' => $companyId,
                    'name' => $data['name'] ?? '',
                    'contact_person' => $data['contact_person'] ?? null,
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'payment_terms' => $data['payment_terms'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($imported + 2) . ": " . $e->getMessage();
            }
        }

        fclose($handle);

        return redirect()->back()->with('success', "Imported {$imported} suppliers." . ($errors ? ' Errors: ' . implode('; ', $errors) : ''));
    }

    public function importBins(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);

        $companyId = $this->getCompanyId($request);
        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle);

        $imported = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);

            try {
                $departmentId = null;
                if (!empty($data['department_name'])) {
                    $dept = DB::table('sa_departments')->where('sa_company_id', $companyId)
                        ->where('name', $data['department_name'])->first();
                    $departmentId = $dept?->id;
                }

                DB::table('sa_bins')->insert([
                    'sa_company_id' => $companyId,
                    'name' => $data['name'] ?? '',
                    'label' => $data['label'] ?? null,
                    'description' => $data['description'] ?? null,
                    'sa_department_id' => $departmentId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($imported + 2) . ": " . $e->getMessage();
            }
        }

        fclose($handle);

        return redirect()->back()->with('success', "Imported {$imported} bins." . ($errors ? ' Errors: ' . implode('; ', $errors) : ''));
    }
}
