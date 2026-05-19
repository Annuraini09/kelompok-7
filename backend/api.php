<?php
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_REQUEST['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Session handling
session_start();

// ============ ADMIN ENDPOINTS ============

if ($action === 'admin_login' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = trim($data['username'] ?? '');
    $password = trim($data['password'] ?? '');

    if (!$username || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Username dan password wajib diisi']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT id, username FROM admins WHERE username = ? AND password = ?');
    $stmt->execute([$username, md5($password)]);
    $admin = $stmt->fetch();

    if ($admin) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['role'] = 'admin';
        echo json_encode(['success' => true, 'message' => 'Login berhasil', 'admin_id' => $admin['id']]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Username atau password salah']);
    }
    exit;
}

if ($action === 'admin_create_voter' && $method === 'POST') {
    if (!isset($_SESSION['admin_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Anda harus login sebagai admin']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $nik = trim($data['nik'] ?? '');
    $name = trim($data['name'] ?? '');
    $password = trim($data['password'] ?? '');
    $rt = trim($data['rt'] ?? '');
    $rw = trim($data['rw'] ?? '');

    if (!$nik || !$name || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'NIK, nama, dan password wajib diisi']);
        exit;
    }

    try {
        // Check if voter already exists
        $stmt = $pdo->prepare('SELECT id FROM voters WHERE nik = ?');
        $stmt->execute([$nik]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['error' => 'NIK sudah terdaftar']);
            exit;
        }

        // Create voter
        $stmt = $pdo->prepare('INSERT INTO voters (nik, name, password, rt, rw) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$nik, $name, md5($password), $rt, $rw]);
        
        echo json_encode(['success' => true, 'message' => 'Akun warga berhasil dibuat', 'voter_id' => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal membuat akun', 'detail' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'admin_get_voters' && $method === 'GET') {
    if (!isset($_SESSION['admin_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Anda harus login sebagai admin']);
        exit;
    }

    $sql = "SELECT id, nik, name, rt, rw, has_voted, created_at FROM voters ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    echo json_encode($stmt->fetchAll());
    exit;
}

// ============ VOTER ENDPOINTS ============

if ($action === 'voter_login' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $nik = trim($data['nik'] ?? '');
    $password = trim($data['password'] ?? '');

    if (!$nik || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'NIK dan password wajib diisi']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT id, nik, name, rt, rw, has_voted FROM voters WHERE nik = ? AND password = ?');
    $stmt->execute([$nik, md5($password)]);
    $voter = $stmt->fetch();

    if ($voter) {
        $_SESSION['voter_id'] = $voter['id'];
        $_SESSION['nik'] = $voter['nik'];
        $_SESSION['voter_name'] = $voter['name'];
        $_SESSION['role'] = 'voter';
        echo json_encode(['success' => true, 'message' => 'Login berhasil', 'voter_id' => $voter['id'], 'has_voted' => (bool)$voter['has_voted']]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'NIK atau password salah']);
    }
    exit;
}

// ============ CANDIDATE ENDPOINTS ============

if ($action === 'candidate_login' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $candidate_id = (int)($data['candidate_id'] ?? 0);
    $password = trim($data['password'] ?? '');

    if (!$candidate_id || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'ID calon dan password wajib diisi']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT id, name, description, is_active FROM candidates WHERE id = ? AND password = ?');
    $stmt->execute([$candidate_id, md5($password)]);
    $candidate = $stmt->fetch();

    if ($candidate && $candidate['is_active']) {
        $_SESSION['candidate_id'] = $candidate['id'];
        $_SESSION['candidate_name'] = $candidate['name'];
        $_SESSION['role'] = 'candidate';
        echo json_encode(['success' => true, 'message' => 'Login berhasil', 'candidate_id' => $candidate['id']]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'ID calon atau password salah']);
    }
    exit;
}

if ($action === 'get_candidate_profile') {
    if (!isset($_SESSION['candidate_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Anda harus login sebagai calon']);
        exit;
    }

    $candidate_id = $_SESSION['candidate_id'];
    
    $sql = "SELECT c.id, c.name, c.description, c.photo_url, IFNULL(v.cnts, 0) AS votes
            FROM candidates c
            LEFT JOIN (SELECT candidate_id, COUNT(*) cnts FROM votes GROUP BY candidate_id) v
            ON c.id = v.candidate_id
            WHERE c.id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$candidate_id]);
    $profile = $stmt->fetch();

    if ($profile) {
        echo json_encode(['success' => true, 'profile' => $profile]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Profil calon tidak ditemukan']);
    }
    exit;
}

if ($action === 'list_candidates') {
    $sql = "SELECT c.id, c.name, c.description, c.photo_url, IFNULL(v.cnts,0) AS votes
            FROM candidates c
            WHERE c.is_active = 1
            LEFT JOIN (SELECT candidate_id, COUNT(*) cnts FROM votes GROUP BY candidate_id) v
            ON c.id = v.candidate_id
            ORDER BY c.id";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll();
    echo json_encode($rows);
    exit;
}

if ($action === 'results') {
    $sql = "SELECT c.id, c.name, IFNULL(v.cnts,0) AS votes
            FROM candidates c
            LEFT JOIN (SELECT candidate_id, COUNT(*) cnts FROM votes GROUP BY candidate_id) v
            ON c.id = v.candidate_id
            ORDER BY votes DESC";
    $stmt = $pdo->query($sql);
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($action === 'check_voter') {
    $nik = $_GET['nik'] ?? '';
    if (!$nik) { echo json_encode(['error'=>'nik required']); exit; }
    $stmt = $pdo->prepare('SELECT has_voted FROM voters WHERE nik = ?');
    $stmt->execute([$nik]);
    $row = $stmt->fetch();
    echo json_encode(['exists' => (bool)$row, 'has_voted' => $row ? (bool)$row['has_voted'] : false]);
    exit;
}

if ($action === 'submit_vote' && $method === 'POST') {
    if (!isset($_SESSION['voter_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Anda harus login untuk voting']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $candidate_id = (int)($data['candidate_id'] ?? 0);
    $voter_id = $_SESSION['voter_id'];

    if (!$candidate_id) {
        http_response_code(400);
        echo json_encode(['error' => 'candidate_id wajib diisi']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Check if voter has already voted
        $stmt = $pdo->prepare('SELECT id, has_voted FROM voters WHERE id = ? FOR UPDATE');
        $stmt->execute([$voter_id]);
        $voter = $stmt->fetch();

        if ($voter['has_voted']) {
            $pdo->rollBack();
            echo json_encode(['error' => 'Anda sudah melakukan vote']);
            exit;
        }

        // Record the vote
        $stmt = $pdo->prepare('INSERT INTO votes (voter_id, candidate_id) VALUES (?, ?)');
        $stmt->execute([$voter_id, $candidate_id]);

        // Update voter has_voted status
        $stmt = $pdo->prepare('UPDATE voters SET has_voted = 1, voted_at = NOW() WHERE id = ?');
        $stmt->execute([$voter_id]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Vote berhasil tercatat']);
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Server error', 'detail' => $e->getMessage()]);
        exit;
    }
}

if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logout berhasil']);
    exit;
}

// Default: unknown action
http_response_code(400);
echo json_encode(['error' => 'Invalid action']);

