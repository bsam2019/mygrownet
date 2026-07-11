<?php

namespace App\Console\Commands;

use App\Models\StockAudit\Bin;
use App\Models\StockAudit\Company;
use App\Models\StockAudit\Item;
use Illuminate\Console\Command;

class StockAuditImportSample extends Command
{
    protected $signature = 'stock-audit:import-sample
        {company? : Company ID or name (defaults to first active company)}
    ';

    protected $description = 'Import representative sample items from the Taradasi Dental Clinic inventory into the Stock Audit module';

    public function handle(): int
    {
        $company = $this->argument('company')
            ? (is_numeric($this->argument('company'))
                ? Company::find($this->argument('company'))
                : Company::where('name', $this->argument('company'))->first())
            : Company::where('status', 'active')->first();

        if (!$company) {
            $this->error('No active company found. Run the StockAuditSeeder first.');
            return Command::FAILURE;
        }

        $existingCount = Item::where('sa_company_id', $company->id)->count();
        if ($existingCount > 50) {
            if (!$this->confirm("Company '{$company->name}' already has {$existingCount} items. Add more?")) {
                return Command::SUCCESS;
            }
        }

        $imported = 0;

        // Bin lookup helper
        $bin = fn($name) => Bin::where('sa_company_id', $company->id)->where('name', $name)->first();

        // --- BIN 1: Preventives ---
        $items = [
            ['bin' => 'Bin 1', 'name' => 'Promint Prophylaxis Paste 100g', 'price' => 165.00, 'qty' => 18, 'unit' => 'piece'],
            ['bin' => 'Bin 1', 'name' => 'Prophy Cup 10 pcs/pk, China', 'price' => 100.00, 'qty' => 4.5, 'unit' => 'pack'],
            ['bin' => 'Bin 1', 'name' => 'Fluoride Foam Trays disposable 50 pcs/pk', 'price' => 250.00, 'qty' => 3, 'unit' => 'pack'],
            ['bin' => 'Bin 1', 'name' => 'Andolex Oral Rinse 200mls', 'price' => 280.00, 'qty' => 9, 'unit' => 'bottle', 'expiry' => '2026-06-20'],
            ['bin' => 'Bin 1', 'name' => 'Holly Hexidine 100mls', 'price' => 170.00, 'qty' => 6, 'unit' => 'bottle'],
            ['bin' => 'Bin 1', 'name' => 'Desensitizing Gel 40g', 'price' => 120.00, 'qty' => 6, 'unit' => 'tube'],
            ['bin' => 'Bin 1', 'name' => 'Whitening Kit For One Patient', 'price' => 1300.00, 'qty' => 4, 'unit' => 'kit'],
            ['bin' => 'Bin 1', 'name' => 'Polishing Brushes 10pcs/pk', 'price' => 120.00, 'qty' => 3, 'unit' => 'pack'],
            ['bin' => 'Bin 1', 'name' => 'Polishing Discs 80pcs', 'price' => 675.00, 'qty' => 2, 'unit' => 'pack'],
        ];
        $imported += $this->insertItems($company, $bin, $items);

        // --- BIN 2: Dental Instruments ---
        $items = [
            ['bin' => 'Bin 2', 'name' => 'Mouth Mirror Plain with handle D 24mm Pakistan', 'price' => 100.00, 'qty' => 8],
            ['bin' => 'Bin 2', 'name' => 'Mirror handle', 'price' => 50.00, 'qty' => 24],
            ['bin' => 'Bin 2', 'name' => 'Tissue Tweezer Forcep', 'price' => 145.00, 'qty' => 4],
            ['bin' => 'Bin 2', 'name' => 'Periodontal Probe Goldman Fox', 'price' => 75.00, 'qty' => 19],
            ['bin' => 'Bin 2', 'name' => 'Filling Instrument Mortenson Plugger Double End', 'price' => 75.00, 'qty' => 26],
            ['bin' => 'Bin 2', 'name' => 'Filling Instrument Ladmore Burnisher Double End', 'price' => 75.00, 'qty' => 17],
            ['bin' => 'Bin 2', 'name' => 'Extracting Forceps Upper Incisors English Pattern', 'price' => 580.00, 'qty' => 14],
            ['bin' => 'Bin 2', 'name' => 'Extracting Forceps Upper Molar English Pattern', 'price' => 580.00, 'qty' => 21],
            ['bin' => 'Bin 2', 'name' => 'Extracting Forceps Lower Molar English Pattern', 'price' => 580.00, 'qty' => 10],
            ['bin' => 'Bin 2', 'name' => 'Elevator Coupland 2mm', 'price' => 360.00, 'qty' => 27],
            ['bin' => 'Bin 2', 'name' => 'Elevator Bein Straight', 'price' => 360.00, 'qty' => 5],
            ['bin' => 'Bin 2', 'name' => 'Elevator Cryer Right', 'price' => 360.00, 'qty' => 15],
            ['bin' => 'Bin 2', 'name' => 'Elevator Cryer Left', 'price' => 360.00, 'qty' => 15],
            ['bin' => 'Bin 2', 'name' => 'Needle Holder', 'price' => 255.00, 'qty' => 5],
            ['bin' => 'Bin 2', 'name' => 'Bone Rongeur Blumenthal 155mm', 'price' => 1080.00, 'qty' => 2],
            ['bin' => 'Bin 2', 'name' => 'Mouth Gag Children 110mm', 'price' => 810.00, 'qty' => 5],
            ['bin' => 'Bin 2', 'name' => 'Mouth Gag Adult 140mm', 'price' => 810.00, 'qty' => 4],
            ['bin' => 'Bin 2', 'name' => 'Tongue Depressor', 'price' => 15.00, 'qty' => 168],
            ['bin' => 'Bin 2', 'name' => 'Aspirating Syringe', 'price' => 350.00, 'qty' => 12],
            ['bin' => 'Bin 2', 'name' => 'Crown Remover', 'price' => 1500.00, 'qty' => 5],
            ['bin' => 'Bin 2', 'name' => 'Cheek Retractor Large', 'price' => 25.00, 'qty' => 79],
        ];
        $imported += $this->insertItems($company, $bin, $items);

        // --- BIN 3: Disposable Instruments ---
        $items = [
            ['bin' => 'Bin 3', 'name' => 'Disposable Dental Needles 27G*31mm Long 100 pcs/box', 'price' => 300.00, 'qty' => 10],
            ['bin' => 'Bin 3', 'name' => 'Disposable Dental Needles 27G*25mm Medium 100 pcs/box', 'price' => 300.00, 'qty' => 6],
            ['bin' => 'Bin 3', 'name' => 'Disposable Dental Needles 30G*13mm Short 100 pcs/box', 'price' => 300.00, 'qty' => 7],
            ['bin' => 'Bin 3', 'name' => 'Micro Applicator Cylinder 100 pcs/tub', 'price' => 200.00, 'qty' => 5],
            ['bin' => 'Bin 3', 'name' => 'Applicator Tips 100pc/Bottle Fine Yellow', 'price' => 80.00, 'qty' => 2],
        ];
        $imported += $this->insertItems($company, $bin, $items);

        // --- BIN 4: Burs ---
        $items = [
            ['bin' => 'Bin 4', 'name' => 'Diamond Bur FG 10 pcs China BR-49', 'price' => 200.00, 'qty' => 8],
            ['bin' => 'Bin 4', 'name' => 'Diamond Bur FG 10 pcs China BR-L46', 'price' => 200.00, 'qty' => 9],
            ['bin' => 'Bin 4', 'name' => 'Diamond Bur FG 10 pcs China SI-47 Inverted Cone Medium', 'price' => 200.00, 'qty' => 15],
            ['bin' => 'Bin 4', 'name' => 'Diamond Bur FG 10 pcs China SI-45 Inverted Cone Small', 'price' => 200.00, 'qty' => 16],
            ['bin' => 'Bin 4', 'name' => 'Carbide Bur Surgical FG Zekrya 28mm 5 pc/box', 'price' => 600.00, 'qty' => 5],
            ['bin' => 'Bin 4', 'name' => 'Carbide Cutters For Lab 10pcs/box', 'price' => 350.00, 'qty' => 74],
            ['bin' => 'Bin 4', 'name' => 'Diamond Bur FG 1112D 12pcs/box Taboom', 'price' => 1200.00, 'qty' => 7],
            ['bin' => 'Bin 4', 'name' => 'Composite Polishing Kit For Low Speed RA0109', 'price' => 390.00, 'qty' => 4],
        ];
        $imported += $this->insertItems($company, $bin, $items);

        // --- BIN 7: Etchant and Bond ---
        $items = [
            ['bin' => 'Bin 7', 'name' => 'Dengo Etchant Syringe 3g', 'price' => 100.00, 'qty' => 4],
            ['bin' => 'Bin 7', 'name' => 'ConnectED Self Etch L/C Bond 6ml', 'price' => 3500.00, 'qty' => 2, 'expiry' => '2026-07-20'],
            ['bin' => 'Bin 7', 'name' => 'Denbond TE', 'price' => 450.00, 'qty' => 1],
            ['bin' => 'Bin 7', 'name' => 'Denbond SE', 'price' => 520.00, 'qty' => 18],
        ];
        $imported += $this->insertItems($company, $bin, $items);

        // --- BIN 8: Composites ---
        $items = [
            ['bin' => 'Bin 8', 'name' => 'Nanotech 7 Syringe Kit (Shades A1–UO, Debond)', 'price' => 2420.00, 'qty' => 3],
            ['bin' => 'Bin 8', 'name' => 'Supreme 7 Syringe Kit 4g (Shades A1–Opaque)', 'price' => 2235.00, 'qty' => 9],
            ['bin' => 'Bin 8', 'name' => 'Dentogyl 12g', 'price' => 560.00, 'qty' => 2],
            ['bin' => 'Bin 8', 'name' => 'RestorePLUS Syringe 4g A1', 'price' => 950.00, 'qty' => 8, 'expiry' => '2026-07-25'],
            ['bin' => 'Bin 8', 'name' => 'RestorePLUS Syringe 4g A2', 'price' => 950.00, 'qty' => 7, 'expiry' => '2026-03-20'],
            ['bin' => 'Bin 8', 'name' => 'RestorePLUS Syringe 4g A3', 'price' => 950.00, 'qty' => 6, 'expiry' => '2026-03-20'],
            ['bin' => 'Bin 8', 'name' => 'RestorePLUS Flow Syringe 2g A3.5 & 5 Tips', 'price' => 1100.00, 'qty' => 9, 'expiry' => '2025-12-05'],
            ['bin' => 'Bin 8', 'name' => 'Supreme Syringe A2 4g', 'price' => 340.00, 'qty' => 16],
            ['bin' => 'Bin 8', 'name' => 'Supreme Syringe A3.5 4g', 'price' => 340.00, 'qty' => 32],
            ['bin' => 'Bin 8', 'name' => 'VITAPAN Classic Shade Guide 16 Shades', 'price' => 945.00, 'qty' => 4],
        ];
        $imported += $this->insertItems($company, $bin, $items);

        // --- BIN 13: Endodontics Materials ---
        $items = [
            ['bin' => 'Bin 13', 'name' => 'Dengo EDTA 15mls', 'price' => 100.00, 'qty' => 3],
            ['bin' => 'Bin 13', 'name' => 'Dencresol', 'price' => 115.00, 'qty' => 1],
            ['bin' => 'Bin 13', 'name' => 'RootPLUS Root Canal Sealant 8g Base & 3.5g Catalyst', 'price' => 1250.00, 'qty' => 1, 'expiry' => '2026-04-01'],
            ['bin' => 'Bin 13', 'name' => 'No-Pulp Devitalizer 2x3g Syringe', 'price' => 380.00, 'qty' => 8],
            ['bin' => 'Bin 13', 'name' => 'Cal Bio LC (Pulp Liner) 2g', 'price' => 1515.00, 'qty' => 2],
        ];
        $imported += $this->insertItems($company, $bin, $items);

        // --- BIN 20: Laboratory Rotary Instruments ---
        $items = [
            ['bin' => 'Bin 20', 'name' => 'Diamond Disk Hard 22.0x0.4mm Double Sided', 'price' => 50.00, 'qty' => 22],
            ['bin' => 'Bin 20', 'name' => 'Plain Bristle Brush with Handle D22mm 10 pc/pk', 'price' => 50.00, 'qty' => 91],
            ['bin' => 'Bin 20', 'name' => 'White Cotton Wheel 4*50 1 pc/pk', 'price' => 55.00, 'qty' => 22],
            ['bin' => 'Bin 20', 'name' => 'Dental Nickel-Chromium Alloy 100g/16 pcs', 'price' => 400.00, 'qty' => 8],
            ['bin' => 'Bin 20', 'name' => 'Brass Dowel Pins #2 100pcs', 'price' => 120.00, 'qty' => 9],
            ['bin' => 'Bin 20', 'name' => 'Brass Dowel Pins #5 100pcs', 'price' => 120.00, 'qty' => 9],
        ];
        $imported += $this->insertItems($company, $bin, $items);

        // --- BIN 21: Laboratory Materials ---
        $items = [
            ['bin' => 'Bin 21', 'name' => 'POP Modelling Plaster Type I White 25 kg', 'price' => 1050.00, 'qty' => 5],
            ['bin' => 'Bin 21', 'name' => 'Dental Die-Stone Type IV 25kg/Bag Blue', 'price' => 1665.00, 'qty' => 5],
            ['bin' => 'Bin 21', 'name' => 'Base Plate Wax Soft 170*95*20mm 17 Sheets 480g/box', 'price' => 320.00, 'qty' => 26],
            ['bin' => 'Bin 21', 'name' => 'Dipping Wax 225g', 'price' => 380.00, 'qty' => 15],
            ['bin' => 'Bin 21', 'name' => 'Beloform Powder Investment Material 160g + Liquid 35ml', 'price' => 90.00, 'qty' => 175, 'expiry' => '2026-06-20'],
            ['bin' => 'Bin 21', 'name' => 'Cold Mold Seal 500ml', 'price' => 180.00, 'qty' => 42, 'expiry' => '2026-09-20'],
            ['bin' => 'Bin 21', 'name' => 'Cold Cure Denture Base Powder 100g', 'price' => 235.00, 'qty' => 15, 'expiry' => '2026-08-20'],
            ['bin' => 'Bin 21', 'name' => 'Cold Cure Denture Base Liquid 500ml', 'price' => 240.00, 'qty' => 8, 'expiry' => '2026-03-20'],
            ['bin' => 'Bin 21', 'name' => 'Vacuum Forming Sheet Soft Square 2mm 710pcs/b', 'price' => 355.00, 'qty' => 17],
        ];
        $imported += $this->insertItems($company, $bin, $items);

        // --- BIN 23: Acrylic Teeth ---
        $items = [
            ['bin' => 'Bin 23', 'name' => 'Acrylic Teeth Upper Anterior A1 6 pcs/set Medium', 'price' => 55.00, 'qty' => 9],
            ['bin' => 'Bin 23', 'name' => 'Acrylic Teeth Upper Anterior A3 6 pcs/set Medium', 'price' => 55.00, 'qty' => 5],
            ['bin' => 'Bin 23', 'name' => 'Acrylic Teeth Upper Anterior A2 6 pcs/set Big', 'price' => 55.00, 'qty' => 1],
            ['bin' => 'Bin 23', 'name' => 'Acrylic Teeth Upper Anterior C1 6 pcs/set Big', 'price' => 55.00, 'qty' => 13],
            ['bin' => 'Bin 23', 'name' => 'Acrylic Teeth Upper Posterior A2 8 pcs/set', 'price' => 75.00, 'qty' => 2],
            ['bin' => 'Bin 23', 'name' => 'Acrylic Teeth Upper Posterior A3 XL 8 teeth/set 12 sets/box', 'price' => 850.00, 'qty' => 7],
            ['bin' => 'Bin 23', 'name' => 'Acrylic Teeth Lower Posterior A2 XL 8 teeth/set 12 sets/box', 'price' => 850.00, 'qty' => 1],
            ['bin' => 'Bin 23', 'name' => 'Acrylic Teeth Lower Posterior A3 XL 8 teeth/set 12 sets/box', 'price' => 850.00, 'qty' => 6],
            ['bin' => 'Bin 23', 'name' => 'Acrylic Teeth Upper Anterior Medium A1 6 teeth/set 16 sets/box', 'price' => 850.00, 'qty' => 5],
        ];
        $imported += $this->insertItems($company, $bin, $items);

        // --- BIN 25: Infection Control ---
        $items = [
            ['bin' => 'Bin 25', 'name' => 'Dental Cotton Roll 10*30mm 2000pcs/bag', 'price' => 625.00, 'qty' => 23],
            ['bin' => 'Bin 25', 'name' => 'Dental Bibs 3-Ply Tissue + Poly 125 pcs/pk', 'price' => 1000.00, 'qty' => 8],
            ['bin' => 'Bin 25', 'name' => 'Saliva Ejectors Standard Clear/Blue tip 100 pcs/pk', 'price' => 180.00, 'qty' => 17],
            ['bin' => 'Bin 25', 'name' => 'Sterilization Rolls 8" (200 x 200mm)', 'price' => 2650.00, 'qty' => 6],
            ['bin' => 'Bin 25', 'name' => 'Examination Gloves', 'price' => 180.00, 'qty' => 90],
            ['bin' => 'Bin 25', 'name' => 'Surgical Gloves', 'price' => 300.00, 'qty' => 20],
            ['bin' => 'Bin 25', 'name' => 'Face Masks', 'price' => 100.00, 'qty' => 18],
            ['bin' => 'Bin 25', 'name' => 'Gauze Roll', 'price' => 400.00, 'qty' => 4],
            ['bin' => 'Bin 25', 'name' => 'Cotton Rolls Dispenser Drawer Type Plastic', 'price' => 150.00, 'qty' => 26],
        ];
        $imported += $this->insertItems($company, $bin, $items);

        // --- BIN 27: Small Equipment ---
        $items = [
            ['bin' => 'Bin 27', 'name' => 'Ultrasonic Scaler US-44 K08A', 'price' => 8000.00, 'qty' => 4],
            ['bin' => 'Bin 27', 'name' => 'Micro Motor Saeyang MARATHON-3 Champion', 'price' => 9250.00, 'qty' => 2],
            ['bin' => 'Bin 27', 'name' => 'Two-Layer Compressor w/double tank', 'price' => 6625.00, 'qty' => 1],
            ['bin' => 'Bin 27', 'name' => 'Endo Motor', 'price' => 7500.00, 'qty' => 1],
            ['bin' => 'Bin 27', 'name' => 'Dental Loupes 3.5X', 'price' => 8100.00, 'qty' => 1],
            ['bin' => 'Bin 27', 'name' => 'Vacuum Former', 'price' => 6000.00, 'qty' => 1],
            ['bin' => 'Bin 27', 'name' => 'Curing Light Woodpecker LED.B', 'price' => 3800.00, 'qty' => 5],
        ];
        $imported += $this->insertItems($company, $bin, $items);

        // --- BIN 28: Teaching Models ---
        $items = [
            ['bin' => 'Bin 28', 'name' => 'Teeth Model Set #16, 14, 11, 21, 33, 36, 45', 'price' => 900.00, 'qty' => 4],
            ['bin' => 'Bin 28', 'name' => 'Mouth Model M2001', 'price' => 900.00, 'qty' => 3],
            ['bin' => 'Bin 28', 'name' => 'Mouth Model M4001-2', 'price' => 900.00, 'qty' => 2],
            ['bin' => 'Bin 28', 'name' => 'Mouth Model M4002', 'price' => 1300.00, 'qty' => 4],
            ['bin' => 'Bin 28', 'name' => 'Skull with removable parts', 'price' => 8710.00, 'qty' => 1],
        ];
        $imported += $this->insertItems($company, $bin, $items);

        $this->info("Imported {$imported} sample items for {$company->name}.");

        return Command::SUCCESS;
    }

    private function insertItems(Company $company, callable $binFinder, array $items): int
    {
        $count = 0;
        $now = now();

        foreach ($items as $data) {
            $b = $binFinder($data['bin']);
            if (!$b) {
                $this->warn("Bin '{$data['bin']}' not found, skipping: {$data['name']}");
                continue;
            }

            Item::create([
                'sa_company_id' => $company->id,
                'sa_department_id' => $b->sa_department_id,
                'sa_bin_id' => $b->id,
                'name' => $data['name'],
                'unit_price' => $data['price'],
                'unit' => $data['unit'] ?? 'pcs',
                'system_quantity' => $data['qty'],
                'category' => $b->label,
                'is_expirable' => !empty($data['expiry']),
                'expiry_date' => $data['expiry'] ?? null,
            ]);
            $count++;
        }

        return $count;
    }
}
