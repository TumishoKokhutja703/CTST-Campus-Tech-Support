<?php
require_once 'config.php';
$data       =json_decode(file_get_contents('php://input'),true);
$userId     =(int)($data['user_id']    ??0);
$category   =trim($data['category']   ??'');
$priority   =trim($data['priority']   ??'Medium');
$description=trim($data['description']??'');
$location   =trim($data['location']   ??'');
$deviceType =trim($data['device_type']??'');
if ($userId===0||$category===''||$description==='') { echo json_encode(['success'=>false,'message'=>'Missing required fields.']); exit; }
$ref=$conn->query("SELECT COUNT(*) AS t FROM tblTickets")->fetch_assoc();
$ticketRef='TKT-'.str_pad((int)$ref['t']+1,3,'0',STR_PAD_LEFT);
$stmt=$conn->prepare("INSERT INTO tblTickets (ticket_ref,user_id,category,priority,status,description,location,device_type) VALUES (?,?,?,?,'Open',?,?,?)");
$stmt->bind_param('sisssss',$ticketRef,$userId,$category,$priority,$description,$location,$deviceType);
echo json_encode($stmt->execute()?['success'=>true,'ticket_ref'=>$ticketRef,'message'=>'Ticket submitted.']:['success'=>false,'message'=>'Could not submit ticket.']);
$stmt->close(); $conn->close();
