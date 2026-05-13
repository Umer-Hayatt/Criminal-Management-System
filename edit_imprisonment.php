<?php
include 'db.php';
$id = intval($_GET['id']);
$criminal_id = intval($_GET['cid'] ?? 0);
$error = '';

if (!$id) { header("Location: records.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prison_id = intval($_POST['prison_id']);
    $cell      = mysqli_real_escape_string($conn, $_POST['cell_number']);
    $start     = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end       = !empty($_POST['end_date']) ? "'".$_POST['end_date']."'" : "NULL";
    $years     = intval($_POST['sentence_years']);

    $sql = "UPDATE Imprisonment
            SET prison_id=$prison_id, cell_number='$cell',
                start_date='$start', end_date=$end, sentence_years=$years
            WHERE imprisonment_id=$id";

    if(mysqli_query($conn, $sql)) {
        $back = $criminal_id ? "profile.php?id=$criminal_id" : "records.php";
        header("Location: $back&msg=Imprisonment+record+updated");
        exit();
    } else {
        $error = mysqli_error($conn);
    }
}

$row = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT i.*, p.prison_name, CONCAT(c.first_name,' ',c.last_name) as cname
     FROM Imprisonment i
     JOIN Prison p ON i.prison_id = p.prison_id
     JOIN Criminal c ON i.criminal_id = c.criminal_id
     WHERE i.imprisonment_id = $id"
));
if (!$row) { die("Imprisonment record not found."); }

$prisons = mysqli_query($conn,"SELECT * FROM Prison ORDER BY prison_name");
$pageTitle = 'Edit Imprisonment Record';
include 'header.php';
?>

<div class="page-hdr">
    <div>
        <h2><i data-lucide="building" style="width:22px;height:22px;vertical-align:-4px;color:var(--indigo);"></i> Edit Imprisonment Record</h2>
        <p>Update sentence and prison assignment for <?=htmlspecialchars($row['cname'])?></p>
    </div>
    <a href="<?=$criminal_id?"profile.php?id=$criminal_id":"records.php"?>" class="btn btn-gray">← Back</a>
</div>

<?php if($error): ?>
<div class="alert alert-error"><i data-lucide="alert-circle"></i> <?=$error?></div>
<?php endif; ?>

<!-- Current Info -->
<div class="card" style="border-left:3px solid var(--rose);">
    <div class="card-title"><i data-lucide="info"></i> Current Record</div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;">
        <div>
            <div style="font-size:11px;color:var(--txt-soft);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;">Criminal</div>
            <div style="font-weight:600;"><?=htmlspecialchars($row['cname'])?></div>
        </div>
        <div>
            <div style="font-size:11px;color:var(--txt-soft);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;">Prison</div>
            <div style="font-weight:600;"><?=htmlspecialchars($row['prison_name'])?></div>
        </div>
        <div>
            <div style="font-size:11px;color:var(--txt-soft);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;">Current Sentence</div>
            <div style="font-weight:600;"><?=$row['sentence_years']?> years</div>
        </div>
        <div>
            <div style="font-size:11px;color:var(--txt-soft);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;">Cell</div>
            <div style="font-weight:600;"><?=htmlspecialchars($row['cell_number'])?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-title"><i data-lucide="edit"></i> Update Imprisonment Details</div>
    <form method="POST">
        <div class="form-section">
            <div class="form-section-title"><i data-lucide="building"></i> Prison Assignment</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Prison *</label>
                    <select name="prison_id" required>
                        <?php
                        while($p=mysqli_fetch_assoc($prisons)):
                        ?>
                        <option value="<?=$p['prison_id']?>" <?=$row['prison_id']==$p['prison_id']?'selected':''?>>
                            <?=htmlspecialchars($p['prison_name'])?> (<?=htmlspecialchars($p['location'])?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cell Number</label>
                    <input type="text" name="cell_number" value="<?=htmlspecialchars($row['cell_number'])?>" placeholder="e.g. B-204">
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title"><i data-lucide="calendar"></i> Sentence Details</div>
            <div class="form-grid cols3">
                <div class="form-group">
                    <label>Sentence (Years) *</label>
                    <input type="number" name="sentence_years" value="<?=$row['sentence_years']?>" min="1" required>
                </div>
                <div class="form-group">
                    <label>Sentence Start Date</label>
                    <input type="date" name="start_date" value="<?=$row['start_date']?>">
                </div>
                <div class="form-group">
                    <label>Sentence End Date</label>
                    <input type="date" name="end_date" value="<?=$row['end_date']??''?>">
                    <small style="font-size:11px;color:var(--txt-soft);margin-top:3px;">Leave blank if serving life sentence.</small>
                </div>
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
