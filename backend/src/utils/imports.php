<?php

//common imports
require_once  'src/utils/ApiResourceBase.php';
require_once 'src/classes/Model.php';
require_once 'src/database/connection.php';

//classes
require_once 'src/classes/User.php';
require_once 'src/classes/Packages.php';
require_once 'src/classes/Booking.php';


//API Endpoints
require_once 'src/api/auth/userApi.php';
require_once 'src/api/packages/packageApi.php';
require_once 'src/api/bookings/bookingApi.php';

//Router
require_once 'src/utils/router.php';


//JWT Handler
require_once 'src/utils/JwtHandler.php';

// Composer autoload
require_once 'vendor/autoload.php'; 

