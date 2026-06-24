<?php

namespace App\Services;

use App\Models\Product;

class EmailFormatterService
{
    public function formatEmailBody(string $body, array $data = []): string
    {
        $body = $this->renderTemplateTags($body, $data);

        $body = $this->textToHtml($body);

        return $this->wrapInTemplate($body, $data);
    }

    private function renderTemplateTags(string $body, array $data): string
    {
        if (str_contains($body, '{{products}}')) {
            $productsHtml = $this->renderActiveProducts();
            $body = str_replace('{{products}}', $productsHtml, $body);
        }

        $replacements = [
            '{{name}}' => $data['name'] ?? 'there',
            '{{email}}' => $data['email'] ?? '',
            '{{date}}' => now()->format('F j, Y'),
            '{{year}}' => now()->format('Y'),
            '{{unsubscribe}}' => $data['unsubscribe'] ?? '#',
            '{{download_token}}' => $data['download_token'] ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $body);
    }

    private function textToHtml(string $text): string
    {
        if ($this->isAlreadyHtml($text)) {
            return $text;
        }

        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        $text = $this->convertUrlsToLinks($text);

        $lines = explode("\n", $text);
        $html = '';
        $inList = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (empty($trimmed)) {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                continue;
            }

            if (preg_match('/^[✓✅•*]\s+(.+)/', $trimmed, $m)) {
                if (!$inList) {
                    $html .= "<ul style=\"margin: 12px 0; padding-left: 20px;\">\n";
                    $inList = true;
                }
                $html .= "  <li style=\"margin-bottom: 8px; line-height: 1.6;\">{$m[1]}</li>\n";
                continue;
            }

            if (preg_match('/^(\d+)\.\s+(.+)/', $trimmed, $m)) {
                if (!$inList) {
                    $html .= "<ol style=\"margin: 12px 0; padding-left: 20px;\">\n";
                    $inList = true;
                }
                $html .= "  <li style=\"margin-bottom: 8px; line-height: 1.6;\">{$m[2]}</li>\n";
                continue;
            }

            if (preg_match('/^—\s+(.+)/', $trimmed, $m)) {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                $html .= "<p style=\"margin: 12px 0; line-height: 1.7; color: #555; font-style: italic;\">— {$m[1]}</p>\n";
                continue;
            }

            if ($inList) {
                $html .= "</ul>\n";
                $inList = false;
            }

            if (preg_match('/^✓\s/', $trimmed)) {
                continue;
            }

            if (str_starts_with($trimmed, '--') || str_starts_with($trimmed, '———')) {
                $html .= "<hr style=\"border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;\">\n";
                continue;
            }

            if (preg_match('/^https?:\/\//', $trimmed)) {
                $html .= "<p style=\"margin: 12px 0; text-align: center;\"><a href=\"{$trimmed}\" style=\"color: #6366f1; word-break: break-all;\">{$trimmed}</a></p>\n";
                continue;
            }

            $html .= "<p style=\"margin: 12px 0; line-height: 1.7;\">{$trimmed}</p>\n";
        }

        if ($inList) {
            $html .= "</ul>\n";
        }

