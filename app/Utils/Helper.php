<?php
class Helper {
    
    public static function formatBytes($size, $precision = 2){
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for($i = 0; $size > 1024 && $i < count($units) - 1; $i++){
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }

    public static function timeAgo($datetime){
        $time = time() - strtotime($datetime);
        
        if($time < 60) return 'just now';
        if($time < 3600) return floor($time/60) . ' minutes ago';
        if($time < 86400) return floor($time/3600) . ' hours ago';
        if($time < 2592000) return floor($time/86400) . ' days ago';
        if($time < 31536000) return floor($time/2592000) . ' months ago';
        
        return floor($time/31536000) . ' years ago';
    }

    public static function formatDuration($seconds){
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
        
        if($hours > 0){
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        } else {
            return sprintf('%02d:%02d', $minutes, $seconds);
        }
    }

    public static function generateSlug($text){
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }

    public static function truncateText($text, $length = 100, $suffix = '...'){
        if(strlen($text) <= $length){
            return $text;
        }
        
        return substr($text, 0, $length) . $suffix;
    }

    public static function formatCurrency($amount, $currency = 'USD'){
        return '$' . number_format($amount, 2);
    }

    public static function generateRandomString($length = 10){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for($i = 0; $i < $length; $i++){
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }

    public static function isValidDate($date, $format = 'Y-m-d'){
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public static function arrayToCSV($array, $filename = 'export.csv'){
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        if(!empty($array)){
            // Write header
            fputcsv($output, array_keys((array)$array[0]));
            
            // Write data
            foreach($array as $row){
                fputcsv($output, (array)$row);
            }
        }
        
        fclose($output);
    }

    public static function sendEmail($to, $subject, $message, $headers = []){
        $defaultHeaders = [
            'From' => 'noreply@' . $_SERVER['HTTP_HOST'],
            'Reply-To' => 'noreply@' . $_SERVER['HTTP_HOST'],
            'Content-Type' => 'text/html; charset=UTF-8'
        ];
        
        $headers = array_merge($defaultHeaders, $headers);
        $headerString = '';
        
        foreach($headers as $key => $value){
            $headerString .= $key . ': ' . $value . "\r\n";
        }
        
        return mail($to, $subject, $message, $headerString);
    }

    public static function redirect($url, $statusCode = 302){
        http_response_code($statusCode);
        header('Location: ' . $url);
        exit();
    }

    public static function jsonResponse($data, $statusCode = 200){
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    public static function getClientIP(){
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach($ipKeys as $key){
            if(array_key_exists($key, $_SERVER) === true){
                foreach(explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip);
                    
                    if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    public static function generateUUID(){
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public static function validateRequired($data, $required = []){
        $errors = [];
        
        foreach($required as $field){
            if(!isset($data[$field]) || empty(trim($data[$field]))){
                $errors[$field] = ucfirst($field) . ' is required';
            }
        }
        
        return $errors;
    }

    public static function paginate($totalItems, $currentPage, $itemsPerPage = 10){
        $totalPages = ceil($totalItems / $itemsPerPage);
        $currentPage = max(1, min($currentPage, $totalPages));
        $offset = ($currentPage - 1) * $itemsPerPage;
        
        return [
            'total_items' => $totalItems,
            'total_pages' => $totalPages,
            'current_page' => $currentPage,
            'items_per_page' => $itemsPerPage,
            'offset' => $offset,
            'has_previous' => $currentPage > 1,
            'has_next' => $currentPage < $totalPages,
            'previous_page' => $currentPage > 1 ? $currentPage - 1 : null,
            'next_page' => $currentPage < $totalPages ? $currentPage + 1 : null
        ];
    }

    public static function debugLog($data, $label = 'DEBUG'){
        $logger = new Logger();
        $logger->debug($label, is_array($data) || is_object($data) ? (array)$data : ['value' => $data]);
    }
}
?>