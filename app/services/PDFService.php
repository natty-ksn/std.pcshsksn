<?php
/**
 * PDFService — บริการออกรายงาน PDF (Phase 3 Formal Excellence Engine)
 *
 * รองรับฟอนต์ Sarabun สำหรับเอกสารราชการ พร้อม header/footer มาตรฐาน
 * Backward compatible กับการเรียกใช้เดิม (Garuda fallback อัตโนมัติ)
 */
class PDFService
{
    /** ตรวจสอบว่ามีฟอนต์ Sarabun ครบหรือไม่ */
    private static function hasSarabun(): bool
    {
        $dir = ROOT_PATH . '/storage/fonts';
        return file_exists($dir . '/Sarabun-Regular.ttf')
            && file_exists($dir . '/Sarabun-Bold.ttf');
    }

    /**
     * สร้าง mPDF instance พร้อม config มาตรฐานเอกสารราชการ
     *
     * @param array $opts รองรับ: title, orientation (P|L), header (HTML), footer (HTML),
     *                    watermark (string), margin_top, margin_bottom
     */
    public static function make(array $opts = []): \Mpdf\Mpdf
    {
        // Ensure adequate memory for mPDF rendering
        if ((int) ini_get('memory_limit') < 256) {
            ini_set('memory_limit', '256M');
        }

        // Clean up temporary files periodically
        self::cleanupTempFiles();

        if (!class_exists('\Mpdf\Mpdf') && file_exists(ROOT_PATH . '/vendor/autoload.php')) {
            require_once ROOT_PATH . '/vendor/autoload.php';
        }

        $useSarabun = self::hasSarabun();
        $defaultFont = $useSarabun ? 'sarabun' : 'garuda';

        $config = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => $opts['orientation'] ?? 'P',
            'default_font' => $defaultFont,
            'default_font_size' => 14,
            'margin_left' => 20,
            'margin_right' => 20,
            'margin_top' => $opts['margin_top'] ?? 32,
            'margin_bottom' => $opts['margin_bottom'] ?? 24,
            'margin_header' => 10,
            'margin_footer' => 10,
            'tempDir' => ROOT_PATH . '/storage/mpdf_tmp',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'useSubstitutions' => true,
        ];

        // ผูกฟอนต์ Sarabun ถ้ามี
        if ($useSarabun) {
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];

            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            $config['fontDir'] = array_merge($fontDirs, [ROOT_PATH . '/storage/fonts']);
            $config['fontdata'] = $fontData + [
                'sarabun' => [
                    'R'  => 'Sarabun-Regular.ttf',
                    'B'  => 'Sarabun-Bold.ttf',
                    'I'  => 'Sarabun-Italic.ttf',
                    'BI' => 'Sarabun-BoldItalic.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ],
            ];
        }

        // เตรียม temp dir
        if (!is_dir($config['tempDir'])) {
            @mkdir($config['tempDir'], 0775, true);
        }

        $mpdf = new \Mpdf\Mpdf($config);

        $mpdf->SetTitle($opts['title'] ?? 'PCSHSKSN Document');
        $mpdf->SetAuthor('โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์');
        $mpdf->SetCreator('PCSHSKSN e-Portfolio System');

        // Header/Footer มาตรฐานราชการ
        $header = $opts['header'] ?? self::defaultHeader($opts['title'] ?? '');
        $footer = $opts['footer'] ?? self::defaultFooter();

        $mpdf->SetHTMLHeader($header);
        $mpdf->SetHTMLFooter($footer);

        // Watermark (ถ้ามี)
        if (!empty($opts['watermark'])) {
            $mpdf->SetWatermarkText($opts['watermark']);
            $mpdf->showWatermarkText = true;
            $mpdf->watermark_font = $defaultFont;
            $mpdf->watermarkTextAlpha = 0.08;
        }

        // CSS ฐานสำหรับเอกสารราชการ
        $mpdf->WriteHTML(self::baseCss($defaultFont), \Mpdf\HTMLParserMode::HEADER_CSS);

