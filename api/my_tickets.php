<?php
require_once 'config.php';
$userId=(int)($_GET['user_id']??0);
if ($userId===0) { echo json_encode(['success'=>false,'message'=>'user_id required.']); exit; }
$stmt=$conn->prepare("SELECT ticket_id,ticket_ref,category,priority,status,description,location,device_type,date_opened FROM tblTickets WHERE user_id=? ORDER BY date_opened DESC");
$stmt->bind_param('i',$userId); $stmt->execute();
$tickets=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo json_encode(['success'=>true,'tickets'=>$tickets]);
$stmt->close(); $conn->close();
