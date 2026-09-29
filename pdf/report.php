<?php
declare(strict_types=1);
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';
require_once '../lib/SimplePdf.php';

$patientId=(int)($_GET['patient_id']??0);
if($patientId<=0){http_response_code(400);exit('Patient ID requis.');}
$db=getDB();
$stmt=$db->prepare("SELECT p.*,ps.label status_label FROM patients p JOIN patient_statuses ps ON ps.id=p.status_id WHERE p.id=?");
$stmt->execute([$patientId]);$patient=$stmt->fetch();
if(!$patient){http_response_code(404);exit('Patient introuvable.');}
$stmt=$db->prepare("SELECT * FROM appointments WHERE patient_id=? ORDER BY appointment_date DESC LIMIT 12");$stmt->execute([$patientId]);$appointments=$stmt->fetchAll();
$stmt=$db->prepare("SELECT * FROM teeth_records WHERE patient_id=? ORDER BY tooth_number");$stmt->execute([$patientId]);$teeth=$stmt->fetchAll();
$toothMap=[];foreach($teeth as $t)$toothMap[(int)$t['tooth_number']]=$t;
$stmt=$db->prepare("SELECT * FROM patient_notes WHERE patient_id=? ORDER BY created_at DESC LIMIT 12");$stmt->execute([$patientId]);$notes=$stmt->fetchAll();
$stmt=$db->prepare("SELECT * FROM attachments WHERE patient_id=? ORDER BY uploaded_at DESC");$stmt->execute([$patientId]);$attachments=$stmt->fetchAll();

$pdf=new SimplePdf();
$pdf->setFillColor(37,99,235);$pdf->rect(14,14,182,22,true);
$pdf->setTextColor(255,255,255);$pdf->setFont('Helvetica','B',16);$pdf->text(20,28,'DentaFlow - Rapport patient');
$pdf->setTextColor(100,116,139);$pdf->setFont('Helvetica','',8);$pdf->text(150,43,'Généré le '.date('d/m/Y H:i'));

 $actionSection = function(string $title) use ($pdf, &$y) {
  if($y>255){$pdf->addPage();$y=18;}
  $pdf->setTextColor(15,23,42);$pdf->setFont('Helvetica','B',12);$pdf->text(14,$y,$title);$pdf->setDrawColor(226,232,240);$pdf->line(14,$y+3,196,$y+3);$y+=10;
};
$y=55;
$actionSection('Informations du patient');
$pdf->setFont('Helvetica','B',9);$pdf->setTextColor(71,85,105);
$info=[['Nom complet',$patient['full_name']],['Téléphone',$patient['phone']],['Assurance',$patient['insurance']?:'Aucune'],['Statut',$patient['status_label']],['Patient depuis',formatDate($patient['created_at'])]];
foreach($info as [$l,$v]){$pdf->text(16,$y,$l.':');$pdf->setFont('Helvetica','',9);$pdf->setTextColor(15,23,42);$pdf->text(55,$y,(string)$v);$pdf->setFont('Helvetica','B',9);$pdf->setTextColor(71,85,105);$y+=6;}
$y+=5;
$actionSection('Carte dentaire');
$stateLabel=['healthy'=>'Saine','needs_intervention'=>'Intervention nécessaire','in_progress'=>'Traitement en cours','treated'=>'Traitée'];
$stateColor=['healthy'=>[248,250,252],'needs_intervention'=>[254,226,226],'in_progress'=>[254,243,199],'treated'=>[220,252,231]];
$upper=[18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28];$lower=[48,47,46,45,44,43,42,41,31,32,33,34,35,36,37,38];
foreach([['Arcade supérieure',$upper],['Arcade inférieure',$lower]] as [$label,$numbers]){
  $pdf->setFont('Helvetica','B',8);$pdf->setTextColor(100,116,139);$pdf->text(16,$y,$label);$y+=5;
  $x=16;foreach($numbers as $num){$state=$toothMap[$num]['state']??'healthy';$c=$stateColor[$state];$pdf->setFillColor(...$c);$pdf->setDrawColor(203,213,225);$pdf->rect($x,$y,10,9,true);$pdf->setTextColor(15,23,42);$pdf->setFont('Helvetica','B',7);$pdf->text($x+2,$y+6,(string)$num);$x+=11;}
  $y+=14;
}
$pdf->setFont('Helvetica','',7);$pdf->setTextColor(71,85,105);$legendX=16;foreach(['healthy','needs_intervention','in_progress','treated'] as $s){$pdf->setFillColor(...$stateColor[$s]);$pdf->rect($legendX,$y,6,5,true);$pdf->setTextColor(71,85,105);$pdf->text($legendX+8,$y+4,$stateLabel[$s]);$legendX+=48;}$y+=12;
if(count($teeth)){foreach($teeth as $t){if($y>270){$pdf->addPage();$y=18;}$pdf->setFont('Helvetica','B',8);$pdf->setTextColor(15,23,42);$pdf->text(16,$y,'Dent '.$t['tooth_number']);$pdf->setFont('Helvetica','',8);$pdf->setTextColor(71,85,105);$line=$stateLabel[$t['state']]??$t['state'];if($t['treatment'])$line.=' - '.$t['treatment'];if($t['record_date'])$line.=' ('.$t['record_date'].')';$pdf->text(38,$y,$line);$y+=5;}}
$y+=6;
$actionSection('Historique des rendez-vous');
if(!$appointments){$pdf->setFont('Helvetica','I',9);$pdf->setTextColor(100,116,139);$pdf->text(16,$y,'Aucun rendez-vous enregistré.');$y+=8;}else{foreach($appointments as $a){if($y>275){$pdf->addPage();$y=18;}$pdf->setFont('Helvetica','B',8);$pdf->setTextColor(15,23,42);$pdf->text(16,$y,formatDate($a['appointment_date'],true));$pdf->setFont('Helvetica','',8);$pdf->setTextColor(71,85,105);$pdf->text(57,$y,($a['reason']?:'Consultation').' - '.$a['status']);$y+=5;}}
$y+=6;$actionSection('Notes cliniques');
if(!$notes){$pdf->setFont('Helvetica','I',9);$pdf->setTextColor(100,116,139);$pdf->text(16,$y,'Aucune note.');$y+=8;}else{foreach($notes as $n){if($y>270){$pdf->addPage();$y=18;}$pdf->setFont('Helvetica','B',8);$pdf->setTextColor(71,85,105);$pdf->text(16,$y,formatDate($n['created_at'],true));$y+=5;$pdf->setFont('Helvetica','',8);$pdf->setTextColor(15,23,42);$pdf->multiText(16,$y,178,$n['content'],4.5);$y+=3;}}
$y+=6;$actionSection('Pièces jointes');
if(!$attachments){$pdf->setFont('Helvetica','I',9);$pdf->setTextColor(100,116,139);$pdf->text(16,$y,'Aucune pièce jointe.');}else{foreach($attachments as $a){if($y>275){$pdf->addPage();$y=18;}$pdf->setFont('Helvetica','',8);$pdf->setTextColor(15,23,42);$pdf->text(16,$y,$a['original_name']);$pdf->setTextColor(100,116,139);$pdf->text(120,$y,strtoupper($a['file_type']).' - '.formatFileSize((int)$a['file_size']));$y+=5;}}

$filename='Rapport_'.preg_replace('/[^A-Za-z0-9_-]+/','_',str_replace(' ','_',$patient['full_name'])).'.pdf';
$pdf->outputDownload($filename);
