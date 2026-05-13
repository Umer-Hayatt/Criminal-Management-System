<?php
include 'db.php';
$id = intval($_GET['id']);
$criminal_id = intval($_GET['cid'] ?? 0);
$error = '';

if (!$id) { header("Location: records.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status  = mysqli_real_escape_string($conn, $_POST['case_status']);
    $close   = !empty($_POST['close_date']) ? "'".$_POST['close_date']."'" : "NULL";
    $desc    = mysqli_real_escape_string($conn, $_POST['description']);

    $sql = "UPDATE Case_Record
            SET case_status='$status', close_date=$close, description='$desc'
            WHERE case_id=$id";

    if(mysqli_query($conn, $sql)) {
        $back = $criminal_id ? "profile.php?id=$criminal_id" : "records.php";
        header("Location: $back&msg=Case+updated+successfully");
        exit();
    } else {
        $error = mysqli_error($conn);
    }
}

$row = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT c.*, cr.crime_type FROM Case_Record c
     JOIN Crime cr ON c.crime_id = cr.crime_id
     WHERE c.case_id = $id"
));
if (!$row) { die("Case not found."); }

$pageTitle = 'Edit Case #'.$id;
include 'header.php';
?>

<div class="page-hdr">
    <div>
        <h2><i data-lucide="folder-open" style="width:22px;height:22px;vertical-align:-4px;color:var(--indigo);"></i> Edit Case #<?=$id?></h2>
        <p>Update investigation status and case notes</p>
    </div>
    <a href="<?=$criminal_id?"profile.php?id=$criminal_id":"records.php"?>" class="btn btn-gray">← Back</a>
</div>

<?php if($error): ?>
<div class="alert alert-error"><i data-lucide="alert-circle"></i> <?=$error?></div>
<?php endif; ?>

<!-- Read-only Crime Info -->
<div class="card" style="border-left:3px solid var(--indigo);">
    <div class="card-title"><i data-lucide="info"></i> Case Information (Read-Only)</div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
        <div>
            <div style="font-size:11px;color:var(--txt-soft);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;">Case ID</div>
            <div style="font-weight:600;">#<?=str_pad($id,4,'0',STR_PAD_LEFT)?></div>
        </div>
        <div>
            <div style="font-size:11px;color:var(--txt-soft);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;">Related Crime</div>
            <div style="font-weight:600;"><?=htmlspecialchars($row['crime_type'])?></div>
        </div>
        <div>
            <div style="font-size:11px;color:var(--txt-soft);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;">Opened</div>
            <div style="font-weight:600;"><?=$row['open_date']?></div>
        </div>
    </div>
</div>

<!-- Editable Fields -->
<div class="card">
    <div class="card-title"><i data-lucide="edit"></i> Update Case Details</div>
    <form method="POST">
        <div class="form-section">
            <div class="form-section-title"><i data-lucide="activity"></i> Case Status & Outcome</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Case Status *</label>
                    <select name="case_status" required>
                        <?php foreach(['Open','Under Investigation','Closed'] as $s): ?>
                        <option value="<?=$s?>" <?=$row['case_status']==$s?'selected':''?>><?=$s?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="font-size:11px;color:var(--txt-soft);margin-top:3px;">
                        Set to "Closed" only when the case is fully resolved.
                    </small>
                </div>
                <div class="form-group">
                    <label>Date Closed</label>
                    <input type="date" name="close_date" value="<?=htmlspecialchars($row['close_date']??'')?>">
                    <small style="font-size:11px;color:var(--txt-soft);margin-top:3px;">
                        Leave blank if case is still active.
                    </small>
                </div>
                <div class="form-group full">
                    <label>Case Description / Investigation Notes</label>
                    <textarea name="description" rows="4"><?=htmlspecialchars($row['description']??'')?></textarea>
                </div>
            </div>
        </div>
        <div style="display:flex;gap:10px;align-items:center;">
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save"></i> Save Changes
            </button>
            <a href="<?=$criminal_id?"profile.php?id=$criminal_id":"records.php"?>" class="btn btn-gray">Cancel</a>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
