<?php
require_once __DIR__ . '/../models/Database.php';

class LoanController {
    private $db;
    public function __construct(){
        $this->db = Database::getInstance()->pdo();
    }

    public function loan($data){
        // Expect data: copy_id, member_id, loaned_by, days
        $copy_id = $data['copy_id'];
        $member_id = $data['member_id'];
        $loaned_by = $data['loaned_by'] ?? 1;
        $days = $data['days'] ?? 7;
        $loan_date = date('Y-m-d');
        $due_date = date('Y-m-d', strtotime("+$days days"));

        // Insert loan
        $stmt = $this->db->prepare('INSERT INTO loans (copy_id, member_id, loaned_by, loan_date, due_date, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$copy_id, $member_id, $loaned_by, $loan_date, $due_date, 'ongoing']);
        $loan_id = $this->db->lastInsertId();

        // Update copy status
        $this->db->prepare('UPDATE copies SET status = ? WHERE id = ?')->execute(['loaned', $copy_id]);

        // Activity log
        $this->db->prepare('INSERT INTO activity_logs (user_id, action, context, ip, created_at) VALUES (?, ?, ?, ?, NOW())')
            ->execute([$loaned_by, 'loan_create', "loan_id:$loan_id,member:$member_id,copy:$copy_id", $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);

        return ['success' => true, 'loan_id' => $loan_id];
    }

    public function return($loan_id){
        // Call stored procedure sp_mark_return if exists, otherwise update
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare('SELECT * FROM loans WHERE id = ? FOR UPDATE');
            $stmt->execute([$loan_id]);
            $loan = $stmt->fetch();
            if (!$loan) return ['error' => 'Loan not found'];

            // Update returned_date
            $this->db->prepare('UPDATE loans SET returned_date = CURDATE(), status = ? WHERE id = ?')->execute(['returned', $loan_id]);

            // Update copy status
            $this->db->prepare('UPDATE copies SET status = ? WHERE id = ?')->execute(['available', $loan['copy_id']]);

            // Optionally call stored procedure
            try { $this->db->exec("CALL sp_mark_return($loan_id)"); } catch (Exception $e) { /* ignore if not present */ }

            $this->db->commit();
            // Log
            $this->db->prepare('INSERT INTO activity_logs (user_id, action, context, ip, created_at) VALUES (?, ?, ?, ?, NOW())')
                ->execute([null, 'loan_return', "loan_id:$loan_id", $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
            return ['success' => true];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['error' => $e->getMessage()];
        }
    }
}
