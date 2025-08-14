<?php
require_once 'src/utils/ApiResourceBase.php';
require_once 'src/utils/JwtHandler.php';
require_once 'src/classes/Booking.php';
require_once 'src/classes/Packages.php';
require_once 'src/database/connection.php';

class BookingApi extends ApiResourceBase {
    
    public function __construct() {
        $this->setRoles([
            "create" => ["customer", "admin"],
            "read" => ["customer", "admin"],
            "readAll" => ["admin"],
            "readUserBookings" => ["customer", "admin"],
            "update" => ["customer", "admin"],
            "updateStatus" => ["admin"],
            "delete" => ["admin"],
        ]);
    }

    /**
     * Create a new booking
     */
    public function create($data) {
        $user = $this->getAuthenticatedUser();
        
        if (!$user) {
            return ["status" => "error", "message" => "Invalid Auth token"];
        }
        
        $userRole = $user['role'] ?? null;
        $userId = $user['id'] ?? null;
        
        if (!$this->checkRoles($userRole, 'create')) {
            return ["status" => "error", "message" => "Unauthorized action"];
        }
        
        // Validate required fields
        $missing = $this->validateFields($data, ['package_id', 'booking_date']);
        if (!empty($missing)) {
            return [
                "status" => "error",
                "message" => "Invalid Request. Missing fields: " . implode(", ", $missing)
            ];
        }
        
        // Validate package exists
        $package = new Packages($data['package_id']);
        if (!$package->read()) {
            return ["status" => "error", "message" => "Package not found"];
        }
        
        // Validate booking date format
        if (!$this->isValidDateTime($data['booking_date'])) {
            return ["status" => "error", "message" => "Invalid booking date format. Use YYYY-MM-DD or YYYY-MM-DD HH:MM:SS"];
        }
        
        // Normalize the datetime (add default time if only date provided)
        $normalizedDateTime = $this->normalizeDateTime($data['booking_date']);
        
        // Check if booking date is in the future
        if (strtotime($normalizedDateTime) <= time()) {
            return ["status" => "error", "message" => "Booking date must be in the future"];
        }
        
        // Create booking
        $status = isset($data['status']) ? $data['status'] : 'pending';
        $booking = new Booking(null, $userId, $data['package_id'], $normalizedDateTime, $status);
        
        $booking_id = $booking->create();
        
        if ($booking_id) {
            // Get the created booking with full details
            $createdBooking = new Booking($booking_id);
            $bookingData = $createdBooking->read();
            
            return [
                "status" => "success",
                "message" => "Booking created successfully",
                "data" => $bookingData
            ];
        } else {
            return ["status" => "error", "message" => "Failed to create booking"];
        }
    }

    /**
     * Read a specific booking by ID
     */
    public function read($data) {
        $user = $this->getAuthenticatedUser();
        
        if (!$user) {
            return ["status" => "error", "message" => "Invalid Auth token"];
        }
        
        $userRole = $user['role'] ?? null;
        $userId = $user['id'] ?? null;
        
        if (!$this->checkRoles($userRole, 'read')) {
            return ["status" => "error", "message" => "Unauthorized action"];
        }
        
        if (!isset($data['id'])) {
            return ["status" => "error", "message" => "Booking ID is required"];
        }
        
        $booking = new Booking($data['id']);
        $bookingData = $booking->read();
        
        if (!$bookingData) {
            return ["status" => "error", "message" => "Booking not found"];
        }
        
        // Check if user can access this booking (owner or admin)
        if ($userRole !== 'admin' && $bookingData['user_id'] != $userId) {
            return ["status" => "error", "message" => "Access denied"];
        }
        
        return [
            "status" => "success",
            "message" => "Booking retrieved successfully",
            "data" => $bookingData
        ];
    }

    /**
     * Get all bookings (admin only)
     */
    public function readAll($data) {
        $user = $this->getAuthenticatedUser();
        
        if (!$user) {
            return ["status" => "error", "message" => "Invalid Auth token"];
        }
        
        $userRole = $user['role'] ?? null;
        
        if (!$this->checkRoles($userRole, 'readAll')) {
            return ["status" => "error", "message" => "Unauthorized action. Admin access required"];
        }
        
        $booking = new Booking();
        $bookings = $booking->getAllBookings();
        
        return [
            "status" => "success",
            "message" => "All bookings retrieved successfully",
            "data" => $bookings
        ];
    }

    /**
     * Get all bookings for the authenticated user
     */
    public function readUserBookings($data) {
        $user = $this->getAuthenticatedUser();
        
        if (!$user) {
            return ["status" => "error", "message" => "Invalid Auth token"];
        }
        
        $userRole = $user['role'] ?? null;
        $userId = $user['id'] ?? null;
        
        if (!$this->checkRoles($userRole, 'readUserBookings')) {
            return ["status" => "error", "message" => "Unauthorized action"];
        }
        
        $booking = new Booking();
        $bookings = $booking->getBookingsByUser($userId);
        
        return [
            "status" => "success",
            "message" => "User bookings retrieved successfully",
            "data" => $bookings
        ];
    }

