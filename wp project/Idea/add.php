<?php
session_start();
require_once __DIR__ . '/../db.php';

// Initialize form data from session if available
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']); // Clear after use

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idea = [
        "title" => $_POST["title"],
        "short_desc" => $_POST["short_desc"],
        "detailed_desc" => $_POST["detailed_desc"],
        "tags" => $_POST["tags"],
        "category" => $_POST["category"],
        "skills" => $_POST["skills"],
        "collab" => isset($_POST["collab"]) ? true : false,
        "visibility" => $_POST["visibility"],
        "file" => ""
    ];

    // Store form data in session in case we redirect back
    $_SESSION['form_data'] = $_POST;

    if (!empty($_FILES["support_file"]["name"])) {
        $targetDir = "uploads/";
        if (!file_exists($targetDir)) mkdir($targetDir);
        $filename = time() . "_" . basename($_FILES["support_file"]["name"]);
        $targetFile = $targetDir . $filename;
        move_uploaded_file($_FILES["support_file"]["tmp_name"], $targetFile);
        $idea["file"] = $targetFile;
    }

    $ideas = file_exists("ideas.json") ? json_decode(file_get_contents("ideas.json"), true) : [];
    $ideas[] = $idea;
    file_put_contents("ideas.json", json_encode($ideas));
    
    // Clear the form data after successful submission
    unset($_SESSION['form_data']);
    header("Location: index.php");
    exit();
}