        return $html;
    }

    private function isAlreadyHtml(string $text): bool
    {
        return preg_match('/<\w+[^>]*>/', $text) === 1;
    }

    private function convertUrlsToLinks(string $text): string
    {
        return preg_replace_callback(
            '/(https?:\/\/[^\s<>"\'()]+)/i',
            function ($matches) {
                $url = $matches[1];
                return $url;
            },
            $text
        );
    }

    private function renderActiveProducts(): string
    {
        try {
            $products = Product::where('is_active', true)
                ->where('price', '>', 0)
                ->orderBy('order')
                ->orderBy('title')
                ->get();
        } catch (\Exception $e) {
            return '';
        }

        if ($products->isEmpty()) {
            return '';
        }

        $html = '<table cellpadding="0" cellspacing="0" style="width:100%;margin:20px 0;">';
        $html .= '<tr><td style="padding-bottom:12px;"><h3 style="color:#1e293b;font-size:16px;margin:0;">Our Digital Products</h3></td></tr>';

        foreach ($products as $product) {
            $price = $product->sale_price ?: $product->price;
            $priceFormatted = '₦' . number_format($price, 0);
            $url = url('/store/' . $product->slug);

            $html .= '<tr>';
            $html .= '<td style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin-bottom:12px;display:block;">';
            $html .= '<table cellpadding="0" cellspacing="0" style="width:100%;">';
            $html .= '<tr>';
            $html .= '<td style="vertical-align:middle;">';
            $html .= '<strong style="color:#1e293b;font-size:15px;">' . e($product->title) . '</strong>';
            if ($product->short_description) {
                $html .= '<p style="color:#64748b;font-size:13px;margin:4px 0 0 0;">' . e($product->short_description) . '</p>';
            }
            $html .= '</td>';
            $html .= '<td style="text-align:right;vertical-align:middle;white-space:nowrap;padding-left:12px;">';
            $html .= '<span style="color:#6366f1;font-weight:bold;font-size:15px;">' . $priceFormatted . '</span>';
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td colspan="2" style="padding-top:10px;">';
            $html .= '<a href="' . $url . '" style="display:inline-block;background:#6366f1;color:#fff;text-decoration:none;padding:8px 20px;border-radius:6px;font-size:13px;font-weight:600;">View Details</a>';
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '</table>';
            $html .= '</td>';
            $html .= '</tr>';
        }

        $html .= '<tr><td style="padding-top:8px;text-align:center;">';
        $html .= '<a href="' . url('/store') . '" style="color:#6366f1;font-size:13px;text-decoration:none;">Browse all products →</a>';
        $html .= '</td></tr>';
        $html .= '</table>';

        return $html;
    }

    private function wrapInTemplate(string $bodyHtml, array $data = []): string
    {
        $name = $data['name'] ?? 'there';
        $year = now()->format('Y');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JoAla Ventures</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #334155; margin: 0; padding: 0; background-color: #f1f5f9;">
    <table cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;margin:0 auto;background-color:#f1f5f9;">
        <tr>
            <td style="padding:30px 20px 10px 20px;text-align:center;">
                <a href="https://joala.com.ng" style="text-decoration:none;">
                    <span style="font-size:22px;font-weight:800;color:#1e293b;letter-spacing:-0.5px;">JoAla</span>
                    <span style="font-size:22px;font-weight:300;color:#6366f1;">Ventures</span>
                </a>
            </td>
        </tr>
        <tr>
            <td style="padding:10px 20px 30px 20px;">
                <table cellpadding="0" cellspacing="0" style="width:100%;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="padding:36px 32px 32px 32px;font-size:15px;color:#334155;">
                            {$bodyHtml}
                            <hr style="border:none;border-top:1px solid #e2e8f0;margin:28px 0 20px 0;">
                            <p style="margin:0;font-size:13px;color:#94a3b8;line-height:1.5;">
                                Best regards,<br>
                                <strong style="color:#475569;">Jome</strong><br>
                                JoAla Ventures
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding:0 20px 30px 20px;text-align:center;">
                <p style="margin:0 0 8px 0;font-size:12px;color:#94a3b8;">
                    <a href="https://joala.com.ng/store" style="color:#6366f1;text-decoration:none;margin:0 8px;">Store</a>
                    <span style="color:#cbd5e1;">|</span>
                    <a href="https://joala.com.ng/contact" style="color:#6366f1;text-decoration:none;margin:0 8px;">Contact</a>
                    <span style="color:#cbd5e1;">|</span>
                    <a href="{{unsubscribe}}" style="color:#6366f1;text-decoration:none;margin:0 8px;">Unsubscribe</a>
                </p>
                <p style="margin:0;font-size:11px;color:#94a3b8;">
                    &copy; {$year} JoAla Ventures. All rights reserved.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
}