    /**
     * Update a booking
     */
    public function update($data) {
        $user = $this->getAuthenticatedUser();
        
        if (!$user) {
            return ["status" => "error", "message" => "Invalid Auth token"];
        }
        
        $userRole = $user['role'] ?? null;
        $userId = $user['id'] ?? null;
        
        if (!$this->checkRoles($userRole, 'update')) {
            return ["status" => "error", "message" => "Unauthorized action"];
        }
        
        if (!isset($data['id'])) {
            return ["status" => "error", "message" => "Booking ID is required"];
        }
        
        $booking = new Booking($data['id']);
        $existingBooking = $booking->read();
        
        if (!$existingBooking) {
            return ["status" => "error", "message" => "Booking not found"];
        }
        
        // Check if user can modify this booking
        if ($userRole !== 'admin' && !$booking->canUserModify($userId)) {
            return ["status" => "error", "message" => "Access denied"];
        }
        
        // Update only provided fields
        if (isset($data['package_id'])) {
            // Validate package exists
            $package = new Packages($data['package_id']);
            if (!$package->read()) {
                return ["status" => "error", "message" => "Package not found"];
            }
            $booking->package_id = $data['package_id'];
        }
        
        if (isset($data['booking_date'])) {
            if (!$this->isValidDateTime($data['booking_date'])) {
                return ["status" => "error", "message" => "Invalid booking date format. Use YYYY-MM-DD or YYYY-MM-DD HH:MM:SS"];
            }
            
            // Normalize the datetime
            $normalizedDateTime = $this->normalizeDateTime($data['booking_date']);
            
            if (strtotime($normalizedDateTime) <= time()) {
                return ["status" => "error", "message" => "Booking date must be in the future"];
            }
            
            $booking->booking_date = $normalizedDateTime;
        }
        
        if (isset($data['status'])) {
            $validStatuses = ['pending', 'confirmed', 'cancelled'];
            if (!in_array($data['status'], $validStatuses)) {
                return ["status" => "error", "message" => "Invalid status. Must be: pending, confirmed, or cancelled"];
            }
            $booking->status = $data['status'];
        }
        
        if ($booking->update()) {
            $updatedBooking = $booking->read();
            return [
                "status" => "success",
                "message" => "Booking updated successfully",
                "data" => $updatedBooking
            ];
        } else {
            return ["status" => "error", "message" => "Failed to update booking"];
        }
    }

    /**
     * Update booking status only (admin only)
     */
    public function updateStatus($data) {
        $user = $this->getAuthenticatedUser();
        
        if (!$user) {
            return ["status" => "error", "message" => "Invalid Auth token"];
        }
        
        $userRole = $user['role'] ?? null;
        
        if (!$this->checkRoles($userRole, 'updateStatus')) {
            return ["status" => "error", "message" => "Unauthorized action. Admin access required"];
        }
        
        if (!isset($data['id']) || !isset($data['status'])) {
            return ["status" => "error", "message" => "Booking ID and status are required"];
        }
        
        $booking = new Booking($data['id']);
        $existingBooking = $booking->read();
        
        if (!$existingBooking) {
            return ["status" => "error", "message" => "Booking not found"];
        }
        
        if ($booking->updateStatus($data['status'])) {
            $updatedBooking = $booking->read();
            return [
                "status" => "success",
                "message" => "Booking status updated successfully",
                "data" => $updatedBooking
            ];
        } else {
            return ["status" => "error", "message" => "Invalid status or failed to update"];
        }
    }

    /**
     * Delete a booking
     */
    public function delete($data) {
        // Debug: Log what we received
        error_log("Delete method called with data: " . json_encode($data));
        
        $user = $this->getAuthenticatedUser();
        
        if (!$user) {
            return ["status" => "error", "message" => "Invalid Auth token"];
        }
        
        $userRole = $user['role'] ?? null;
        $userId = $user['id'] ?? null;
        
        if (!$this->checkRoles($userRole, 'delete')) {
            return ["status" => "error", "message" => "Unauthorized action"];
        }
        
        // Check if data is null or not an array
        if ($data === null || !is_array($data)) {
            return ["status" => "error", "message" => "Invalid request data format"];
        }
        
        if (!isset($data['id'])) {
            return ["status" => "error", "message" => "Booking ID is required"];
        }
        
        $booking = new Booking($data['id']);
        $existingBooking = $booking->read();
        
        if (!$existingBooking) {
            return ["status" => "error", "message" => "Booking not found"];
        }
        
        // Check if user can delete this booking
        if ($userRole !== 'admin' && !$booking->canUserModify($userId)) {
            return ["status" => "error", "message" => "Access denied"];
        }
        
        if ($booking->delete()) {
            return [
                "status" => "success",
                "message" => "Booking deleted successfully"
            ];
        } else {
            return ["status" => "error", "message" => "Failed to delete booking"];
        }
    }

    /**
     * Validate datetime format - accepts both YYYY-MM-DD and YYYY-MM-DD HH:MM:SS
     */
    private function isValidDateTime($datetime) {
        // Try full datetime format first (YYYY-MM-DD HH:MM:SS)
        $d = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
        if ($d && $d->format('Y-m-d H:i:s') === $datetime) {
            return true;
        }
        
        // Try date only format (YYYY-MM-DD)
        $d = DateTime::createFromFormat('Y-m-d', $datetime);
        if ($d && $d->format('Y-m-d') === $datetime) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Normalize date to full datetime format
     * If only date is provided (YYYY-MM-DD), add default time (09:00:00)
     */
    private function normalizeDateTime($datetime) {
        // If it's already in full format, return as is
        $d = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
        if ($d && $d->format('Y-m-d H:i:s') === $datetime) {
            return $datetime;
        }
        
        // If it's date only, add default time
        $d = DateTime::createFromFormat('Y-m-d', $datetime);
        if ($d && $d->format('Y-m-d') === $datetime) {
            return $datetime . ' 09:00:00';
        }
        
        return $datetime; // Return original if neither format matches
    }
}
?>
