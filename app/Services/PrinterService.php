<?php

namespace App\Services;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\EscposImage;
use App\Models\Kot;
use App\Models\Sale;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PrinterService
{
    protected $config;

    public function __construct()
    {
        $this->config = config('printers');
    }

    /**
     * Print KOT (Kitchen Order Ticket)
     */
    public function printKOT(Kot $kot)
    {
        if (!$this->config['printers']['kot']['enabled']) {
            Log::info('KOT printer is disabled');
            return false;
        }

        try {
            $printer = $this->getConnector('kot');
            $this->formatKOT($printer, $kot);
            $printer->close();
            
            Log::info("KOT printed successfully: {$kot->kot_no}");
            return true;
        } catch (Exception $e) {
            return $this->handlePrintError('KOT', $kot, $e);
        }
    }

    /**
     * Print BOT (Bar Order Ticket)
     */
    public function printBOT(Kot $bot)
    {
        if (!$this->config['printers']['bot']['enabled']) {
            Log::info('BOT printer is disabled');
            return false;
        }

        try {
            $printer = $this->getConnector('bot');
            $this->formatBOT($printer, $bot);
            $printer->close();
            
            Log::info("BOT printed successfully: {$bot->kot_no}");
            return true;
        } catch (Exception $e) {
            return $this->handlePrintError('BOT', $bot, $e);
        }
    }

    /**
     * Print POS Receipt
     */
    public function printReceipt(Sale $sale)
    {
        if (!$this->config['printers']['pos']['enabled']) {
            Log::info('POS printer is disabled');
            return false;
        }

        try {
            $printer = $this->getConnector('pos');
            $this->formatReceipt($printer, $sale);
            $printer->close();
            
            Log::info("Receipt printed successfully: {$sale->receipt_no}");
            return true;
        } catch (Exception $e) {
            return $this->handlePrintError('Receipt', $sale, $e);
        }
    }

    /**
     * Get printer connector based on type
     */
    protected function getConnector($printerType)
    {
        $printerConfig = $this->config['printers'][$printerType];
        $type = $printerConfig['type'];
        $connector = $printerConfig['connector'];

        switch ($type) {
            case 'network':
                [$host, $port] = explode(':', $connector);
                $connection = new NetworkPrintConnector($host, $port, $this->config['default_timeout']);
                break;

            case 'usb':
            case 'windows':
                // For Windows USB printers (e.g., "POS-80" printer name)
                $connection = new WindowsPrintConnector($connector);
                break;

            case 'file':
                // For Linux/Mac USB (e.g., "/dev/usb/lp0" or "/dev/ttyUSB0")
                $connection = new FilePrintConnector($connector);
                break;

            default:
                throw new Exception("Unsupported printer type: {$type}");
        }

        return new Printer($connection);
    }

    /**
     * Format and print KOT
     */
    protected function formatKOT(Printer $printer, Kot $kot)
    {
        $width = $this->config['settings']['paper_width'];

        // Header
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->setTextSize(2, 2);
        $printer->text("KITCHEN ORDER\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);
        $printer->text(str_repeat("=", $width) . "\n");

        // Order Info
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->setEmphasis(true);
        $printer->text("KOT NO: {$kot->kot_no}\n");
        $printer->setEmphasis(false);
        
        $printer->text("Branch: {$kot->branch->name}\n");
        $printer->text("Waiter: {$kot->user_name}\n");
        $printer->text("Date: " . $kot->created_at->format('d/m/Y H:i') . "\n");
        
        if ($kot->sale) {
            $printer->text("Sale: {$kot->sale->receipt_no}\n");
        }
        
        $printer->text(str_repeat("-", $width) . "\n");

        // Items
        $printer->setEmphasis(true);
        $printer->text($this->padText("ITEM", "QTY", $width - 4) . "\n");
        $printer->setEmphasis(false);
        $printer->text(str_repeat("-", $width) . "\n");

        foreach ($kot->kotItems as $item) {
            // Item name
            $printer->setEmphasis(true);
            $printer->setTextSize(1, 2);
            $printer->text(wordwrap($item->item_name, $width - 8, "\n", false) . "\n");
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);
            
            // Quantity
            $printer->text("    x {$item->quantity}\n");
            
            // Special instructions
            if ($item->special_instructions) {
                $printer->setEmphasis(true);
                $printer->text("    NOTE: " . wordwrap($item->special_instructions, $width - 10, "\n          ", false) . "\n");
                $printer->setEmphasis(false);
            }
            
            $printer->text("\n");
        }

        $printer->text(str_repeat("=", $width) . "\n");

        // Notes
        if ($kot->notes) {
            $printer->setEmphasis(true);
            $printer->text("NOTES:\n");
            $printer->setEmphasis(false);
            $printer->text(wordwrap($kot->notes, $width, "\n", false) . "\n");
            $printer->text(str_repeat("=", $width) . "\n");
        }

        // Footer
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("\n");
        $printer->setEmphasis(true);
        $printer->text("PREPARE IMMEDIATELY\n");
        $printer->setEmphasis(false);
        $printer->text("\n\n");

        // Cut paper
        if ($this->config['settings']['cut_paper']) {
            $printer->cut();
        }

        $printer->feed(2);
    }

    /**
     * Format and print BOT
     */
    protected function formatBOT(Printer $printer, Kot $bot)
    {
        $width = $this->config['settings']['paper_width'];

        // Header
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->setTextSize(2, 2);
        $printer->text("BAR ORDER\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);
        $printer->text(str_repeat("=", $width) . "\n");

        // Order Info
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->setEmphasis(true);
        $printer->text("BOT NO: {$bot->kot_no}\n");
        $printer->setEmphasis(false);
        
        $printer->text("Branch: {$bot->branch->name}\n");
        $printer->text("Waiter: {$bot->user_name}\n");
        $printer->text("Date: " . $bot->created_at->format('d/m/Y H:i') . "\n");
        
        if ($bot->sale) {
            $printer->text("Sale: {$bot->sale->receipt_no}\n");
        }
        
        $printer->text(str_repeat("-", $width) . "\n");

        // Items
        $printer->setEmphasis(true);
        $printer->text($this->padText("ITEM", "QTY", $width - 4) . "\n");
        $printer->setEmphasis(false);
        $printer->text(str_repeat("-", $width) . "\n");

        foreach ($bot->kotItems as $item) {
            // Item name
            $printer->setEmphasis(true);
            $printer->setTextSize(1, 2);
            $printer->text(wordwrap($item->item_name, $width - 8, "\n", false) . "\n");
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);
            
            // Quantity
            $printer->text("    x {$item->quantity}\n");
            
            // Special instructions
            if ($item->special_instructions) {
                $printer->setEmphasis(true);
                $printer->text("    NOTE: " . wordwrap($item->special_instructions, $width - 10, "\n          ", false) . "\n");
                $printer->setEmphasis(false);
            }
            
            $printer->text("\n");
        }

        $printer->text(str_repeat("=", $width) . "\n");

        // Notes
        if ($bot->notes) {
            $printer->setEmphasis(true);
            $printer->text("NOTES:\n");
            $printer->setEmphasis(false);
            $printer->text(wordwrap($bot->notes, $width, "\n", false) . "\n");
            $printer->text(str_repeat("=", $width) . "\n");
        }

        // Footer
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("\n");
        $printer->setEmphasis(true);
        $printer->text("PREPARE IMMEDIATELY\n");
        $printer->setEmphasis(false);
        $printer->text("\n\n");

        // Cut paper
        if ($this->config['settings']['cut_paper']) {
            $printer->cut();
        }

        $printer->feed(2);
    }

    /**
     * Format and print Receipt
     */
    protected function formatReceipt(Printer $printer, Sale $sale)
    {
        $width = $this->config['settings']['paper_width'];

        // Header
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->setTextSize(2, 1);
        $printer->text("RAVON RESTAURANT\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);
        $printer->text("{$sale->branch->name}\n");
        $printer->text("{$sale->branch->address}\n");
        if ($sale->branch->phone) {
            $printer->text("Tel: {$sale->branch->phone}\n");
        }
        $printer->text(str_repeat("=", $width) . "\n");

        // Receipt Info
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->setEmphasis(true);
        $printer->text("Receipt: {$sale->receipt_no}\n");
        $printer->setEmphasis(false);
        $printer->text("Date: " . $sale->created_at->format('d/m/Y H:i:s') . "\n");
        $printer->text("Cashier: {$sale->user->name}\n");
        $printer->text("Customer: {$sale->customer_name}\n");
        $printer->text(str_repeat("-", $width) . "\n");

        // Items
        $printer->text($this->padText("Item", "Qty x Price", $width - 12) . $this->rightAlign("Total", 12) . "\n");
        $printer->text(str_repeat("-", $width) . "\n");

        foreach ($sale->saleItems as $item) {
            $itemName = strlen($item->item_name) > $width - 12 ? 
                        substr($item->item_name, 0, $width - 15) . "..." : 
                        $item->item_name;
            
            $printer->text($itemName . "\n");
            
            $qtyPrice = "{$item->quantity} x " . number_format($item->unit_price, 2);
            $total = number_format($item->total_price, 2);
            
            $printer->text("  " . $this->padText($qtyPrice, "", $width - 14) . $this->rightAlign($total, 12) . "\n");
        }

        $printer->text(str_repeat("-", $width) . "\n");

        // Totals
        $printer->setEmphasis(true);
        $printer->text($this->padText("SUBTOTAL:", "", $width - 12) . $this->rightAlign(number_format($sale->subtotal, 2), 12) . "\n");
        
        if ($sale->discount > 0) {
            $printer->text($this->padText("DISCOUNT:", "", $width - 12) . $this->rightAlign("-" . number_format($sale->discount, 2), 12) . "\n");
        }
        
        if ($sale->tax > 0) {
            $printer->text($this->padText("TAX:", "", $width - 12) . $this->rightAlign(number_format($sale->tax, 2), 12) . "\n");
        }
        
        $printer->setTextSize(2, 2);
        $printer->text($this->padText("TOTAL:", "", ($width - 24) / 2) . $this->rightAlign(number_format($sale->total, 2), 24) . "\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);
        
        $printer->text(str_repeat("=", $width) . "\n");

        // Payment
        $printer->text($this->padText("Payment Method:", $sale->payment_method, $width) . "\n");
        
        if ($sale->payment_method === 'CASH' && $sale->customer_payment > 0) {
            $printer->text($this->padText("Cash Received:", "", $width - 12) . $this->rightAlign(number_format($sale->customer_payment, 2), 12) . "\n");
            $change = $sale->customer_payment - $sale->total;
            if ($change > 0) {
                $printer->setEmphasis(true);
                $printer->text($this->padText("CHANGE:", "", $width - 12) . $this->rightAlign(number_format($change, 2), 12) . "\n");
                $printer->setEmphasis(false);
            }
        } elseif ($sale->payment_method === 'CARD & CASH') {
            $printer->text($this->padText("Cash Payment:", "", $width - 12) . $this->rightAlign(number_format($sale->customer_payment, 2), 12) . "\n");
            $printer->text($this->padText("Card Payment:", "", $width - 12) . $this->rightAlign(number_format($sale->card_payment, 2), 12) . "\n");
        }

        $printer->text(str_repeat("=", $width) . "\n");

        // Footer
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("\nThank you for dining with us!\n");
        $printer->text("Please visit again\n\n");
        
        // QR Code or Barcode (optional)
        // $printer->qrCode($sale->receipt_no, Printer::QR_ECLEVEL_L, 6);
        
        $printer->text("\n");

        // Cut paper
        if ($this->config['settings']['cut_paper']) {
            $printer->cut();
        }

        $printer->feed(2);
    }

    /**
     * Pad text to align left and right
     */
    protected function padText($left, $right, $width)
    {
        $rightLen = strlen($right);
        $leftLen = strlen($left);
        $spaces = $width - $rightLen - $leftLen;
        
        if ($spaces < 1) {
            return $left . " " . $right;
        }
        
        return $left . str_repeat(" ", $spaces) . $right;
    }

    /**
     * Right align text
     */
    protected function rightAlign($text, $width)
    {
        $textLen = strlen($text);
        if ($textLen >= $width) {
            return $text;
        }
        return str_repeat(" ", $width - $textLen) . $text;
    }

    /**
     * Handle print errors
     */
    protected function handlePrintError($type, $document, Exception $e)
    {
        $error = "Failed to print {$type}: " . $e->getMessage();
        Log::error($error, [
            'document_id' => $document->id,
            'document_type' => get_class($document),
            'exception' => $e
        ]);

        // Save to file as fallback
        if ($this->config['fallback']['save_to_file']) {
            $this->savePrintJobToFile($type, $document);
        }

        return false;
    }

    /**
     * Save print job to file as fallback
     */
    protected function savePrintJobToFile($type, $document)
    {
        try {
            $directory = $this->config['fallback']['file_path'];
            
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $filename = strtolower($type) . '_' . $document->id . '_' . time() . '.txt';
            $filepath = $directory . DIRECTORY_SEPARATOR . $filename;

            $content = $this->generateTextContent($type, $document);
            file_put_contents($filepath, $content);

            Log::info("Print job saved to file: {$filepath}");
        } catch (Exception $e) {
            Log::error("Failed to save print job to file: " . $e->getMessage());
        }
    }

    /**
     * Generate text content for fallback
     */
    protected function generateTextContent($type, $document)
    {
        $content = "=================================================\n";
        $content .= strtoupper($type) . " PRINT JOB\n";
        $content .= "Generated: " . now()->format('Y-m-d H:i:s') . "\n";
        $content .= "=================================================\n\n";

        if ($document instanceof Kot) {
            $content .= "Order No: {$document->kot_no}\n";
            $content .= "Type: {$document->type}\n";
            $content .= "Branch: {$document->branch->name}\n";
            $content .= "Waiter: {$document->user_name}\n";
            $content .= "Date: " . $document->created_at->format('d/m/Y H:i') . "\n\n";
            
            $content .= "ITEMS:\n";
            $content .= "-------------------------------------------------\n";
            foreach ($document->kotItems as $item) {
                $content .= "- {$item->item_name} x {$item->quantity}\n";
                if ($item->special_instructions) {
                    $content .= "  Note: {$item->special_instructions}\n";
                }
            }
        } elseif ($document instanceof Sale) {
            $content .= "Receipt: {$document->receipt_no}\n";
            $content .= "Customer: {$document->customer_name}\n";
            $content .= "Cashier: {$document->user->name}\n";
            $content .= "Date: " . $document->created_at->format('d/m/Y H:i:s') . "\n\n";
            
            $content .= "ITEMS:\n";
            $content .= "-------------------------------------------------\n";
            foreach ($document->saleItems as $item) {
                $content .= "- {$item->item_name} x {$item->quantity} @ {$item->unit_price} = {$item->total_price}\n";
            }
            
            $content .= "\n-------------------------------------------------\n";
            $content .= "TOTAL: LKR " . number_format($document->total, 2) . "\n";
            $content .= "Payment: {$document->payment_method}\n";
        }

        return $content;
    }

    /**
     * Test printer connection
     */
    public function testPrinter($printerType)
    {
        try {
            $printer = $this->getConnector($printerType);
            
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->text("PRINTER TEST\n");
            $printer->setEmphasis(false);
            $printer->text(str_repeat("=", 48) . "\n");
            $printer->text("Printer: {$this->config['printers'][$printerType]['name']}\n");
            $printer->text("Type: {$this->config['printers'][$printerType]['type']}\n");
            $printer->text("Date: " . now()->format('Y-m-d H:i:s') . "\n");
            $printer->text(str_repeat("=", 48) . "\n");
            $printer->text("\nTest successful!\n\n");
            
            if ($this->config['settings']['cut_paper']) {
                $printer->cut();
            }
            
            $printer->close();
            
            return true;
        } catch (Exception $e) {
            Log::error("Printer test failed for {$printerType}: " . $e->getMessage());
            return false;
        }
    }
}
