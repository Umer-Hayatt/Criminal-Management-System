<?php
include 'db.php';
require_login();
if($_SESSION['role'] !== 'viewer') {
 header('Location: index.php');
 exit();
}
$pageTitle = 'Report a Crime';
$errors = [];

if($_SERVER['REQUEST_METHOD']==='POST'){
 $ctype = esc($conn, $_POST['crime_type'] ?? '');
 $desc  = esc($conn, $_POST['description'] ?? '');
 $date  = esc($conn, $_POST['date_occurred'] ?? '');
 $loc   = esc($conn, $_POST['location'] ?? '');
 $sev   = esc($conn, $_POST['severity'] ?? 'Minor');
 
 $fname = esc($conn, $_POST['first_name'] ?? '');
 $lname = esc($conn, $_POST['last_name'] ?? '');
 $phone = esc($conn, $_POST['phone'] ?? '');
 $contact = esc($conn, $_POST['contact_number'] ?? '');
 $vstat = esc($conn, $_POST['victim_statement'] ?? '');
 
 if(!$ctype) $errors[] = 'Crime type is required.';
 if(!$loc) $errors[] = 'Crime location is required.';
 if(!$date) $errors[] = 'Date of occurrence is required.';
 
 if(empty($errors)){
  mysqli_begin_transaction($conn);
  try {
   mysqli_query($conn, "INSERT INTO Crime(crime_type, description, date_occurred, location, severity) VALUES('$ctype', '$desc', '$date', '$loc', '$sev')");
   $crime_id = mysqli_insert_id($conn);
   
   if($fname && $lname) {
    mysqli_query($conn, "INSERT INTO Victim(crime_id, first_name, last_name, contact_number, statement) VALUES($crime_id, '$fname', '$lname', '$contact', '$vstat')");
   }
   
   mysqli_commit($conn);
   log_activity('Created', 'Crime Report', $crime_id, "Crime report submitted: $ctype at $loc");
   set_flash('success', 'Crime report submitted successfully. Officers will review and investigate.');
   header('Location: index.php');
   exit();
  } catch(Exception $e) {
   mysqli_rollback($conn);
   $errors[] = 'Failed to submit report. Please try again.';
  }
 }
}

include 'header.php';
?>
<div class="breadcrumb">
 <a href="index.php">Dashboard</a>
 <i data-lucide="chevron-right"></i>
 <span>Report a Crime</span>
</div>

<div class="page-hdr">
 <div class="page-hdr-left">
  <div class="page-hdr-icon"><i data-lucide="alert-triangle"></i></div>
  <div><h2>Report a Crime</h2><p>Submit information about a crime incident for investigation</p></div>
 </div>
 <a href="index.php" class="btn btn-ghost"><i data-lucide="arrow-left"></i> Back</a>
</div>

<?php foreach($errors as $e): ?><div class="flash flash-error"><i data-lucide="alert-circle"></i><?=htmlspecialchars($e)?></div><?php endforeach; ?>

<div class="form-section">
 <div class="form-section-title"><i data-lucide="alert-triangle"></i> Crime Details</div>
 <form method="POST">
  <div class="form-grid cols3">
   <div class="form-group"><label>Crime Type *</label>
    <select name="crime_type" required>
     <option value="">-- Select --</option>
     <?php foreach(['Robbery','Murder','Fraud','Drug Dealing','Car Theft','Kidnapping','Assault','Terrorism','Cyber Crime','Other'] as $t): ?>
     <option><?=$t?></option>
     <?php endforeach; ?>
    </select>
   </div>
   <div class="form-group"><label>Severity</label>
    <select name="severity"><option>Minor</option><option>Major</option><option>Felony</option></select>
   </div>
   <div class="form-group"><label>Date Occurred *</label><input type="date" name="date_occurred" required></div>
   <div class="form-group"><label>Location *</label><input type="text" name="location" placeholder="City, area, or specific address" required></div>
   <div class="form-group full"><label>Description of Crime</label><textarea name="description" placeholder="Provide details about what happened..." rows="4"></textarea></div>
  </div>
  
  <div class="form-section-title"><i data-lucide="user"></i> Your Information (Optional)</div>
  <p style="font-size:12px;color:var(--txt-soft);margin-bottom:16px;">Providing your information helps officers contact you for follow-up questions.</p>
  
  <div class="form-grid cols3">
   <div class="form-group"><label>First Name</label><input type="text" name="first_name" placeholder="Your first name"></div>
   <div class="form-group"><label>Last Name</label><input type="text" name="last_name" placeholder="Your last name"></div>
   <div class="form-group"><label>Contact Number</label><input type="tel" name="contact_number" placeholder="Phone number"></div>
   <div class="form-group full"><label>Your Statement</label><textarea name="victim_statement" placeholder="Any additional details or observations..." rows="3"></textarea></div>
  </div>
  
  <div style="display:flex;gap:10px;margin-top:16px">
   <button type="submit" class="btn btn-primary"><i data-lucide="send"></i> Submit Report</button>
   <a href="index.php" class="btn btn-ghost">Cancel</a>
  </div>
 </form>
</div>

<div class="form-section" style="background:rgba(191,186,255,.08);border:1px solid rgba(139,92,246,.2);">
 <div class="form-section-title" style="color:var(--violet)"><i data-lucide="shield-alert"></i> Important Information</div>
 <ul style="font-size:13px;line-height:1.8;color:var(--txt-soft)">
  <li>• Your report will be reviewed by law enforcement officers</li>
  <li>• Provide as much detail as possible to help the investigation</li>
  <li>• Your contact information will only be used for follow-up inquiries</li>
  <li>• Anonymous reports are also accepted if you prefer not to provide your details</li>
  <li>• For emergencies, please contact emergency services immediately</li>
 </ul>
</div>

<?php include 'footer.php'; ?>
