<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

function convertToCompanyCurrency($pdo, $amount, $from_currency, $to_currency) {
    if ($from_currency === $to_currency) {
        return ['amount' => $amount, 'rate' => 1];
    }
    
    $config = include __DIR__ . '/config.php';
    $rates = getRates($pdo, $from_currency);
    
    if (!$rates || !isset($rates['rates'][$to_currency])) {
        return ['amount' => $amount, 'rate' => 1, 'error' => 'Currency conversion failed'];
    }
    
    $rate = $rates['rates'][$to_currency];
    $converted_amount = $amount * $rate;
    
    return [
        'amount' => round($converted_amount, 2),
        'rate' => $rate
    ];
}

function getRates($pdo, $base) {
    $config = include __DIR__ . '/config.php';
    
    $stmt = $pdo->prepare("SELECT rates, fetched_at FROM exchange_rates WHERE base_currency = ?");
    $stmt->execute([$base]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row && (time() - strtotime($row['fetched_at']) < 86400)) {
        return json_decode($row['rates'], true);
    }
    
    $json = file_get_contents($config['currency_api'] . $base);
    $data = json_decode($json, true);
    
    if ($data && isset($data['rates'])) {
        $stmt = $pdo->prepare("REPLACE INTO exchange_rates (base_currency, rates, fetched_at) VALUES (?, ?, NOW())");
        $stmt->execute([$base, json_encode($data)]);
        return $data;
    }
    
    return $row ? json_decode($row['rates'], true) : null;
}

function parseOCRText($text) {
    $result = [
        'amount' => '',
        'date' => '',
        'merchant' => ''
    ];
    
    // Extract amount - look for currency patterns
    if (preg_match('/(\d{1,3}(?:[.,]\d{2,})?)/', $text, $matches)) {
        $result['amount'] = str_replace(',', '', $matches[1]);
    }
    
    // Extract date - various formats
    if (preg_match('/(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4})/', $text, $matches)) {
        $result['date'] = $matches[1];
    }
    
    // Extract merchant - look for common patterns
    $lines = explode("\n", $text);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strlen($line) > 3 && !preg_match('/^\d/', $line) && !preg_match('/total|subtotal|tax/i', $line)) {
            $result['merchant'] = $line;
            break;
        }
    }
    
    return $result;
}

function getCountries() {
    $config = include __DIR__ . '/config.php';
    $json = file_get_contents($config['countries_api']);
    $countries = json_decode($json, true);
    
    $result = [];
    foreach ($countries as $country) {
        if (isset($country['currencies'])) {
            $currency = array_keys($country['currencies'])[0];
            $result[] = [
                'name' => $country['name']['common'],
                'currency' => $currency
            ];
        }
    }
    
    return $result;
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateAmount($amount) {
    return is_numeric($amount) && $amount > 0;
}

function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}
