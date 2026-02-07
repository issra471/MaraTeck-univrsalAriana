<?php
/**
 * DashboardModel.php
 * Manages database operations for dashboard data using PDO and config.php
 */
require_once __DIR__ . '/../view/config.php';

class DashboardModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    // Aggregated stats logic remains here
    // Entity-specific methods have been moved to their respective Model files.

    // ===================================================================================
    // DASHBOARD & STATS
    // ===================================================================================
    public function getTotalBeneficiaries()
    {
        // Estimate based on cases or donations, or hardcoded for now as it's an impact metric
        // Assuming 1 case serves approx 5 people on average
        $cases = $this->pdo->query("SELECT COUNT(*) FROM cases")->fetchColumn();
        return $cases * 5;
    }

    public function getDonationsTrend()
    {
        $stmt = $this->pdo->query("
            SELECT 
                DATE_FORMAT(created_at, '%b') as month,
                SUM(amount) as total
            FROM donations 
            WHERE status = 'completed'
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY MIN(created_at) ASC
            LIMIT 6
        ");
        return $stmt->fetchAll();
    }

    public function getCasesByCategory()
    {
        $stmt = $this->pdo->query("
            SELECT category, COUNT(*) as count 
            FROM cases 
            GROUP BY category
        ");
        return $stmt->fetchAll();
    }

    public function getDashboardStats()
    {
        $stats = [];
        // ... (existing logic)

        // User statistics
        $stats['total_users'] = $this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?: 0;

        // Association statistics
        $stats['total_associations'] = $this->pdo->query("SELECT COUNT(*) FROM associations")->fetchColumn() ?: 0;

        // Case statistics
        $stats['total_cases'] = $this->pdo->query("SELECT COUNT(*) FROM cases")->fetchColumn() ?: 0;
        $stats['active_cases'] = $this->pdo->query("SELECT COUNT(*) FROM cases WHERE status = 'active'")->fetchColumn() ?: 0;
        $stats['resolved_cases'] = $this->pdo->query("SELECT COUNT(*) FROM cases WHERE status = 'resolved'")->fetchColumn() ?: 0;
        $stats['pending_cases'] = $this->pdo->query("SELECT COUNT(*) FROM cases WHERE status = 'pending'")->fetchColumn() ?: 0;

        // Donation statistics
        $stats['total_donations_amount'] = $this->pdo->query("SELECT SUM(amount) FROM donations WHERE status = 'completed'")->fetchColumn() ?: 0;
        $stats['total_donations_count'] = $this->pdo->query("SELECT COUNT(*) FROM donations")->fetchColumn() ?: 0;
        $stats['today_donations'] = $this->pdo->query("SELECT SUM(amount) FROM donations WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?: 0;
        $stats['today_donors'] = $this->pdo->query("SELECT COUNT(DISTINCT user_id) FROM donations WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?: 0;

        // Other statistics
        $stats['total_donations'] = $stats['total_donations_amount']; // Alias for compatibility
        $stats['total_volunteers'] = $this->pdo->query("SELECT COUNT(*) FROM volunteers WHERE status = 'active'")->fetchColumn() ?: 0;
        $stats['messages_pending'] = $this->pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'pending'")->fetchColumn() ?: 0;
        $stats['events_upcoming'] = $this->pdo->query("SELECT COUNT(*) FROM events WHERE event_date >= NOW()")->fetchColumn() ?: 0;

        return $stats;
    }

}
?>