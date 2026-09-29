<?php
declare(strict_types=1);

final class SimplePdf
{
    private const W = 210.0;
    private const H = 297.0;
    private const PT_PER_MM = 2.8346456693;

    private array $pages = [];
    private int $page = -1;
    private string $font = 'Helvetica';
    private float $fontSize = 10;
    private array $textColor = [20, 30, 45];
    private array $fillColor = [255, 255, 255];
    private array $drawColor = [210, 214, 220];

    public function __construct()
    {
        $this->addPage();
    }

    public function addPage(): void
    {
        $this->pages[] = '';
        $this->page = count($this->pages) - 1;
        $this->font = 'Helvetica';
        $this->fontSize = 10;
        $this->textColor = [20, 30, 45];
        $this->fillColor = [255, 255, 255];
        $this->drawColor = [210, 214, 220];
    }

    public function setFont(string $font = 'Helvetica', string $style = '', float $size = 10): void
    {
        $this->font = $style === 'B' ? 'Helvetica-Bold' : ($style === 'BI' || $style === 'I' ? 'Helvetica-Oblique' : 'Helvetica');
        $this->fontSize = $size;
    }

    public function setTextColor(int $r, int $g, int $b): void { $this->textColor = [$r,$g,$b]; }
    public function setFillColor(int $r, int $g, int $b): void { $this->fillColor = [$r,$g,$b]; }
    public function setDrawColor(int $r, int $g, int $b): void { $this->drawColor = [$r,$g,$b]; }

    public function text(float $x, float $yFromTop, string $text): void
    {
        $this->write('BT');
        $this->write(sprintf('/%s %.2f Tf', $this->font === 'Helvetica-Bold' ? 'F2' : ($this->font === 'Helvetica-Oblique' ? 'F3' : 'F1'), $this->fontSize));
        $this->write(sprintf('%.3f %.3f %.3f rg', $this->textColor[0]/255, $this->textColor[1]/255, $this->textColor[2]/255));
        $this->write(sprintf('1 0 0 1 %.2f %.2f Tm (%s) Tj', $x*self::PT_PER_MM, (self::H-$yFromTop)*self::PT_PER_MM, $this->encode($text)));
        $this->write('ET');
    }

    public function line(float $x1, float $y1, float $x2, float $y2): void
    {
        $this->write(sprintf('%.3f %.3f %.3f RG %.2f w %.2f %.2f m %.2f %.2f l S',
            $this->drawColor[0]/255,$this->drawColor[1]/255,$this->drawColor[2]/255,
            0.35, $x1*self::PT_PER_MM, (self::H-$y1)*self::PT_PER_MM, $x2*self::PT_PER_MM, (self::H-$y2)*self::PT_PER_MM));
    }

    public function rect(float $x, float $yFromTop, float $w, float $h, bool $filled = false): void
    {
        $xpt=$x*self::PT_PER_MM; $ypt=(self::H-$yFromTop-$h)*self::PT_PER_MM; $wpt=$w*self::PT_PER_MM; $hpt=$h*self::PT_PER_MM;
        $this->write(sprintf('%.3f %.3f %.3f RG', $this->drawColor[0]/255,$this->drawColor[1]/255,$this->drawColor[2]/255));
        if ($filled) {
            $this->write(sprintf('%.3f %.3f %.3f rg %.2f %.2f %.2f %.2f re B', $this->fillColor[0]/255,$this->fillColor[1]/255,$this->fillColor[2]/255,$xpt,$ypt,$wpt,$hpt));
        } else {
            $this->write(sprintf('%.2f %.2f %.2f %.2f re S',$xpt,$ypt,$wpt,$hpt));
        }
    }

    public function roundedRect(float $x,float $y,float $w,float $h,float $r,bool $filled=false): void
    {
        // Rounded corners are approximated with a normal rectangle for maximum PDF compatibility.
        $this->rect($x,$y,$w,$h,$filled);
    }

    public function multiText(float $x, float &$y, float $maxWidth, string $text, float $lineHeight = 5): void
    {
        $lines = $this->wrap($text, $maxWidth);
        foreach ($lines as $line) {
            if ($y > 280) { $this->addPage(); $y = 18; }
            $this->text($x,$y,$line);
            $y += $lineHeight;
        }
    }

    public function wrap(string $text, float $maxWidth): array
    {
        $text = preg_replace('/\s+/u',' ',trim($text)) ?? trim($text);
        if ($text === '') return [''];
        $maxChars = max(12, (int)floor($maxWidth / max(1.6, $this->fontSize * 0.47 * 0.3528)));
        $words = preg_split('/\s+/u',$text) ?: [$text];
        $lines=[]; $line='';
        foreach ($words as $word) {
            $candidate = $line === '' ? $word : $line . ' ' . $word;
            $length = function_exists('mb_strlen') ? mb_strlen($candidate, 'UTF-8') : strlen($candidate);
            if ($length <= $maxChars) { $line = $candidate; continue; }
            if ($line !== '') $lines[]=$line;
            $line=$word;
        }
        if ($line!=='') $lines[]=$line;
        return $lines;
    }

    private function encode(string $text): string
    {
        $text = str_replace(['—','–','•','→','←','“','”','’','‘','…'], ['-','-','-','->','<-','"','"',"'","'",'...'], $text);
        $text = (string)@iconv('UTF-8','Windows-1252//TRANSLIT//IGNORE',$text);
        return strtr($text, ['\\'=>'\\\\','('=>'\\(',')'=>'\\)']);
    }

    private function write(string $line): void
    {
        $this->pages[$this->page] .= $line . "\n";
    }

    public function outputDownload(string $filename): never
    {
        $pdf = $this->build();
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9_.-]+/','_',$filename) . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }

    public function build(): string
    {
        $objects=[];
        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[] = ''; // pages object is filled below
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Oblique /Encoding /WinAnsiEncoding >>';

        $pageRefs=[];
        foreach ($this->pages as $content) {
            $contentObj = count($objects)+1;
            $pageObj = $contentObj+1;
            $stream = $content;
            $objects[] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream";
            $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595.28 841.89] /Resources << /Font << /F1 3 0 R /F2 4 0 R /F3 5 0 R >> >> /Contents {$contentObj} 0 R >>";
            $pageRefs[] = $pageObj . ' 0 R';
        }
        $objects[1] = '<< /Type /Pages /Count ' . count($pageRefs) . ' /Kids [' . implode(' ',$pageRefs) . '] >>';

        $pdf="%PDF-1.4\n%\xE2\xE3\xCF\xD3\n"; $offsets=[0];
        foreach ($objects as $i=>$object) { $num=$i+1; $offsets[$num]=strlen($pdf); $pdf .= $num . " 0 obj\n" . $object . "\nendobj\n"; }
        $xrefOffset=strlen($pdf); $pdf .= "xref\n0 " . (count($objects)+1) . "\n0000000000 65535 f \n";
        for($i=1;$i<=count($objects);$i++) $pdf .= sprintf("%010d 00000 n \n",$offsets[$i]);
        $pdf .= "trailer\n<< /Size " . (count($objects)+1) . " /Root 1 0 R >>\nstartxref\n".$xrefOffset."\n%%EOF";
        return $pdf;
    }
}
