<?php
require_once 'config.php';
$data=$json=json_decode(file_get_contents('php://input'),true);
$email=trim($data['email']??''); $password=$data['password']??'';
if ($email===''||$password==='') { echo json_encode(['success'=>false,'message'=>'Email and password required.']); exit; }
$stmt=$conn->prepare("SELECT user_id,full_name,email,role,department,password_hash FROM tblUsers WHERE email=? LIMIT 1");
$stmt->bind_param('s',$email); $stmt->execute();
$user=$stmt->get_result()->fetch_assoc(); $stmt->close();
if (!$user) { echo json_encode(['success'=>false,'message'=>'No account found.']); exit; }
if (!password_verify($password,$user['password_hash'])) { echo json_encode(['success'=>false,'message'=>'Incorrect password.']); exit; }
unset($user['password_hash']);
echo json_encode(['success'=>true,'user'=>$user]);
$conn->close();
