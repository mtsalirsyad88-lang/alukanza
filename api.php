<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// --- KONFIGURASI DATABASE (EDIT DISINI) ---
$db_host = "localhost";
$db_user = "root";      // Ganti dengan User Database cPanel
$db_pass = "";          // Ganti dengan Password Database cPanel
$db_name = "mts_akademik"; // Ganti dengan Nama Database

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die(json_encode(["error" => "Koneksi Gagal: " . $conn->connect_error]));
}

// Menangkap Request
$method = $_SERVER['REQUEST_METHOD'];
$action = "";

if ($method === 'GET') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $action = isset($input['action']) ? $input['action'] : '';
    $data = isset($input['data']) ? $input['data'] : null;
}

// --- ROUTING ---
switch ($action) {
    case 'getAllData':
        echo json_encode(getAllData($conn));
        break;
    case 'batchSave':
        echo json_encode(batchSave($conn, $data));
        break;
    case 'createData':
        // createData di frontend mengirim object tunggal, kita bungkus array agar sama dengan batchSave
        echo json_encode(batchSave($conn, [$data]));
        break;
    case 'deleteData':
        echo json_encode(deleteData($conn, $data['id']));
        break;
    default:
        echo json_encode(["message" => "API MTs Al Irsyad Ready"]);
        break;
}

// --- FUNGSI ---

function getAllData($conn) {
    $sql = "SELECT * FROM data_akademik ORDER BY date DESC, created_at DESC";
    $result = $conn->query($sql);
    
    $rows = [];
    while($r = $result->fetch_assoc()) {
        // Mapping agar sesuai dengan format Frontend yang mengharapkan __backendId
        $r['__backendId'] = $r['id']; 
        $rows[] = $r;
    }
    return $rows;
}

function batchSave($conn, $records) {
    if (!$records || !is_array($records)) return getAllData($conn);

    foreach ($records as $r) {
        $id = isset($r['__backendId']) ? $r['__backendId'] : null;
        
        // Sanitasi data dasar
        $type = $conn->real_escape_string($r['type'] ?? '');
        $date = $conn->real_escape_string($r['date'] ?? date('Y-m-d'));
        $class = $conn->real_escape_string($r['class_name'] ?? '');
        $subject = $conn->real_escape_string($r['subject'] ?? '');
        $student = $conn->real_escape_string($r['student_name'] ?? '');
        
        // Data optional
        $status = $conn->real_escape_string($r['status'] ?? '');
        $topic = $conn->real_escape_string($r['topic'] ?? '');
        $notes = $conn->real_escape_string($r['notes'] ?? '');
        $g_val = $conn->real_escape_string($r['grade_value'] ?? '');
        $g_type = $conn->real_escape_string($r['grade_type'] ?? '');
        $g_title = $conn->real_escape_string($r['grade_title'] ?? '');
        $proof = $conn->real_escape_string($r['proof_image'] ?? '');

        // Cek apakah ID ada dan valid di DB
        $exists = false;
        if ($id) {
            $check = $conn->query("SELECT id FROM data_akademik WHERE id = '$id'");
            if ($check && $check->num_rows > 0) $exists = true;
        }

        if ($exists) {
            // UPDATE
            $sql = "UPDATE data_akademik SET 
                    status='$status', notes='$notes', grade_value='$g_val', 
                    grade_type='$g_type', grade_title='$g_title'";
            
            // Hanya update foto jika ada data foto baru (tidak kosong)
            if (!empty($proof)) {
                $sql .= ", proof_image='$proof'";
            }
            
            $sql .= " WHERE id='$id'";
            $conn->query($sql);

        } else {
            // INSERT
            $sql = "INSERT INTO data_akademik 
                    (type, date, class_name, subject, student_name, status, topic, notes, grade_value, grade_type, grade_title, proof_image)
                    VALUES 
                    ('$type', '$date', '$class', '$subject', '$student', '$status', '$topic', '$notes', '$g_val', '$g_type', '$g_title', '$proof')";
            $conn->query($sql);
        }
    }
    
    return getAllData($conn);
}

function deleteData($conn, $id) {
    if ($id) {
        $cleanId = $conn->real_escape_string($id);
        $conn->query("DELETE FROM data_akademik WHERE id = '$cleanId'");
    }
    return getAllData($conn);
}
?>
