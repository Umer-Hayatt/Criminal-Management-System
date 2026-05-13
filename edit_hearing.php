<?php
include 'db.php';
$id = intval($_GET['id']);
$criminal_id = intval($_GET['cid'] ?? 0);
$error = '';

if (!$id) { header("Location: records.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $verdict   = mysqli_real_escape_string($conn, $_POST['verdict']);
    $judge     = mysqli_real_escape_string($conn, $_POST['judge_name']);
    $court     = mysqli_real_escape_string($conn, $_POST['court_name']);
    $hdate     = mysqli_real_escape_string($conn, $_POST['hearing_date']);
    $nextdate  = !empty($_POST['next_hearing_date'])
                    ? "'".$_POST['next_hearing_date']."'" : "NULL";

    $sql = "UPDATE Court_Hearing
            SET verdict='$verdict', judge_name='$judge',
                court_name='$court', hearing_date='$hdate',
                next_hearing_date=$nextdate
            WHERE hearing_id=$id";

    if(mysqli_query($conn, $sql)) {
        $back = $criminal_id ? "profile.php?id=$criminal_id" : "records.php";
        header("Location: $back&msg=Hearing+updated");
        exit();
    } else {
        $error = mysqli_error($conn);
    }
}

$row = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT h.*, c.description as case_desc
     FROM Court_Hearing h
     JOIN Case_Record c ON h.case_id = c.case_id
     WHERE h.hearing_id = $id"
));
if (!$row) { die("Hearing not found."); }

$pageTitle = 'Edit Hearing #'.$id;
include 'header.php';
?>

<div class="page-hdr">
    <div>
        <h2><i data-lucide="scale" style="width:22px;height:22px;vertical-align:-4px;color:var(--indigo);"></i> Edit Hearing #<?=$id?></h2>
        <p>Update court hearing details and verdict</p>
    </div>
    <a href="<?=$criminal_id?"profile.php?id=$criminal_id":"records.php"?>" class="btn btn-gray">← Back</a>
</div>

<?php if($error): ?>
<div class="alert alert-error"><i data-lucide="alert-circle"></i> <?=$error?></div>
<?php endif; ?>

<!-- Context -->
<div class="card" style="border-left:3px solid var(--sky);">
    <div class="card-title"><i data-lucide="info"></i> Hearing Context</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div>
            <div style="font-size:11px;color:var(--txt-soft);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;">Hearing ID</div>
            <div style="font-weight:600;">#<?=$id?></div>
        </div>
        <div>
            <div style="font-size:11px;color:var(--txt-soft);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;">Case</div>
            <div style="font-weight:600;">Case #<?=$row['case_id']?> — <?=htmlspecialchars(substr($row['case_desc'],0,40))?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-title"><i data-lucide="edit"></i> Update Hearing Details</div>
    <form method="POST">
        <div class="form-section">
            <div class="form-section-title"><i data-lucide="gavel"></i> Court Information</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Court Name</label>
                    <input type="text" name="court_name" value="<?=htmlspecialchars($row['court_name'])?>" required>
                </div>
                <div class="form-group">
                    <label>Judge Name</label>
                    <input type="text" name="judge_name" value="<?=htmlspecialchars($row['judge_name'])?>">
                </div>
                <div class="form-group">
                    <label>Hearing Date</label>
                    <input type="date" name="hearing_date" value="<?=$row['hearing_date']?>">
                </div>
                <div class="form-group">
                    <label>Next Hearing Date</label>
                    <input type="date" name="next_hearing_date" value="<?=$row['next_hearing_date']??''?>">
                    <small style="font-size:11px;color:var(--txt-soft);margin-top:3px;">Leave blank if no further hearing is scheduled.</small>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title"><i data-lucide="check-circle"></i> Verdict</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Current Verdict *</label>
                    <select name="verdict" required>
                        <?php foreach(['Pending','Guilty','Not Guilty'] as $v): ?>
                        <option value="<?=$v?>" <?=$row['verdict']==$v?'selected':''?>><?=$v?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="font-size:11px;color:var(--txt-soft);margin-top:3px;">
                        Update to "Guilty" or "Not Guilty" once the judge has delivered judgment.
                    </small>
                </div>
            </div>

            <!-- Verdict visual indicator -->
            <div style="margin-top:14px;padding:12px 16px;border-radius:8px;background:var(--surface-alt);border:1px solid var(--border);font-size:13px;color:var(--txt-mid);">
                <i data-lucide="info" style="width:14px;height:14px;vertical-align:-2px;"></i>
                <strong>Tip:</strong> Changing the verdict to "Guilty" should only be done after official court confirmation.
                Consider also updating the Criminal's status to "Imprisoned" if a custodial sentence is given.
            </div>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save"></i> Save Changes
            </button>
            <a href="<?=$criminal_id?"profile.php?id=$criminal_id":"records.php"?>" class="btn btn-gray">Cancel</a>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
