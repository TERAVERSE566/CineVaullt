<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to use the watchlist.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action  = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    if ($action === 'add') {
        $title     = $_POST['title'] ?? '';
        $year      = $_POST['year'] ?? '';
        $duration  = $_POST['duration'] ?? '';
        $rating    = $_POST['rating'] ?? '';
        $genre     = $_POST['genre'] ?? '';
        $desc      = $_POST['desc'] ?? '';
        $posterUrl = $_POST['poster'] ?? '';

        if (empty($title)) {
            echo json_encode(['status' => 'error', 'message' => 'Movie title is required.']);
            exit;
        }

        // Get or create content record by title
        $stmt = $pdo->prepare("SELECT id FROM content WHERE title = ?");
        $stmt->execute([$title]);
        $content = $stmt->fetch();

        if ($content) {
            $content_id = $content['id'];
        } else {
            $ins = $pdo->prepare("INSERT INTO content (title, release_year, duration, rating, genre, description, poster_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $ins->execute([$title, $year, $duration, $rating, $genre, $desc, $posterUrl]);
            $content_id = $pdo->lastInsertId();
        }

        // Check duplicate
        $check = $pdo->prepare("SELECT id FROM watchlist WHERE user_id = ? AND content_id = ?");
        $check->execute([$user_id, $content_id]);
        if ($check->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Already in watchlist.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO watchlist (user_id, content_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $content_id]);
        echo json_encode(['status' => 'success', 'message' => 'Added to watchlist.']);

    } elseif ($action === 'remove') {
        $title = $_POST['title'] ?? '';
        $stmt = $pdo->prepare("SELECT id FROM content WHERE title = ?");
        $stmt->execute([$title]);
        $content = $stmt->fetch();
        if ($content) {
            $stmt = $pdo->prepare("DELETE FROM watchlist WHERE user_id = ? AND content_id = ?");
            $stmt->execute([$user_id, $content['id']]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Removed from watchlist.']);

    } elseif ($action === 'clear') {
        $stmt = $pdo->prepare("DELETE FROM watchlist WHERE user_id = ?");
        $stmt->execute([$user_id]);
        echo json_encode(['status' => 'success', 'message' => 'Watchlist cleared.']);

    } elseif ($action === 'get') {
        $stmt = $pdo->prepare("
            SELECT c.* FROM content c
            JOIN watchlist w ON c.id = w.content_id
            WHERE w.user_id = ?
            ORDER BY w.added_at DESC
        ");
        $stmt->execute([$user_id]);
        $items = $stmt->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $items]);

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
?>