        return $mpdf;
    }

    /** Header เริ่มต้น (ตราโรงเรียน + ชื่อโรงเรียน + ชื่อเอกสาร) */
    private static function defaultHeader(string $title): string
    {
        $logoPath = file_exists(ROOT_PATH . '/public/assets/images/logo.png')
            ? ROOT_PATH . '/public/assets/images/logo.png'
            : ROOT_PATH . '/public/assets/img/logo-pcshs-ksn.png';
        $logoTag = file_exists($logoPath)
            ? '<img src="' . $logoPath . '" style="height: 44px; vertical-align: middle;">'
            : '<div style="height:40px;width:40px;border:1.5pt solid #1e3a8a;border-radius:50%;text-align:center;line-height:38px;font-weight:bold;color:#1e3a8a;">จภ</div>';

        return '
        <table width="100%" style="border-bottom: 1pt solid #1e3a8a; padding-bottom: 4pt;">
            <tr>
                <td width="60" style="vertical-align: middle;">' . $logoTag . '</td>
                <td style="vertical-align: middle;">
                    <div style="font-size: 13pt; font-weight: bold; color: #0f172a;">โรงเรียนวิทยาศาสตร์จุฬาภรณราชวิทยาลัย กาฬสินธุ์</div>
                    <div style="font-size: 9pt; color: #475569;">Princess Chulabhorn Science High School Kalasin</div>
                </td>
                <td width="180" style="text-align: right; vertical-align: middle; font-size: 9pt; color: #475569;">
                    เอกสาร: ' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '<br>
                    ออกเมื่อ: ' . self::thaiDate() . '
                </td>
            </tr>
        </table>';
    }

    /** Footer เริ่มต้น (เลขหน้า + ลิขสิทธิ์) */
    private static function defaultFooter(): string
    {
        return '
        <table width="100%" style="border-top: 0.5pt solid #cbd5e1; padding-top: 4pt; font-size: 8pt; color: #64748b;">
            <tr>
                <td>© PCSHS KSN — เอกสารนี้ออกโดยระบบ e-Portfolio และไม่ต้องประทับตรา</td>
                <td style="text-align: right;">หน้า {PAGENO} / {nbpg}</td>
            </tr>
        </table>';
    }

    /** CSS ฐาน (Premium Government Style) */
    private static function baseCss(string $font): string
    {
        return "
        body { font-family: '{$font}', sans-serif; font-size: 14pt; line-height: 1.55; color: #1e293b; }
        h1, h2, h3, h4 { color: #0f172a; font-weight: bold; margin: 0 0 10pt; }
        h1 { font-size: 22pt; text-align: center; }
        h2 { font-size: 17pt; border-bottom: 1pt solid #94a3b8; padding-bottom: 4pt; margin-top: 18pt; }
        h3 { font-size: 14pt; color: #1e3a8a; margin-top: 14pt; }
        h4 { font-size: 13pt; color: #334155; }
        p  { margin: 0 0 8pt; text-indent: 1cm; }
        .no-indent p, p.no-indent { text-indent: 0; }

        table { width: 100%; border-collapse: collapse; margin: 8pt 0 14pt; }
        th, td { border: 0.5pt solid #cbd5e1; padding: 6pt 8pt; vertical-align: top; font-size: 12pt; }
        th { background-color: #f1f5f9; color: #0f172a; font-weight: bold; text-align: left; }
        table.clean, table.clean th, table.clean td { border: none; }
        table.clean th, table.clean td { padding: 3pt 6pt; }

        .doc-title { text-align: center; font-size: 20pt; font-weight: bold; margin: 18pt 0 6pt; color: #0f172a; }
        .doc-subtitle { text-align: center; font-size: 13pt; color: #475569; margin-bottom: 22pt; }
        .doc-meta { background: #f8fafc; border-left: 3pt solid #1e3a8a; padding: 10pt 14pt; margin: 12pt 0 18pt; font-size: 12pt; }
        .doc-meta strong { color: #0f172a; }

        .badge { display: inline-block; padding: 2pt 8pt; border-radius: 3pt; font-size: 10pt; font-weight: bold; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger  { background: #fee2e2; color: #991b1b; }
        .badge-info    { background: #dbeafe; color: #1e40af; }

        .signature-row { margin-top: 50pt; }
        .signature-cell { width: 50%; text-align: center; vertical-align: bottom; padding: 0 20pt; font-size: 12pt; }
        .signature-line { border-top: 0.8pt solid #1e293b; margin: 50pt 20pt 6pt; }

        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .text-muted  { color: #64748b; }
        .small       { font-size: 11pt; }
        .mt-2 { margin-top: 8pt; } .mt-3 { margin-top: 12pt; } .mt-4 { margin-top: 18pt; }
        ";
    }

    /** แปลงเป็นวันที่ไทย พ.ศ. */
    public static function thaiDate(?string $date = null): string
    {
        $months = ['', 'มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
                   'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
        $ts = $date ? strtotime($date) : time();
        if (!$ts) return '-';
        $d = (int) date('j', $ts);
        $m = (int) date('n', $ts);
        $y = (int) date('Y', $ts) + 543;
        return sprintf('%d %s %d', $d, $months[$m], $y);
    }

    // ==========================================================
    // LEGACY API (Backward compatible — ใช้ใน AnalyticsController)
    // ==========================================================

    /**
     * @deprecated ใช้ make() + WriteHTML() แทน
     */
    public static function create(string $html, string $title = 'Report', string $orientation = 'P'): \Mpdf\Mpdf
    {
        $mpdf = self::make(['title' => $title, 'orientation' => $orientation]);
        $mpdf->WriteHTML($html, 0); // Use 0 (DEFAULT_MODE) to support <style> tags
        return $mpdf;
    }

    public static function stream(string $html, string $filename, string $title = 'Report', string $orientation = 'P'): void
    {
        self::create($html, $title, $orientation)
            ->Output($filename . '.pdf', \Mpdf\Output\Destination::INLINE);
    }

    public static function download(string $html, string $filename, string $title = 'Report', string $orientation = 'P'): void
    {
        self::create($html, $title, $orientation)
            ->Output($filename . '.pdf', \Mpdf\Output\Destination::DOWNLOAD);
    }

    public static function saveToFile(string $html, string $path, string $title = 'Report', string $orientation = 'P'): string
    {
        self::create($html, $title, $orientation)
            ->Output($path, \Mpdf\Output\Destination::FILE);
        return $path;
    }

    // ==========================================================
    // HIGH-LEVEL HELPERS (Phase 3 API)
    // ==========================================================

    /**
     * Render view template เป็น PDF แล้วส่งออก browser
     *
     * @param string $viewPath  เช่น 'pdf/project_progress_report'
     * @param array  $data      ตัวแปรที่ส่งให้ template
     * @param array  $opts      title, filename, orientation, watermark, mode (I|D|F)
     */
    public static function renderView(string $viewPath, array $data, array $opts = []): void
    {
        $file = VIEW_PATH . '/' . str_replace('.', '/', $viewPath) . '.php';
        if (!file_exists($file)) {
            throw new \RuntimeException("PDF view not found: {$file}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        $html = ob_get_clean();

        $mpdf = self::make($opts);
        $mpdf->WriteHTML($html, 0); // 0 = DEFAULT_MODE (Parses both <style> and body)

        $mode = $opts['mode'] ?? 'I'; // I=inline, D=download, F=file
        $filename = ($opts['filename'] ?? 'document') . '.pdf';

        $destMap = [
            'I' => \Mpdf\Output\Destination::INLINE,
            'D' => \Mpdf\Output\Destination::DOWNLOAD,
            'F' => \Mpdf\Output\Destination::FILE,
        ];
        $mpdf->Output($filename, $destMap[$mode] ?? \Mpdf\Output\Destination::INLINE);
    }

    /**
     * ล้างไฟล์ชั่วคราวใน storage/mpdf_tmp ที่มีอายุเกินกำหนด
     */
    public static function cleanupTempFiles(int $maxAge = 86400): void
    {
        $tmpDir = ROOT_PATH . '/storage/mpdf_tmp';
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
            return;
        }

        // Cleanup with 5% sampling to avoid IO overhead on every invocation
        if (rand(1, 20) === 1) {
            $now = time();
            $files = @scandir($tmpDir) ?: [];
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || $file === '.gitignore' || $file === '.htaccess') {
                    continue;
                }
                $filePath = $tmpDir . '/' . $file;
                if (is_file($filePath) && ($now - filemtime($filePath)) > $maxAge) {
                    @unlink($filePath);
                }
            }
        }
    }
}
