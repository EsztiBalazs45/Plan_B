<?php
require_once '../includes/header.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_newsletter']) || isset($_POST['edit_newsletter'])) {
        $title = sanitize($_POST['title']);
        $content = $_POST['content']; // Allow HTML content
        $status = (int)$_POST['status'];
        
        if (isset($_POST['add_newsletter'])) {
            $stmt = $conn->prepare("INSERT INTO newsletters (newsletter_title, newsletter_content, newsletter_status, user_id) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$title, $content, $status, $user_id])) {
                $_SESSION['message'] = 'Hírlevél sikeresen létrehozva!';
                $_SESSION['message_type'] = 'success';
            }
        } else {
            $newsletter_id = $_POST['newsletter_id'];
            $stmt = $conn->prepare("UPDATE newsletters SET newsletter_title = ?, newsletter_content = ?, newsletter_status = ? WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$title, $content, $status, $newsletter_id, $user_id])) {
                $_SESSION['message'] = 'Hírlevél sikeresen frissítve!';
                $_SESSION['message_type'] = 'success';
            }
        }
        header('Location: newsletters.php');
        exit();
    }
    
    if (isset($_POST['delete_newsletter'])) {
        $newsletter_id = $_POST['newsletter_id'];
        $stmt = $conn->prepare("DELETE FROM newsletters WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$newsletter_id, $user_id])) {
            $_SESSION['message'] = 'Hírlevél sikeresen törölve!';
            $_SESSION['message_type'] = 'success';
        }
        header('Location: newsletters.php');
        exit();
    }
}

// Get newsletter data for editing
$edit_newsletter = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM newsletters WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $user_id]);
    $edit_newsletter = $stmt->fetch();
    
    if (!$edit_newsletter) {
        header('Location: newsletters.php');
        exit();
    }
}

// Get all newsletters for listing
$newsletters = [];
if ($action === 'list') {
    $stmt = $conn->prepare("SELECT * FROM newsletters WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $newsletters = $stmt->fetchAll();
}
?>

<?php if ($action === 'new' || $action === 'edit'): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><?php echo $action === 'new' ? 'Új hírlevél létrehozása' : 'Hírlevél szerkesztése'; ?></h5>
        </div>
        <div class="card-body">
            <form method="POST" action="" class="needs-validation" novalidate>
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="newsletter_id" value="<?php echo $edit_newsletter['id']; ?>">
                <?php endif; ?>
                
                <div class="mb-3">
                    <label for="title" class="form-label">Hírlevél címe</label>
                    <input type="text" class="form-control" id="title" name="title" 
                           value="<?php echo $edit_newsletter ? htmlspecialchars($edit_newsletter['newsletter_title']) : ''; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="content" class="form-label">Tartalom</label>
                    <textarea class="form-control" id="content" name="content" rows="10" required><?php 
                        echo $edit_newsletter ? htmlspecialchars($edit_newsletter['newsletter_content']) : ''; 
                    ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="status" class="form-label">Státusz</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="0" <?php echo $edit_newsletter && $edit_newsletter['newsletter_status'] == 0 ? 'selected' : ''; ?>>Vázlat</option>
                        <option value="1" <?php echo $edit_newsletter && $edit_newsletter['newsletter_status'] == 1 ? 'selected' : ''; ?>>Publikált</option>
                    </select>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="newsletters.php" class="btn btn-secondary">Vissza</a>
                    <button type="submit" name="<?php echo $action === 'new' ? 'add_newsletter' : 'edit_newsletter'; ?>" class="btn btn-primary">
                        <?php echo $action === 'new' ? 'Hírlevél létrehozása' : 'Módosítások mentése'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Hírlevelek kezelése</h5>
            <a href="?action=new" class="btn btn-primary">
                <i class="fas fa-plus"></i> Új hírlevél
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($newsletters)): ?>
                <p class="text-center text-muted">Még nincsenek hírlevelek.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Cím</th>
                                <th>Státusz</th>
                                <th>Létrehozva</th>
                                <th>Műveletek</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($newsletters as $newsletter): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($newsletter['newsletter_title']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $newsletter['newsletter_status'] ? 'success' : 'warning'; ?>">
                                            <?php echo $newsletter['newsletter_status'] ? 'Publikált' : 'Vázlat'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y.m.d H:i', strtotime($newsletter['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="?action=edit&id=<?php echo $newsletter['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Szerkesztés">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="" class="d-inline" 
                                                  onsubmit="return confirm('Biztosan törölni szeretné ezt a hírlevelet?');">
                                                <input type="hidden" name="newsletter_id" value="<?php echo $newsletter['id']; ?>">
                                                <button type="submit" name="delete_newsletter" class="btn btn-sm btn-danger" title="Törlés">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Include TinyMCE -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js"></script>
<script>
tinymce.init({
    selector: '#content',
    height: 400,
    menubar: false,
    plugins: [
        'advlist autolink lists link image charmap print preview anchor',
        'searchreplace visualblocks code fullscreen',
        'insertdatetime media table paste code help wordcount'
    ],
    toolbar: 'undo redo | formatselect | bold italic backcolor | \
        alignleft aligncenter alignright alignjustify | \
        bullist numlist outdent indent | removeformat | help'
});

// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php require_once '../includes/footer.php'; ?>
