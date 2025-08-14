<?php

class Booking extends Model {
    public $id;
    public $user_id;
    public $package_id;
    public $booking_date;
    public $status;
    public $created_at;

    public function __construct($id = null, $user_id = null, $package_id = null, $booking_date = null, $status = 'pending') {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->package_id = $package_id;
        $this->booking_date = $booking_date;
        $this->status = $status;
    }

    /**
     * Create a new booking
     * 
     * @return bool|int Returns booking ID on success, false on failure
     */
    public function create() {
        $conn = DatabaseConnection::getConnection();
        $sql = "INSERT INTO bookings (user_id, package_id, booking_date, status) VALUES (:user_id, :package_id, :booking_date, :status)";
        $stmt = $conn->prepare($sql);
        
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':package_id', $this->package_id);
        $stmt->bindParam(':booking_date', $this->booking_date);
        $stmt->bindParam(':status', $this->status);
        
        $success = $stmt->execute();
        
        if ($success) {
            $this->id = $conn->lastInsertId();
            return $this->id;
        }
        
        return false;
    }

    /**
     * Read booking by ID
     * 
     * @return array|false Returns booking data on success, false on failure
     */
    public function read() {
        $conn = DatabaseConnection::getConnection();
        
        if ($this->id) {
            $sql = "SELECT b.*, u.name as user_name, u.email as user_email, p.title as package_title, p.price as package_price 
                    FROM bookings b 
                    LEFT JOIN users u ON b.user_id = u.id 
                    LEFT JOIN packages p ON b.package_id = p.id 
                    WHERE b.id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $this->id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $this->user_id = $result['user_id'];
                $this->package_id = $result['package_id'];
                $this->booking_date = $result['booking_date'];
                $this->status = $result['status'];
                $this->created_at = $result['created_at'];
                return $result;
            }
        }
        return false;
    }

    /**
     * Update booking
     * 
     * @return bool Returns true on success, false on failure
     */
    public function update() {
        $conn = DatabaseConnection::getConnection();
        $sql = "UPDATE bookings SET package_id = :package_id, booking_date = :booking_date, status = :status WHERE id = :id";
        $stmt = $conn->prepare($sql);
        
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':package_id', $this->package_id);
        $stmt->bindParam(':booking_date', $this->booking_date);
        $stmt->bindParam(':status', $this->status);
        
        return $stmt->execute();
    }

    /**
     * Delete booking
     * 
     * @return bool Returns true on success, false on failure
     */
    public function delete() {
        $conn = DatabaseConnection::getConnection();
        $sql = "DELETE FROM bookings WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    /**
     * Get all bookings for a specific user
     * 
     * @param int $user_id
     * @return array Returns array of bookings
     */
    public function getBookingsByUser($user_id) {
        $conn = DatabaseConnection::getConnection();
        $sql = "SELECT b.*, p.title as package_title, p.price as package_price, p.duration as package_duration, p.image_url as package_image
                FROM bookings b 
                LEFT JOIN packages p ON b.package_id = p.id 
                WHERE b.user_id = :user_id 
                ORDER BY b.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all bookings (admin function)
     * 
     * @return array Returns array of all bookings
     */
    public function getAllBookings() {
        $conn = DatabaseConnection::getConnection();
        $sql = "SELECT b.*, u.name as user_name, u.email as user_email, p.title as package_title, p.price as package_price 
                FROM bookings b 
                LEFT JOIN users u ON b.user_id = u.id 
                LEFT JOIN packages p ON b.package_id = p.id 
                ORDER BY b.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update booking status
     * 
     * @param string $status
     * @return bool Returns true on success, false on failure
     */
    public function updateStatus($status) {
        $validStatuses = ['pending', 'confirmed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $conn = DatabaseConnection::getConnection();
        $sql = "UPDATE bookings SET status = :status WHERE id = :id";
        $stmt = $conn->prepare($sql);
        
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':status', $status);
        
        $success = $stmt->execute();
        if ($success) {
            $this->status = $status;
        }
        
        return $success;
    }

    /**
     * Check if user can modify this booking (user owns the booking)
     * 
     * @param int $user_id
     * @return bool
     */
    public function canUserModify($user_id) {
        return $this->user_id == $user_id;
    }

    /**
     * Get user ID from JWT token
     * 
     * @return array Returns user data or error
     */
    public static function getUserFromToken() {
        return JwtHandler::getTokenFromHeader();
    }
}