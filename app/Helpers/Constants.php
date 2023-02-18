<?php

define('PAGINATE', 15);

// Statuses
define('STATUS_DEACTIVE', 0);
define('STATUS_ACTIVE', 1);

// Status Codes
define('ERROR_401', 401);
define('ERROR_400', 400);
define('SUCCESS_200', 200);
define('ERROR_500', 500);

// User Types
define('USER_ADMIN', 1);
define('USER_APP', 2);

// Login Types
define('LOGIN_EMAIL', 1);
define('LOGIN_GOOGLE', 2);
define('LOGIN_FACEBOOK', 3);
define('LOGIN_APPLE', 4);

// General Messages

// Weathers
define('WEATHER_SUNNY', 'Sunny');
define('WEATHER_PARTLY_CLOUDY', 'Partly Cloudy');
define('WEATHER_RAIN', 'Rain');
define('WEATHER_CLOUDY', 'Cloudy');
define('WEATHER_THUNDER_STORM', 'Thunder Storm');

// Schedule Type
define('TYPE_GRADUAL', "1");
define('TYPE_STEP', "2");

// Device Types
define('DEVICE_ANDROID', 1);
define('DEVICE_IOS', 2);
define('DEVICE_WEB', 3);

// Aquaria Frequency
define('FREQUENCY_WEEK', 1);
define('FREQUENCY_FORTNIGHT', 2);
define('FREQUENCY_MONTH', 3);

define('DEFAULT_SCHEDULE_DEVICE', 1);
define('DEFAULT_SCHEDULE_GROUP', 2);

define('SCHEDULE_EASY', 1);
define('SCHEDULE_ADVANCED', 2);

define('DEVICE_UPDATE_NOTHING', 1);
define('DEVICE_REMOVED_FROM_GROUP', 2);
define('DEVICE_ADDED_TO_GROUP', 3);

// Schedule Approvals
define('PENDING_APPROVAL', 'Pending');
define('ACCEPTED_APPROVAL', 'Accepted');
define('REJECTED_APPROVAL', 'Rejected');


// Water Types
define('WATER_FRESH', 'Fresh');
define('WATER_MARINE', 'Marine');

// Group Type Flags
define('REQUIRE_WATER_TYPE', 'Device water type is missing.');
define('REQUIRE_SAME_WATER_TYPE', 'Device water type is invalid.');

//
define('WATER_TYPE_NOT_UPDATED', 'Water type can not be changed, because device is in group.');
define('DATA_NOT_AVAILABLE_TABLE', 'No data available in table');

define('LOG_TYPE_TOPIC', 1);
define('LOG_TYPE_SUBSCRIBE', 2);
define('LOG_TYPE_CONNECTIVITY', 3);;

// Device
define('ACTIVE_DEVICE', 1);
define('DEVICE_ADD_DISCONNECT_MESSAGE', 'Device Can Not Be Added Into Group, Because Device Is Not Active');
define('DEVICE_REMOVE_DISCONNECT_MESSAGE', 'Device Can Not Be Removed From Group, Because Device Is Not Active');

// Shared Aquarium Statuses
define('SHARED_AQUARIUM_STATUS_PENDING', 0);
define('SHARED_AQUARIUM_STATUS_ACCEPTED', 1);
define('SHARED_AQUARIUM_STATUS_REJECTED', 2);

// Messages
define('GENERAL_ERROR_MESSAGE', 'Operation Failed');
define('GENERAL_SUCCESS_MESSAGE', 'Data Saved Successfully');


// Notifications Push Type
define('NOTIFICATION_WEB_PUSH', 1);
define('NOTIFICATION_MOBILE_PUSH', 2);

// Notification Device Status
define('ACTIVE', 1);
