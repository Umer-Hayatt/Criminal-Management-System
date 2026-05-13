<?php
include 'db.php';
$pageTitle = 'Register New Criminal';
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

 mysqli_begin_transaction($conn);
 try {

 // Helpers 
 function esc($conn, $v) { return mysqli_real_escape_string($conn, trim($v)); }
 function nullOrVal($v) { return trim($v) === '' ? 'NULL' : "'$v'"; }

 // 1. INSERT Criminal 
 $fn = esc($conn, $_POST['first_name']);
 $ln = esc($conn, $_POST['last_name']);
 $dob = esc($conn, $_POST['date_of_birth']);
 $gen = esc($conn, $_POST['gender']);
 $nat = esc($conn, $_POST['nationality']);
 $addr = esc($conn, $_POST['address']);
 $ph = esc($conn, $_POST['phone']);
 $st = esc($conn, $_POST['status']);

 mysqli_query($conn,
 "INSERT INTO Criminal(first_name,last_name,date_of_birth,gender,nationality,address,phone,status)
 VALUES('$fn','$ln','$dob','$gen','$nat','$addr','$ph','$st')"
 );
 $criminal_id = mysqli_insert_id($conn);

 // 2. INSERT Crime 
 $ctype = esc($conn, $_POST['crime_type']);
 $cdesc = esc($conn, $_POST['crime_description']);
 $cdate = esc($conn, $_POST['date_occurred']);
 $cloc = esc($conn, $_POST['crime_location']);
 $csev = esc($conn, $_POST['severity']);

 mysqli_query($conn,
 "INSERT INTO Crime(crime_type,description,date_occurred,location,severity)
 VALUES('$ctype','$cdesc','$cdate','$cloc','$csev')"
 );
 $crime_id = mysqli_insert_id($conn);

 // 3. INSERT Criminal_Crime (M:N) 
 $role = esc($conn, $_POST['role']);
 $adate = esc($conn, $_POST['arrest_date']);

 mysqli_query($conn,
 "INSERT INTO Criminal_Crime(criminal_id,crime_id,role,arrest_date)
 VALUES($criminal_id,$crime_id,'$role','$adate')"
 );

 // 4. INSERT Case_Record 
 $cstat = esc($conn, $_POST['case_status']);
 $copen = esc($conn, $_POST['open_date']);
 $ccdesc = esc($conn, $_POST['case_description']);

 mysqli_query($conn,
 "INSERT INTO Case_Record(crime_id,case_status,open_date,description)
 VALUES($crime_id,'$cstat','$copen','$ccdesc')"
 );
 $case_id = mysqli_insert_id($conn);

 // 5. INSERT Officer_Case (M:N) if officer selected 
 $officer_id = intval($_POST['officer_id']);
 if ($officer_id > 0) {
 $orole = esc($conn, $_POST['officer_role']);
 $odate = esc($conn, $_POST['open_date']);
 mysqli_query($conn,
 "INSERT INTO Officer_Case(officer_id,case_id,assigned_date,role)
 VALUES($officer_id,$case_id,'$odate','$orole')"
 );
 }

 // 6. INSERT Victim if name provided 
 $vfn = esc($conn, $_POST['victim_first_name']);
 $vln = esc($conn, $_POST['victim_last_name']);
 if ($vfn !== '') {
 $vage = intval($_POST['victim_age']);
 $vgen = esc($conn, $_POST['victim_gender']);
 $vph = esc($conn, $_POST['victim_phone']);
 $vstmt = esc($conn, $_POST['victim_statement']);
 mysqli_query($conn,
 "INSERT INTO Victim(crime_id,first_name,last_name,age,gender,contact_number,statement)
 VALUES($crime_id,'$vfn','$vln',$vage,'$vgen','$vph','$vstmt')"
 );
 }

 // 7. INSERT Court_Hearing if court name provided 
 $court = esc($conn, $_POST['court_name']);
 if ($court !== '') {
 $judge = esc($conn, $_POST['judge_name']);
 $hdate = esc($conn, $_POST['hearing_date']);
 $verdict = esc($conn, $_POST['verdict']);
 $nextd = nullOrVal(esc($conn, $_POST['next_hearing_date']));
 mysqli_query($conn,
 "INSERT INTO Court_Hearing(case_id,hearing_date,judge_name,verdict,court_name,next_hearing_date)
 VALUES($case_id,'$hdate','$judge','$verdict','$court',$nextd)"
 );
 }

 // 8. INSERT Imprisonment if prison selected 
 $prison_id = intval($_POST['prison_id']);
 if ($prison_id > 0) {
 $cell = esc($conn, $_POST['cell_number']);
 $syrs = intval($_POST['sentence_years']);
 $sdate = esc($conn, $_POST['sentence_start']);
 $edate = nullOrVal(esc($conn, $_POST['sentence_end']));
 mysqli_query($conn,
 "INSERT INTO Imprisonment(criminal_id,prison_id,cell_number,start_date,end_date,sentence_years)
 VALUES($criminal_id,$prison_id,'$cell','$sdate',$edate,$syrs)"
 );
 }

 mysqli_commit($conn);
 header("Location: profile.php?id=$criminal_id&new=1");
 exit();

 } catch (Exception $e) {
 mysqli_rollback($conn);
 $error = "Something went wrong: " . $e->getMessage();
 }
}

