<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt Stationery</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; }

        /*
         * @page has margin: 0 so the full 210×297mm is available.
         *
         * The .page div carries the real margins via CSS `margin`
         * (NOT padding). In dompdf, `margin` correctly reduces the
         * block's available width, while `padding` gets added on top
         * of `width` because dompdf ignores box-sizing:border-box.
         *
         * .page has NO explicit width — `width:auto` on a block element
         * naturally resolves to: parent(210mm) − margin-left(18mm)
         *                                      − margin-right(5mm)
         *                      = 187mm  ✓
         *
         * The .rg table then fills width:100% = 187mm exactly.
         *
         * Margins:
         *   left  18mm — binding/gutter
         *   others 5mm — safe zone
         */
        @page {
            margin: 0;
            size: A4 portrait;
        }

        .page {
            /* width intentionally omitted — auto fills available space */
            margin-top:    5mm;
            margin-right:  5mm;
            margin-bottom: 5mm;
            margin-left:   18mm;
        }

        /* ── Outer grid ───────────────────────────────────────── */
        .rg {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: avoid;
        }
        .rc {
            vertical-align: top;
            border: 1px dashed #aaa;
            background: #fefef8;
            overflow: hidden;
            padding: 0;
        }

        /*
         * ── Flat inner receipt table (14 rows) ────────────────
         *  Row  1     : header
         *  Rows 2–3   : F1 No/Date          (label + underline)
         *  Rows 4–5   : F2 Received from    (label + underline)
         *  Rows 6–7   : F3 Amount in words  (label + underline)
         *  Rows 8–9   : F4 Payment/Cheque   (label + underline)
         *  Rows 10–11 : F5 Purpose/Contact  (label + underline)
         *  Row  12    : F6 Amount ZMW       (inline: label + box)
         *  Row  13    : F7 Signatures       (inline: label|ul|label|ul)
         *  Row  14    : footer bar
         */
        .ft { width:100%; border-collapse:collapse; table-layout:fixed; }
        .ft td { padding:0; overflow:hidden; vertical-align:bottom; }

        .ht { width:100%; border-collapse:collapse; table-layout:fixed; }
        .ht td { vertical-align:middle; padding:0; overflow:hidden; }

        .rtitle {
            text-align:center;
            border:2px solid #2d7a4f;
            color:#2d7a4f;
            font-weight:bold;
            background:#fff;
        }

        .ct { width:100%; border-collapse:collapse; table-layout:fixed; }
        .ct td { padding:0; vertical-align:bottom; overflow:hidden; }

        .ul { display:block; width:100%; height:100%; border-bottom:1px solid #555; }

        .amt-box {
            display:block;
            border:1.5px solid #000;
            background:#fff;
        }

        .fbar { background:#2d7a4f; display:block; width:100%; }
    </style>
</head>
<body>
@php
    /* ── Layout ──────────────────────────────────────────────── */
    $dpp  = (int) $documentsPerPage;
    $cols = match($dpp) { 1=>1, 2=>1, 4=>2, 6=>3, 8=>2, 10=>2, default=>2 };
    $rows = (int) ceil($dpp / $cols);

    $totalDocs  = count($documentNumbers);
    $totalPages = isset($totalPages) ? (int)$totalPages : (int)ceil($totalDocs / $dpp);
    $maxPages   = (isset($isPdf) && !$isPdf) ? min(2, $totalPages) : $totalPages;

    /*
     * Usable area after .page margins:
     *   width  = 210 − 18 − 5 = 187mm  (table fills this via width:100%)
     *   height = 297 −  5 − 5 = 287mm
     *
     * $pgH_max = 265mm (287 − 22mm buffer for dompdf rendering slack).
     * $cellH   = floor($pgH_max / $rows)
     */
    $pgH_max = 265;
    $cellH   = (int) floor($pgH_max / $rows);
    $colW    = round(100 / $cols, 4);

    /* Cell outer padding top & bottom each */
    $cp = match(true) { $rows >= 4 => 1, $rows == 2 => 2, default => 3 };

    /* Inner box height = cell − padding×2 − 1px border */
    $boxH = $cellH - ($cp * 2) - 1;

    /* Left/right padding inside the receipt */
    $lr = match(true) { $rows >= 4 => 1, $rows == 2 => 2, default => 3 };

    /* Typography */
    $fs      = match(true) { $rows>=5=>6, $rows>=4=>6, $rows>=3=>7, $rows==2=>8, default=>9 };
    $titleFs = $fs + 2;
    $bizFs   = max(5, $fs - 1);

    /* Logo */
    $logoW = 15;
    $logoH = 9;

    /* Currency */
    $curr = method_exists($businessProfile, 'currency')
        ? ($businessProfile->currency() ?? 'ZMW') : 'ZMW';

    /*
     * ── Vertical budget ──────────────────────────────────────
     *
     * Row  1 : HDR  = $logoH mm
     * Row 14 : FTR  = $ftrH mm (absorbs rounding remainder)
     *
     * 7 equal field slots × $fh mm:
     *   Slots 1–5 : 2 sub-rows ($lh label + $uh underline)
     *   Slot  6   : 1 row — Amount label + bordered box
     *   Slot  7   : 1 row — Signatures (inline)
     */
    $hdrH = (float) $logoH;
    $ftrH = 3.0;
    $numF = 7;

    $fh = round(($boxH - $hdrH - $ftrH) / $numF, 4);

    /* 1 text-line in mm  (1px = 0.353mm, line-height 1.6) */
    $lh = round($fs * 0.353 * 1.6, 4);
    $uh = round($fh - $lh, 4);
    $uh = max(1.0, $uh);
    $lh = round($fh - $uh, 4);   /* lh + uh = fh exactly */

    /* Absorb floating-point drift into the footer */
    $calcTotal = $hdrH + 5.0 * ($lh + $uh) + $fh + $fh + $ftrH;
    $ftrH      = round($ftrH + ($boxH - $calcTotal), 4);
    $ftrH      = max(2.0, $ftrH);

    /* Amount box: 55% of $fh, max 6mm, min 3mm */
    $amtBoxH = min(round($fh * 0.55, 2), 6.0);
    $amtBoxH = max(3.0, $amtBoxH);
@endphp

@for ($p = 0; $p < $maxPages; $p++)

    @if ($p > 0)
        <div style="page-break-before: always;"></div>
    @endif

    {{--
        .page margins create the binding/safe-zone whitespace.
        No width set — block auto-sizing gives us exactly 187mm.
    --}}
    <div class="page">
        <table class="rg">
            @for ($r = 0; $r < $rows; $r++)
                <tr>
                    @for ($c = 0; $c < $cols; $c++)
                        @php
                            $idx = $p * $dpp + $r * $cols + $c;
                            $doc = ($idx < $totalDocs) ? $documentNumbers[$idx] : null;
                        @endphp

                        @if ($doc === null)
                            <td class="rc"
                                style="width:{{ $colW }}%; height:{{ $cellH }}mm;
                                       padding:{{ $cp }}mm; visibility:hidden;"></td>
                        @else

                        <td class="rc"
                            style="width:{{ $colW }}%; height:{{ $cellH }}mm;
                                   padding:{{ $cp }}mm;">

                            <table class="ft">

                                {{-- ── ROW 1: HEADER ───────────────────── --}}
                                <tr style="height:{{ $hdrH }}mm;">
                                <td style="height:{{ $hdrH }}mm; padding:0 {{ $lr }}mm;
                                           vertical-align:middle; overflow:hidden;">
                                    <table class="ht">
                                        <tr>
                                            <td style="width:{{ $logoW }}mm;">
                                                @if ($logoPath)
                                                    <img src="{{ $logoPath }}" alt="Logo"
                                                         style="height:{{ $logoH }}mm; width:auto;
                                                                max-width:{{ $logoW }}mm; display:block;">
                                                @endif
                                            </td>
                                            <td style="text-align:center;">
                                                <table style="margin:0 auto; border-collapse:collapse;">
                                                    <tr><td class="rtitle"
                                                            style="font-size:{{ $titleFs }}px;
                                                                   padding:0.8mm 8mm;">RECEIPT</td></tr>
                                                </table>
                                            </td>
                                            <td style="width:24mm; text-align:right;
                                                       font-size:{{ $bizFs }}px; line-height:1.25;">
                                                <strong>{{ $businessProfile->businessName() }}</strong><br>
                                                {{ $businessProfile->phone() }}
                                                @if ($businessProfile->tpin())
                                                    <br>TPIN: {{ $businessProfile->tpin() }}
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                </tr>

                                {{-- ── F1: No / Date ───────────────────── --}}
                                <tr style="height:{{ $lh }}mm;">
                                <td style="height:{{ $lh }}mm; padding:0 {{ $lr }}mm;
                                           vertical-align:bottom; font-size:{{ $fs }}px;">
                                    <table class="ct"><tr>
                                        <td style="width:55%;"><strong>No:</strong>
                                            <span style="color:#dc2626; font-weight:bold;
                                                         background:#fff5f5; padding:0 1mm;">{{ $doc }}</span>
                                        </td>
                                        <td style="width:45%; text-align:right;"><strong>Date:</strong></td>
                                    </tr></table>
                                </td>
                                </tr>
                                <tr style="height:{{ $uh }}mm;">
                                <td style="height:{{ $uh }}mm; padding:0 {{ $lr }}mm; overflow:hidden;">
                                    <table class="ct"><tr>
                                        <td style="width:55%;"></td>
                                        <td style="width:45%;">
                                            <div class="ul" style="height:{{ $uh }}mm;"></div>
                                        </td>
                                    </tr></table>
                                </td>
                                </tr>

                                {{-- ── F2: Received with thanks from ───── --}}
                                <tr style="height:{{ $lh }}mm;">
                                <td style="height:{{ $lh }}mm; padding:0 {{ $lr }}mm;
                                           vertical-align:bottom; font-size:{{ $fs }}px;">
                                    Received with thanks from:
                                </td>
                                </tr>
                                <tr style="height:{{ $uh }}mm;">
                                <td style="height:{{ $uh }}mm; padding:0 {{ $lr }}mm; overflow:hidden;">
                                    <div class="ul" style="height:{{ $uh }}mm;"></div>
                                </td>
                                </tr>

                                {{-- ── F3: Amount in words ─────────────── --}}
                                <tr style="height:{{ $lh }}mm;">
                                <td style="height:{{ $lh }}mm; padding:0 {{ $lr }}mm;
                                           vertical-align:bottom; font-size:{{ $fs }}px;">
                                    Amount in words:
                                </td>
                                </tr>
                                <tr style="height:{{ $uh }}mm;">
                                <td style="height:{{ $uh }}mm; padding:0 {{ $lr }}mm; overflow:hidden;">
                                    <div class="ul" style="height:{{ $uh }}mm;"></div>
                                </td>
                                </tr>

                                {{-- ── F4: Payment / Cheque / Date ─────── --}}
                                <tr style="height:{{ $lh }}mm;">
                                <td style="height:{{ $lh }}mm; padding:0 {{ $lr }}mm;
                                           vertical-align:bottom; font-size:{{ $fs }}px;">
                                    <table class="ct"><tr>
                                        <td style="width:34%;">Payment Mode:</td>
                                        <td style="width:34%;">Cheque No:</td>
                                        <td style="width:30%;">Date:</td>
                                    </tr></table>
                                </td>
                                </tr>
                                <tr style="height:{{ $uh }}mm;">
                                <td style="height:{{ $uh }}mm; padding:0 {{ $lr }}mm; overflow:hidden;">
                                    <table class="ct"><tr>
                                        <td style="width:34%; padding-right:2mm;">
                                            <div class="ul" style="height:{{ $uh }}mm;"></div>
                                        </td>
                                        <td style="width:34%; padding-right:2mm;">
                                            <div class="ul" style="height:{{ $uh }}mm;"></div>
                                        </td>
                                        <td style="width:30%;">
                                            <div class="ul" style="height:{{ $uh }}mm;"></div>
                                        </td>
                                    </tr></table>
                                </td>
                                </tr>

                                {{-- ── F5: Purpose / Contact ───────────── --}}
                                <tr style="height:{{ $lh }}mm;">
                                <td style="height:{{ $lh }}mm; padding:0 {{ $lr }}mm;
                                           vertical-align:bottom; font-size:{{ $fs }}px;">
                                    <table class="ct"><tr>
                                        <td style="width:62%;">For the purpose of:</td>
                                        <td style="width:36%;">Contact:</td>
                                    </tr></table>
                                </td>
                                </tr>
                                <tr style="height:{{ $uh }}mm;">
                                <td style="height:{{ $uh }}mm; padding:0 {{ $lr }}mm; overflow:hidden;">
                                    <table class="ct"><tr>
                                        <td style="width:62%; padding-right:2mm;">
                                            <div class="ul" style="height:{{ $uh }}mm;"></div>
                                        </td>
                                        <td style="width:36%;">
                                            <div class="ul" style="height:{{ $uh }}mm;"></div>
                                        </td>
                                    </tr></table>
                                </td>
                                </tr>

                                {{-- ── F6: Amount — inline label + box ──── --}}
                                <tr style="height:{{ $fh }}mm;">
                                <td style="height:{{ $fh }}mm; padding:0 {{ $lr }}mm;
                                           vertical-align:middle; overflow:hidden;">
                                    <table class="ct" style="height:{{ $fh }}mm;">
                                        <tr style="height:{{ $fh }}mm;">
                                            <td style="width:35%; height:{{ $fh }}mm;
                                                       vertical-align:middle;
                                                       font-size:{{ $fs }}px; padding-right:2mm;
                                                       white-space:nowrap;">
                                                <strong>Amount ({{ $curr }}):</strong>
                                            </td>
                                            <td style="width:65%; height:{{ $fh }}mm;
                                                       vertical-align:middle;">
                                                <div class="amt-box"
                                                     style="height:{{ $amtBoxH }}mm; width:85%;"></div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                </tr>

                                {{-- ── F7: Signatures — inline ─────────── --}}
                                <tr style="height:{{ $fh }}mm;">
                                <td style="height:{{ $fh }}mm; padding:0 {{ $lr }}mm;
                                           vertical-align:bottom; overflow:hidden;">
                                    <table class="ct" style="height:{{ $fh }}mm;">
                                        <tr style="height:{{ $fh }}mm;">
                                            <td style="width:22%; height:{{ $fh }}mm;
                                                       vertical-align:bottom; white-space:nowrap;
                                                       font-size:{{ $fs }}px; padding-right:1mm;">
                                                <strong>Received By:</strong>
                                            </td>
                                            <td style="width:26%; height:{{ $fh }}mm;
                                                       vertical-align:bottom; padding-right:4mm;">
                                                <div class="ul" style="height:{{ $uh }}mm;"></div>
                                            </td>
                                            <td style="width:19%; height:{{ $fh }}mm;
                                                       vertical-align:bottom; white-space:nowrap;
                                                       font-size:{{ $fs }}px; padding-right:1mm;">
                                                <strong>Signature:</strong>
                                            </td>
                                            <td style="width:33%; height:{{ $fh }}mm;
                                                       vertical-align:bottom;">
                                                <div class="ul" style="height:{{ $uh }}mm;"></div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                </tr>

                                {{-- ── ROW 14: FOOTER BAR ──────────────── --}}
                                <tr style="height:{{ $ftrH }}mm;">
                                <td style="height:{{ $ftrH }}mm; padding:1mm {{ $lr }}mm 0;
                                           vertical-align:bottom; overflow:hidden;">
                                    <div class="fbar" style="height:2mm;"></div>
                                </td>
                                </tr>

                            </table>
                        </td>

                        @endif
                    @endfor
                </tr>
            @endfor
        </table>
    </div>

@endfor
</body>
</html>