<?php
session_start();
require_once 'api/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$msg    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_email') {
        $email = trim($_POST['email'] ?? '');
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $pdo->prepare("UPDATE users SET email = ? WHERE id = ?")->execute([$email, $userId]);
                $msg = "<div class='settings-msg ok'>✅ Email updated successfully!</div>";
            } catch (PDOException $e) {
                $msg = "<div class='settings-msg err'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        } else {
            $msg = "<div class='settings-msg err'>❌ Please enter a valid email address.</div>";
        }
    }

    if ($action === 'change_password') {
        $current  = $_POST['current_password']  ?? '';
        $new_pass = $_POST['new_password']       ?? '';
        $confirm  = $_POST['confirm_password']   ?? '';

        if (empty($current) || empty($new_pass) || empty($confirm)) {
            $msg = "<div class='settings-msg err'>❌ All password fields are required.</div>";
        } elseif ($new_pass !== $confirm) {
            $msg = "<div class='settings-msg err'>❌ New passwords do not match.</div>";
        } elseif (strlen($new_pass) < 6) {
            $msg = "<div class='settings-msg err'>❌ Password must be at least 6 characters.</div>";
        } else {
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $hash = $stmt->fetchColumn();

            if (!password_verify($current, $hash)) {
                $msg = "<div class='settings-msg err'>❌ Current password is incorrect.</div>";
            } else {
                $newHash = password_hash($new_pass, PASSWORD_BCRYPT);
                $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$newHash, $userId]);
                $msg = "<div class='settings-msg ok'>✅ Password changed successfully!</div>";
            }
        }
    }

    if ($action === 'update_plan') {
        $plan = $_POST['plan'] ?? 'basic';
        if (in_array($plan, ['basic', 'pro', 'max'])) {
            $pdo->prepare("UPDATE users SET plan = ? WHERE id = ?")->execute([$plan, $userId]);
            $msg = "<div class='settings-msg ok'>✅ Plan updated to " . ucfirst($plan) . "!</div>";
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$pageTitle  = 'CineVault – Settings';
$activePage = 'settings';
include 'includes/header.php';
?>

<style>
.settings-page { max-width: 720px; margin: 80px auto; padding: 0 20px 80px; }
.settings-title { font-family: var(--font-display); font-size: 42px; color: var(--red); margin-bottom: 5px; }
.settings-subtitle { color: #888; margin-bottom: 40px; }
.settings-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 16px;
    padding: 30px;
    margin-bottom: 24px;
    transition: border-color .2s;
}
.settings-card:hover { border-color: rgba(230,57,70,0.3); }
.settings-card h3 {
    font-size: 18px; font-weight: 700; margin-bottom: 20px;
    color: #fff; border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 12px;
}
.settings-field { margin-bottom: 18px; }
.settings-field label { display: block; color: #bbb; margin-bottom: 7px; font-size: 14px; font-weight: 600; }
.settings-field input, .settings-field select {
    width: 100%; padding: 12px 15px; background: #111;
    border: 1px solid rgba(255,255,255,0.12); border-radius: 8px;
    color: #fff; font-family: 'Nunito', sans-serif; font-size: 15px;
    transition: border-color .2s;
}
.settings-field input:focus, .settings-field select:focus { outline: none; border-color: var(--red); }
.settings-field input:disabled { color: #555; cursor: not-allowed; }
.settings-btn { width: 100%; padding: 13px; font-size: 15px; margin-top: 8px; }
.settings-msg { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; font-size: 14px; }
.settings-msg.ok  { background: rgba(0,200,100,0.1); border: 1px solid rgba(0,200,100,0.3); color: #0c6; }
.settings-msg.err { background: rgba(230,57,70,0.1);  border: 1px solid rgba(230,57,70,0.3); color: var(--red); }
.danger-zone { border-color: rgba(230,57,70,0.3); }
.danger-zone h3 { color: var(--red); }
/* Avatar upload preview */
.avatar-preview { width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, var(--red),#800);
    display:flex;align-items:center;justify-content:center;font-size:40px;font-weight:800;
    overflow:hidden; position:relative; cursor:pointer; border:3px solid rgba(255,255,255,0.1);
    transition: border-color .2s; margin: 0 auto 20px; }
.avatar-preview:hover { border-color: var(--red); }
.avatar-preview img { width:100%;height:100%;object-fit:cover; }
.avatar-preview .avatar-overlay { position:absolute;inset:0;background:rgba(0,0,0,0.5);
    display:none;align-items:center;justify-content:center;font-size:28px; border-radius:50%; }
.avatar-preview:hover .avatar-overlay { display:flex; }
.plan-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 12px; }
.plan-opt { border: 2px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 16px;
    text-align: center; cursor: pointer; transition: all .2s; }
.plan-opt:hover { border-color: var(--red); background: rgba(230,57,70,0.05); }
.plan-opt.selected { border-color: var(--gold); background: rgba(255,193,7,0.07); }
.plan-opt .plan-name { font-weight: 800; font-size: 16px; }
.plan-opt .plan-price { color: #aaa; font-size: 13px; margin-top: 5px; }
</style>

<div class="settings-page">
    <h1 class="settings-title">⚙️ Account Settings</h1>
    <p class="settings-subtitle">Manage your profile, security, and subscription preferences.</p>

    <?= $msg ?>

    <!-- ── Avatar Upload ── -->
    <div class="settings-card">
        <h3>🖼️ Profile Picture</h3>
        <div id="avatarPreview" class="avatar-preview" onclick="document.getElementById('avatarInput').click()">
            <?php if (!empty($user['avatar_url'])): ?>
                <img src="<?= htmlspecialchars($user['avatar_url']) ?>" id="avatarImg" alt="Avatar">
            <?php else: ?>
                <span id="avatarInitial"><?= strtoupper(substr($user['username'],0,1)) ?></span>
            <?php endif; ?>
            <div class="avatar-overlay">📷</div>
        </div>
        <input type="file" id="avatarInput" accept="image/*" style="display:none" onchange="uploadAvatar(this)">
        <p style="text-align:center;color:#666;font-size:13px;">Click avatar to upload. JPG, PNG, WEBP — max 5MB</p>
        <div id="avatarMsg" style="text-align:center;margin-top:8px;font-size:14px;"></div>
    </div>

    <!-- ── Email ── -->
    <div class="settings-card">
        <h3>📧 Email Address</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update_email">
            <div class="settings-field">
                <label>Username (read-only)</label>
                <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled>
            </div>
            <div class="settings-field">
                <label>Email Address</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <button type="submit" class="glow-btn settings-btn">Save Email</button>
        </form>
    </div>

    <!-- ── Password ── -->
    <div class="settings-card">
        <h3>🔒 Change Password</h3>
        <form method="POST">
            <input type="hidden" name="action" value="change_password">
            <div class="settings-field">
                <label>Current Password</label>
                <input type="password" name="current_password" placeholder="Enter current password" required>
            </div>
            <div class="settings-field">
                <label>New Password</label>
                <input type="password" name="new_password" placeholder="At least 6 characters" required>
            </div>
            <div class="settings-field">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" placeholder="Repeat new password" required>
            </div>
            <button type="submit" class="glow-btn settings-btn">Update Password</button>
        </form>
    </div>

    <!-- ── Subscription Plan ── -->
    <div class="settings-card">
        <h3>💎 Subscription Plan</h3>
        <form method="POST" id="planForm">
            <input type="hidden" name="action" value="update_plan">
            <input type="hidden" name="plan" id="selectedPlan" value="<?= htmlspecialchars($user['plan'] ?? 'basic') ?>">
            <div class="plan-grid">
                <?php
                $plans = [
                    'basic' => ['Basic', '$0/mo', 'Free access, ads, 720p'],
                    'pro'   => ['Pro',   '$9/mo', 'Full vault, 1080p, no ads'],
                    'max'   => ['Max',   '$14/mo','4K HDR, 5 screens, downloads'],
                ];
                foreach ($plans as $key => [$name, $price, $desc]):
                    $sel = ($user['plan'] ?? 'basic') === $key ? 'selected' : '';
                ?>
                <div class="plan-opt <?= $sel ?>" onclick="selectPlan('<?= $key ?>')">
                    <div class="plan-name"><?= $name ?></div>
                    <div class="plan-price"><?= $price ?></div>
                    <div style="font-size:12px;color:#777;margin-top:6px"><?= $desc ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="glow-btn settings-btn" style="margin-top:16px">Update Plan</button>
        </form>
    </div>

    <!-- ── Danger Zone ── -->
    <div class="settings-card danger-zone">
        <h3>☠️ Danger Zone</h3>
        <p style="color:#888;margin-bottom:16px;font-size:14px">These actions are permanent and cannot be undone.</p>
        <button class="glow-btn" style="background:#500;border:1px solid var(--red);box-shadow:none;padding:12px 24px;"
            onclick="if(confirm('Are you sure you want to delete your account? This CANNOT be undone!')) alert('Please contact an administrator to delete your account.')">
            🗑️ Delete Account
        </button>
    </div>
</div>

<script>
function selectPlan(plan) {
    document.getElementById('selectedPlan').value = plan;
    document.querySelectorAll('.plan-opt').forEach(el => el.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
}

async function uploadAvatar(input) {
    if (!input.files[0]) return;
    const msg = document.getElementById('avatarMsg');
    msg.textContent = '⏳ Uploading...';
    msg.style.color = '#aaa';

    const fd = new FormData();
    fd.append('avatar', input.files[0]);

    try {
        const res  = await fetch('api/upload_avatar.php', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.status === 'success') {
            msg.textContent = '✅ Avatar updated!';
            msg.style.color = '#0c6';
            // Show new avatar in preview
            const prev = document.getElementById('avatarPreview');
            const init = document.getElementById('avatarInitial');
            let img = document.getElementById('avatarImg');
            if (!img) {
                img = document.createElement('img');
                img.id = 'avatarImg';
                if (init) init.remove();
                prev.insertBefore(img, prev.firstChild);
            }
            img.src = data.avatar_url + '?t=' + Date.now();
        } else {
            msg.textContent = '❌ ' + (data.message || 'Upload failed');
            msg.style.color = '#e63946';
        }
    } catch(e) {
        msg.textContent = '❌ Connection error';
        msg.style.color = '#e63946';
    }
}
</script>

<?php include 'includes/footer.php'; ?>
