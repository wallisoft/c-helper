<?php
/**
 * c-helper - Universal AI Development Proxy
 * 
 * Enables AI assistants to directly edit, build, and interact with any codebase
 * via a simple HTTP API. No uploads, no context window bloat, just pure efficiency.
 * 
 * @author Steve Wallis & Claude (Anthropic)
 * @license MIT
 * @version 1.0.0
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/c-helper-errors.log');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Configuration
define('DB_PATH', getenv('C_HELPER_DB') ?: __DIR__ . '/c-helper.db');
define('API_KEY', getenv('C_HELPER_KEY') ?: 'dev-' . time());
define('BASE_DIR', getenv('C_HELPER_BASE') ?: dirname(__DIR__));

// Log function
function log_message($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message);
}

// Verify API key
$providedKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($providedKey !== API_KEY) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid API key']);
    log_message("Invalid API key attempt: " . $providedKey);
    exit;
}

// Initialize database
try {
    $db = new SQLite3(DB_PATH);
    $db->busyTimeout(5000);
    
    $db->exec('CREATE TABLE IF NOT EXISTS files (
        project TEXT NOT NULL,
        path TEXT NOT NULL,
        content TEXT,
        updated_at INTEGER DEFAULT (strftime("%s", "now")),
        PRIMARY KEY (project, path)
    )');
    
    $db->exec('CREATE TABLE IF NOT EXISTS projects (
        name TEXT PRIMARY KEY,
        base_path TEXT NOT NULL,
        created_at INTEGER DEFAULT (strftime("%s", "now"))
    )');
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    log_message("Database error: " . $e->getMessage());
    exit;
}

// Route handler
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$query = $_GET;
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($uri) {
        case '/api/file':
            handleFile($db, $method, $query);
            break;
        case '/api/sync':
            handleSync($db, $query);
            break;
        case '/api/build':
            handleBuild($db, $query);
            break;
        case '/api/exec':
            handleExec($db, $method);
            break;
        case '/api/projects':
            handleProjects($db, $method);
            break;
        case '/health':
            echo json_encode(['status' => 'ok', 'version' => '1.0.0', 'time' => time()]);
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found: ' . $uri]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    log_message("Error in $uri: " . $e->getMessage());
}

function handleFile($db, $method, $query) {
    $project = $query['project'] ?? null;
    $path = $query['path'] ?? null;
    
    if (!$project || !$path) {
        throw new Exception('Missing project or path parameter');
    }
    
    if ($method === 'GET') {
        $stmt = $db->prepare('SELECT content FROM files WHERE project = ? AND path = ?');
        $stmt->bindValue(1, $project, SQLITE3_TEXT);
        $stmt->bindValue(2, $path, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
        if (!$result) {
            http_response_code(404);
            echo json_encode(['error' => 'File not found']);
            return;
        }
        
        echo json_encode(['content' => $result['content']]);
        
    } elseif ($method === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON: ' . json_last_error_msg());
        }
        
        $content = $input['content'] ?? '';
        
        $stmt = $db->prepare('INSERT OR REPLACE INTO files (project, path, content, updated_at) VALUES (?, ?, ?, ?)');
        $stmt->bindValue(1, $project, SQLITE3_TEXT);
        $stmt->bindValue(2, $path, SQLITE3_TEXT);
        $stmt->bindValue(3, $content, SQLITE3_TEXT);
        $stmt->bindValue(4, time(), SQLITE3_INTEGER);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'File stored in database',
            'project' => $project,
            'path' => $path,
            'size' => strlen($content)
        ]);
        
    } elseif ($method === 'DELETE') {
        $stmt = $db->prepare('DELETE FROM files WHERE project = ? AND path = ?');
        $stmt->bindValue(1, $project, SQLITE3_TEXT);
        $stmt->bindValue(2, $path, SQLITE3_TEXT);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'File deleted']);
    }
}

function handleSync($db, $query) {
    $project = $query['project'] ?? null;
    if (!$project) {
        throw new Exception('Missing project parameter');
    }
    
    $projectPath = getProjectPath($db, $project);
    if (!$projectPath) {
        throw new Exception('Project not found: ' . $project);
    }
    
    $stmt = $db->prepare('SELECT path, content FROM files WHERE project = ?');
    $stmt->bindValue(1, $project, SQLITE3_TEXT);
    $result = $stmt->execute();
    
    $synced = [];
    $errors = [];
    
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $fullPath = $projectPath . '/' . $row['path'];
        $dir = dirname($fullPath);
        
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                $errors[] = "Failed to create directory: $dir";
                continue;
            }
        }
        
        if (file_put_contents($fullPath, $row['content']) !== false) {
            $synced[] = $row['path'];
        } else {
            $errors[] = "Failed to write: " . $row['path'];
        }
    }
    
    echo json_encode([
        'success' => true,
        'synced' => $synced,
        'errors' => $errors,
        'count' => count($synced)
    ]);
}

function handleBuild($db, $query) {
    $project = $query['project'] ?? null;
    if (!$project) {
        throw new Exception('Missing project parameter');
    }
    
    $projectPath = getProjectPath($db, $project);
    if (!$projectPath) {
        throw new Exception('Project not found');
    }
    
    // Detect build system
    $buildCmd = null;
    if (file_exists("$projectPath/package.json")) {
        $buildCmd = 'npm run build';
    } elseif (glob("$projectPath/*.csproj")) {
        $buildCmd = 'dotnet build';
    } elseif (file_exists("$projectPath/Makefile")) {
        $buildCmd = 'make';
    } elseif (file_exists("$projectPath/build.sh")) {
        $buildCmd = './build.sh';
    }
    
    if (!$buildCmd) {
        throw new Exception('No build system detected');
    }
    
    $startTime = microtime(true);
    $output = [];
    $exitCode = 0;
    
    exec("cd '$projectPath' && $buildCmd 2>&1", $output, $exitCode);
    $duration = round((microtime(true) - $startTime) * 1000);
    
    echo json_encode([
        'success' => $exitCode === 0,
        'stdout' => implode("\n", $output),
        'stderr' => '',
        'exit_code' => $exitCode,
        'duration_ms' => $duration
    ]);
}

function handleExec($db, $method) {
    if ($method !== 'POST') {
        throw new Exception('Method not allowed');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }
    
    $project = $input['project'] ?? null;
    $command = $input['command'] ?? null;
    
    if (!$command) {
        throw new Exception('Missing command');
    }
    
    $workDir = BASE_DIR;
    if ($project) {
        $workDir = getProjectPath($db, $project);
        if (!$workDir) {
            throw new Exception('Project not found');
        }
    }
    
    $startTime = microtime(true);
    $output = [];
    $exitCode = 0;
    
    // Execute command
    $fullCommand = "cd '$workDir' && $command 2>&1";
    exec($fullCommand, $output, $exitCode);
    $duration = round((microtime(true) - $startTime) * 1000);
    
    echo json_encode([
        'success' => $exitCode === 0,
        'stdout' => implode("\n", $output),
        'stderr' => '',
        'exit_code' => $exitCode,
        'duration_ms' => $duration
    ]);
}

function handleProjects($db, $method) {
    if ($method === 'GET') {
        $result = $db->query('SELECT name, base_path FROM projects');
        $projects = [];
        
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $projects[] = $row;
        }
        
        echo json_encode(['projects' => $projects]);
        
    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON: ' . json_last_error_msg());
        }
        
        $name = $input['name'] ?? null;
        $path = $input['path'] ?? null;
        
        if (!$name || !$path) {
            throw new Exception('Missing name or path');
        }
        
        if (!is_dir($path)) {
            throw new Exception('Path does not exist: ' . $path);
        }
        
        $stmt = $db->prepare('INSERT OR REPLACE INTO projects (name, base_path) VALUES (?, ?)');
        $stmt->bindValue(1, $name, SQLITE3_TEXT);
        $stmt->bindValue(2, realpath($path), SQLITE3_TEXT);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Project registered']);
    }
}

function getProjectPath($db, $project) {
    $stmt = $db->prepare('SELECT base_path FROM projects WHERE name = ?');
    $stmt->bindValue(1, $project, SQLITE3_TEXT);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    return $result ? $result['base_path'] : null;
}