// Get list of users for collaboration
$users = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE id != ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Share New Idea</title>
    <link rel="stylesheet" href="../Idea/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        .collaborators-section {
            display: none;
            margin-top: 10px;
        }
        #collaborators-btn {
            background-color: #4CAF50;
        }
        #collaborators-btn:hover {
            background-color: #45a049;
        }
        .user-search-container {
            position: relative;
            margin-top: 10px;
        }
        .user-search-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .user-dropdown {
            display: none;
            position: absolute;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 0 0 4px 4px;
            background: white;
            z-index: 1000;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .user-dropdown.show {
            display: block;
        }
        .user-item {
            padding: 10px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .user-item:hover {
            background-color: #f0f8ff;
        }
        .no-users {
            padding: 10px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
<div class="main-container">
    <div class="content-wrapper">
        <div class="edit-form-wrapper">
            <h2>Share a New Idea</h2>
            <form method="POST" enctype="multipart/form-data" class="styled-form">
                <label>Title *</label>
                <input type="text" name="title" required placeholder="e.g., Smart Waste Tracker" value="<?= htmlspecialchars($formData['title'] ?? '') ?>">

                <label>Short Description *</label>
                <textarea name="short_desc" rows="2" required placeholder="Brief overview of your idea..."><?= htmlspecialchars($formData['short_desc'] ?? '') ?></textarea>

                <label>Detailed Description</label>
                <textarea name="detailed_desc" rows="5" placeholder="Explain the idea in detail, benefits, how it works..."><?= htmlspecialchars($formData['detailed_desc'] ?? '') ?></textarea>

                <label>Tags</label>
                <input type="text" name="tags" placeholder="e.g., environment, sustainability" value="<?= htmlspecialchars($formData['tags'] ?? '') ?>">

                <label>Category</label>
                <select name="category">
                    <option value="Technology" <?= ($formData['category'] ?? '') === 'Technology' ? 'selected' : '' ?>>Technology</option>
                    <option value="Education" <?= ($formData['category'] ?? '') === 'Education' ? 'selected' : '' ?>>Education</option>
                    <option value="Health" <?= ($formData['category'] ?? '') === 'Health' ? 'selected' : '' ?>>Health</option>
                    <option value="Social" <?= ($formData['category'] ?? '') === 'Social' ? 'selected' : '' ?>>Social Impact</option>
                    <option value="Others" <?= empty($formData['category']) || ($formData['category'] ?? '') === 'Others' ? 'selected' : '' ?>>Others</option>
                </select>

                <label>Upload Supporting File (PDF/Image/Docs)</label>
                <input type="file" name="support_file">

                <label>Required Skills</label>
                <input type="text" name="skills" placeholder="e.g., React, PHP, UI Design" value="<?= htmlspecialchars($formData['skills'] ?? '') ?>">

                <label>Accepting Collaborators?</label>
                <div>
                    <input type="checkbox" name="collab" id="collab-checkbox" <?= ($formData['collab'] ?? true) ? 'checked' : '' ?>> Yes
                </div>
                
                <div class="collaborators-section" id="collaborators-section">
                    <div class="user-search-container">
                        <input type="text" class="user-search-input" id="user-search" placeholder="Search users...">
                        <div class="user-dropdown" id="user-dropdown">
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <div class="user-item" data-user-id="<?= $user['id'] ?>">
                                        <?= htmlspecialchars($user['username']) ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-users">No users found</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <label>Visibility</label>
                <select name="visibility">
                    <option value="public" <?= ($formData['visibility'] ?? 'public') === 'public' ? 'selected' : '' ?>>Public</option>
                    <option value="private" <?= ($formData['visibility'] ?? '') === 'private' ? 'selected' : '' ?>>Private</option>
                </select>

                <div class="form-buttons">
                    <button type="submit">💡 Submit Idea</button>
                    <a href="index.php"><button type="button">Back</button></a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="chat-container" style="width:450px;">
        <div class="chat-header">Chat bot Assistance</div>
        <div class="chat-box" id="chat">
            <div class="bot-message message">Hi, may I help you with anything?</div>
        </div>
        <div class="input-area">
            <input type="text" id="message" placeholder="Enter Text">
            <button id="send">Send</button>
        </div>
    </div>
</div>
<script src="script.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.getElementById('collab-checkbox');
        const collaboratorsSection = document.getElementById('collaborators-section');
        const userSearch = document.getElementById('user-search');
        const userDropdown = document.getElementById('user-dropdown');
        const userItems = document.querySelectorAll('.user-item');
        
        // Initial check - show collaborators section if checkbox is checked
        if (checkbox.checked) {
            collaboratorsSection.style.display = 'block';
        }
        
        // Toggle collaborators section
        checkbox.addEventListener('change', function() {
            collaboratorsSection.style.display = this.checked ? 'block' : 'none';
        });
        
        // Show dropdown when search input is focused
        userSearch.addEventListener('focus', function() {
            userDropdown.classList.add('show');
            filterUsers();
        });
        
        // Hide dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userSearch.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.remove('show');
            }
        });
        
        // Filter users based on search input
        userSearch.addEventListener('input', filterUsers);
        
        // Handle user selection
        userItems.forEach(item => {
            item.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');
                
                // Store form data in sessionStorage before redirecting
                const form = document.querySelector('form');
                const formData = new FormData(form);
                for (const [key, value] of formData.entries()) {
                    sessionStorage.setItem(key, value);
                }
                
                window.location.href = `invit_collaboration.php?user_id=${userId}`;
            });
        });
        
        function filterUsers() {
            const searchTerm = userSearch.value.toLowerCase();
            let hasResults = false;
            
            userItems.forEach(item => {
                const username = item.textContent.toLowerCase();
                if (username.includes(searchTerm)) {
                    item.style.display = 'block';
                    hasResults = true;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Show "No users" message if no results
            const noUsersMsg = document.querySelector('.no-users');
            if (noUsersMsg) {
                noUsersMsg.style.display = hasResults ? 'none' : 'block';
            }
        }
        
        // Restore form data from sessionStorage if available
        const form = document.querySelector('form');
        form.querySelectorAll('input, textarea, select').forEach(element => {
            const savedValue = sessionStorage.getItem(element.name);
            if (savedValue !== null) {
                if (element.type === 'checkbox') {
                    element.checked = savedValue === 'true';
                } else if (element.type === 'radio') {
                    if (element.value === savedValue) {
                        element.checked = true;
                    }
                } else {
                    element.value = savedValue;
                }
            }
        });
        
        // Clear sessionStorage when form is submitted
        form.addEventListener('submit', function() {
            sessionStorage.clear();
        });
    });
</script>
</body>
</html>