<?php
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if (!Auth::check()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$db = Database::getConnection();
$userId = Auth::id();
$action = getInput('action', 'count');

switch ($action) {
    case 'count':
        $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM notifikasi WHERE user_id = ? AND is_read = 0');
        $stmt->execute([$userId]);
        jsonResponse(['count' => (int)$stmt->fetch()['cnt']]);

    case 'list':
        $stmt = $db->prepare('SELECT * FROM notifikasi WHERE user_id = ? ORDER BY created_at DESC LIMIT 20');
        $stmt->execute([$userId]);
        jsonResponse(['notifications' => $stmt->fetchAll()]);

    case 'read':
        $id = (int)getInput('id', 0);
        if ($id) {
            $db->prepare('UPDATE notifikasi SET is_read = 1 WHERE id = ? AND user_id = ?')->execute([$id, $userId]);
        } else {
            $db->prepare('UPDATE notifikasi SET is_read = 1 WHERE user_id = ?')->execute([$userId]);
        }
        jsonResponse(['success' => true]);

    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
