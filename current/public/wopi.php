<?php
/**
 * Minimal WOPI endpoint for Collabora Online
 * Handles CheckFileInfo, GetFile, and PutFile
 */

$basePath = '/var/www/prevencion/docs/';

// Parse the request URI
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Expected URL patterns:
// GET  /wopi/files/<encoded_path>          -> CheckFileInfo
// GET  /wopi/files/<encoded_path>/contents  -> GetFile  
// POST /wopi/files/<encoded_path>/contents  -> PutFile

if (preg_match('#^/wopi/files/(.+?)/contents$#', $uri, $matches)) {
    $filePath = $basePath . urldecode($matches[1]);
    
    if ($method === 'GET') {
        // GetFile - return file contents
        if (file_exists($filePath)) {
            header('Content-Type: application/octet-stream');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            http_response_code(404);
            echo "File not found: " . $filePath;
            exit;
        }
    } elseif ($method === 'POST') {
        // PutFile - save file
        $content = file_get_contents('php://input');
        file_put_contents($filePath, $content);
        http_response_code(200);
        echo json_encode(['LastModifiedTime' => date('c', filemtime($filePath))]);
        exit;
    }
} elseif (preg_match('#^/wopi/files/(.+?)$#', $uri, $matches)) {
    $relativePath = urldecode($matches[1]);
    $filePath = $basePath . $relativePath;
    
    if ($method === 'GET') {
        // CheckFileInfo
        if (file_exists($filePath)) {
            $info = [
                'BaseFileName' => basename($filePath),
                'Size' => filesize($filePath),
                'OwnerId' => 'admin',
                'UserId' => 'admin',
                'UserFriendlyName' => 'Admin',
                'UserCanWrite' => true,
                'ReadOnly' => false,
                'SupportsLocks' => false,
                'SupportsUpdate' => true,
                'UserCanNotWriteRelative' => true,
                'LastModifiedTime' => date('c', filemtime($filePath)),
            ];
            header('Content-Type: application/json');
            echo json_encode($info);
            exit;
        } else {
            http_response_code(404);
            echo "File not found";
            exit;
        }
    }
}

http_response_code(400);
echo "Bad WOPI request";