// Load officers & prisons for dropdowns
$officers = mysqli_query($conn, "SELECT * FROM Officer ORDER BY first_name");
$prisons = mysqli_query($conn, "SELECT * FROM Prison ORDER BY prison_name");

include 'header.php';
?>

<div class="page-hdr">
 <div>
 <h2> Register New Criminal</h2>
 <p>Fill in all sections below — everything will be saved in one go</p>
 </div>
 <a href="records.php" class="btn btn-gray">← Back to Records</a>
</div>

<?php if ($error): ?><div class="alert alert-error"> <?=$error?></div><?php endif; ?>

<form method="POST" action="">

<!-- SECTION 1: PERSONAL INFORMATION -->
<div class="form-section">
 <div class="form-section-title"> Section 1 — Personal Information</div>
 <div class="form-grid">
 <div class="form-group">
 <label>First Name *</label>
 <input type="text" name="first_name" placeholder="e.g. Ali" required>
 </div>
 <div class="form-group">
 <label>Last Name *</label>
 <input type="text" name="last_name" placeholder="e.g. Hassan" required>
 </div>
 <div class="form-group">
 <label>Date of Birth</label>
 <input type="date" name="date_of_birth">
 </div>
 <div class="form-group">
 <label>Gender</label>
 <select name="gender">
 <option value="Male">Male</option>
 <option value="Female">Female</option>
 </select>
 </div>
 <div class="form-group">
 <label>Nationality</label>
 <input type="text" name="nationality" placeholder="e.g. Pakistani">
 </div>
 <div class="form-group">
 <label>Phone Number</label>
 <input type="text" name="phone" placeholder="e.g. 0300-1234567">
 </div>
 <div class="form-group">
 <label>Criminal Status *</label>
 <select name="status" required>
 <option value="Wanted">Wanted</option>
 <option value="Imprisoned">Imprisoned</option>
 <option value="Released">Released</option>
 <option value="Under Trial">Under Trial</option>
 </select>
 </div>
 <div class="form-group full">
 <label>Home Address</label>
 <input type="text" name="address" placeholder="Full home address">
 </div>
 </div>
</div>

<!-- SECTION 2: CRIME DETAILS -->
<div class="form-section">
 <div class="form-section-title"> Section 2 — Crime Details</div>
 <div class="form-grid cols3">
 <div class="form-group">
 <label>Crime Type *</label>
 <select name="crime_type" required>
 <option value="">-- Select --</option>
 <option>Robbery</option>
 <option>Murder</option>
 <option>Fraud</option>
 <option>Drug Dealing</option>
 <option>Car Theft</option>
 <option>Kidnapping</option>
 <option>Assault</option>
 <option>Terrorism</option>
 <option>Cyber Crime</option>
 <option>Other</option>
 </select>
 </div>
 <div class="form-group">
 <label>Severity</label>
 <select name="severity">
 <option>Minor</option>
 <option>Major</option>
 <option>Felony</option>
 </select>
 </div>
 <div class="form-group">
 <label>Date Crime Occurred</label>
 <input type="date" name="date_occurred">
 </div>
 <div class="form-group">
 <label>Crime Location</label>
 <input type="text" name="crime_location" placeholder="City or area">
 </div>
 <div class="form-group">
 <label>Criminal's Role</label>
 <select name="role">
 <option>Main Accused</option>
 <option>Accomplice</option>
 <option>Suspect</option>
 </select>
 </div>
 <div class="form-group">
 <label>Arrest Date</label>
 <input type="date" name="arrest_date">
 </div>
 <div class="form-group full">
 <label>Crime Description</label>
 <textarea name="crime_description" placeholder="Describe what happened in detail..."></textarea>
 </div>
 </div>
</div>

<!-- SECTION 3: CASE DETAILS -->
<div class="form-section">
 <div class="form-section-title"> Section 3 — Case Details</div>
 <div class="form-grid">
 <div class="form-group">
 <label>Case Status *</label>
 <select name="case_status" required>
 <option value="Open">Open</option>
 <option value="Under Investigation">Under Investigation</option>
 <option value="Closed">Closed</option>
 </select>
 </div>
 <div class="form-group">
 <label>Case Open Date</label>
 <input type="date" name="open_date">
 </div>
 <div class="form-group full">
 <label>Case Description / Notes</label>
 <textarea name="case_description" placeholder="Additional notes about this case..."></textarea>
 </div>
 </div>
</div>

