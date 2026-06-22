<?php
require_once 'config.php';
$data       = json_decode(file_get_contents('php://input'), true);
$fullName   = trim($data['full_name']  ?? '');
$email      = trim($data['email']      ?? '');
$password   = trim($data['password']   ?? '');
$role       = trim($data['role']       ?? 'Student');
$department = trim($data['department'] ?? '');
if ($fullName===''||$email===''||$password==='') { echo json_encode(['success'=>false,'message'=>'Fill in all fields.']); exit; }
$chk = $conn->prepare("SELECT user_id FROM tblUsers WHERE email=?");
$chk->bind_param('s',$email); $chk->execute(); $chk->store_result();
if ($chk->num_rows>0) { echo json_encode(['success'=>false,'message'=>'Email already registered.']); exit; }
$chk->close();
password_hash($password,PASSWORD_DEFAULT);
$stmt=$conn->prepare("INSERT INTO tblUsers (full_name,email,password_hash,role,department) VALUES (?,?,?,?,?)");
$stmt->bind_param('sssss',$fullName,$email,$hash,$role,$department);
echo json_encode($stmt->execute()?['success'=>true,'message'=>'Account created.']:['success'=>false,'message'=>'Registration failed.']);
$stmt->close(); $conn->close();