<!-- SECTION 4: ASSIGNED OFFICER -->
<div class="form-section">
 <div class="form-section-title"> Section 4 — Assigned Officer</div>
 <div class="form-grid">
 <div class="form-group">
 <label>Select Officer</label>
 <select name="officer_id">
 <option value="0">-- None / Not Assigned Yet --</option>
 <?php while($o=mysqli_fetch_assoc($officers)): ?>
 <option value="<?=$o['officer_id']?>">
 <?=$o['first_name'].' '.$o['last_name']?> — <?=$o['rank']?>, <?=$o['department']?>
 </option>
 <?php endwhile; ?>
 </select>
 </div>
 <div class="form-group">
 <label>Officer's Role on Case</label>
 <select name="officer_role">
 <option>Lead Investigator</option>
 <option>Assistant</option>
 <option>Observer</option>
 </select>
 </div>
 </div>
</div>

<!-- SECTION 5: VICTIM INFORMATION -->
<div class="form-section">
 <div class="form-section-title"> Section 5 — Victim Information <span style="font-weight:400;color:#7f8c8d;font-size:11px;">(Optional — leave blank if no victim info available)</span></div>
 <div class="form-grid">
 <div class="form-group">
 <label>Victim First Name</label>
 <input type="text" name="victim_first_name" placeholder="Leave blank if unknown">
 </div>
 <div class="form-group">
 <label>Victim Last Name</label>
 <input type="text" name="victim_last_name" placeholder="">
 </div>
 <div class="form-group">
 <label>Age</label>
 <input type="number" name="victim_age" min="1" max="120" placeholder="e.g. 35">
 </div>
 <div class="form-group">
 <label>Gender</label>
 <select name="victim_gender">
 <option>Male</option>
 <option>Female</option>
 </select>
 </div>
 <div class="form-group">
 <label>Contact Number</label>
 <input type="text" name="victim_phone" placeholder="e.g. 0300-1234567">
 </div>
 <div class="form-group full">
 <label>Victim's Statement</label>
 <textarea name="victim_statement" placeholder="What the victim said about the crime..."></textarea>
 </div>
 </div>
</div>

<!-- SECTION 6: COURT HEARING -->
<div class="form-section">
 <div class="form-section-title"> Section 6 — Court Hearing <span style="font-weight:400;color:#7f8c8d;font-size:11px;">(Optional — fill if a hearing has been scheduled)</span></div>
 <div class="form-grid">
 <div class="form-group">
 <label>Court Name</label>
 <input type="text" name="court_name" placeholder="e.g. Sessions Court Rawalpindi">
 </div>
 <div class="form-group">
 <label>Judge Name</label>
 <input type="text" name="judge_name" placeholder="e.g. Justice Anwar Kamal">
 </div>
 <div class="form-group">
 <label>Hearing Date</label>
 <input type="date" name="hearing_date">
 </div>
 <div class="form-group">
 <label>Current Verdict</label>
 <select name="verdict">
 <option value="Pending">Pending</option>
 <option value="Guilty">Guilty</option>
 <option value="Not Guilty">Not Guilty</option>
 </select>
 </div>
 <div class="form-group">
 <label>Next Hearing Date</label>
 <input type="date" name="next_hearing_date">
 </div>
 </div>
</div>

<!-- SECTION 7: IMPRISONMENT -->
<div class="form-section">
 <div class="form-section-title"> Section 7 — Imprisonment <span style="font-weight:400;color:#7f8c8d;font-size:11px;">(Optional — fill only if criminal is being imprisoned)</span></div>
 <div class="form-grid">
 <div class="form-group">
 <label>Select Prison</label>
 <select name="prison_id">
 <option value="0">-- Not Imprisoned --</option>
 <?php
 // Reset pointer since we looped officers above
 $prisons = mysqli_query($conn, "SELECT * FROM Prison ORDER BY prison_name");
 while($p=mysqli_fetch_assoc($prisons)):
 ?>
 <option value="<?=$p['prison_id']?>"><?=$p['prison_name']?> (<?=$p['location']?>)</option>
 <?php endwhile; ?>
 </select>
 </div>
 <div class="form-group">
 <label>Cell Number</label>
 <input type="text" name="cell_number" placeholder="e.g. B-204">
 </div>
 <div class="form-group">
 <label>Sentence (Years)</label>
 <input type="number" name="sentence_years" min="1" placeholder="e.g. 10">
 </div>
 <div class="form-group">
 <label>Sentence Start Date</label>
 <input type="date" name="sentence_start">
 </div>
 <div class="form-group">
 <label>Sentence End Date</label>
 <input type="date" name="sentence_end">
 </div>
 </div>
</div>

<!-- SUBMIT -->
<div style="display:flex;gap:12px;align-items:center;margin-top:8px;">
 <button type="submit" class="btn btn-primary" style="padding:13px 36px;font-size:15px;">
 Register Criminal Record
 </button>
 <a href="records.php" class="btn btn-gray" style="padding:13px 20px;">Cancel</a>
 <span style="color:#7f8c8d;font-size:12px;">All sections marked with * are required</span>
</div>

</form>

<?php include 'footer.php'; ?>
