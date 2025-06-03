-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2025 at 04:42 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gym_ms`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `A_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`A_id`) VALUES
(3);

-- --------------------------------------------------------

--
-- Table structure for table `certifications`
--

CREATE TABLE `certifications` (
  `W_id` int(11) NOT NULL,
  `Certification` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certifications`
--

INSERT INTO `certifications` (`W_id`, `Certification`) VALUES
(1, 'Certified Personal Trainer - ACE'),
(1, 'Nutrition Specialist - NASM'),
(2, 'Certified Group Fitness Instructor - AFAA'),
(2, 'Yoga Alliance RYT 200');

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `E_id` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Cost` decimal(10,2) DEFAULT NULL,
  `Quantity` int(11) DEFAULT NULL,
  `Date_of_Purchase` date DEFAULT NULL,
  `V_name` varchar(500) DEFAULT NULL,
  `V_contact` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`E_id`, `Name`, `Cost`, `Quantity`, `Date_of_Purchase`, `V_name`, `V_contact`) VALUES
(1, 'Treadmill Pro Series X', 2500.00, 5, '2023-01-15', 'FitEquip Inc.', 'sales@fitequip.com'),
(2, 'Elliptical Cross-Trainer E5', 1800.00, 3, '2023-01-20', 'CardioMax Ltd.', 'contact@cardiomax.com'),
(3, 'Adjustable Dumbbell Set (5-50 lbs)', 350.00, 10, '2022-11-05', 'Strength Gear Co.', 'info@strengthgear.com'),
(4, 'Yoga Mat Premium', 25.00, 20, '2023-02-01', 'Zen Supplies', 'orders@zensupplies.net');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_serviced`
--

CREATE TABLE `equipment_serviced` (
  `S_id` int(11) NOT NULL,
  `E_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment_serviced`
--

INSERT INTO `equipment_serviced` (`S_id`, `E_id`) VALUES
(1, 1),
(2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `equipment_used`
--

CREATE TABLE `equipment_used` (
  `M_id` int(11) NOT NULL,
  `E_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment_used`
--

INSERT INTO `equipment_used` (`M_id`, `E_id`) VALUES
(1, 1),
(2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `F_id` int(11) NOT NULL,
  `Category` varchar(500) DEFAULT NULL,
  `Time` time DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `Text` text DEFAULT NULL,
  `M_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`F_id`, `Category`, `Time`, `Date`, `Text`, `M_id`) VALUES
(1, 'Trainers', '10:30:00', '2025-05-11', 'Daniel is very knowledgeable and motivating! The fat loss plan is challenging but effective.', 1),
(2, 'Equipment', '11:00:00', '2025-05-09', 'Some of the older treadmills are a bit noisy.', 1),
(3, 'Classes', '17:15:00', '2025-05-11', 'Loved the Zumba class last week! More evening options would be great.', 2),
(5, 'Equipment', '04:41:13', '2025-05-15', 'rusty', 3);

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `M_id` int(11) NOT NULL,
  `Email` varchar(500) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Subscription_Type` varchar(500) DEFAULT NULL,
  `Name` varchar(500) NOT NULL,
  `Date_of_Birth` date DEFAULT NULL,
  `Address` varchar(500) DEFAULT NULL,
  `Phone_No` varchar(20) DEFAULT NULL,
  `P_id` int(11) DEFAULT NULL,
  `S_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`M_id`, `Email`, `Password`, `Subscription_Type`, `Name`, `Date_of_Birth`, `Address`, `Phone_No`, `P_id`, `S_id`) VALUES
(1, 'sophia.davis@email.com', '123456', 'Premium Annual', 'Sophia Davis', '1995-03-12', '123 Member St, Fitville', '555-0201', 2, 1),
(2, 'ethan.wilson@email.com', 'abcdef', 'Basic Monthly', 'Ethan Wilson', '1988-07-22', '456 Active Rd, Fitville', '555-0202', 3, 2),
(3, 'chloe.garcia@email.com', '123456', 'Premium Monthly', 'Chloe Garcia', '1992-11-05', '789 Health Dr, Fitville', '555-02074', 2, 1),
(4, 'liam.rodriguez@email.com', 'abcdef', 'Inactive', 'Liam Rodriguez', '2000-01-30', '321 Fitness Ln, Fitville', '555-0204', NULL, NULL),
(12, 'tushit3902@gmail.com', '123456', 'Inactive', 'Tushit Roy', '2002-02-02', 'asdfghjkl', 'sdfghjk', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `plan`
--

CREATE TABLE `plan` (
  `P_id` int(11) NOT NULL,
  `Plan_Name` varchar(255) NOT NULL DEFAULT 'Default Plan',
  `Current_Weight` decimal(5,2) DEFAULT NULL,
  `Diet` text DEFAULT NULL,
  `Goal_Weight` decimal(5,2) DEFAULT NULL,
  `S_id` int(11) DEFAULT NULL,
  `Starting_Weight` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plan`
--

INSERT INTO `plan` (`P_id`, `Plan_Name`, `Current_Weight`, `Diet`, `Goal_Weight`, `S_id`, `Starting_Weight`) VALUES
(1, 'Beginner Fat Loss Program', NULL, 'Focus on calorie deficit+ exercise, lean proteins, and vegetables. Drink 3L water daily.', 70.00, 1, NULL),
(2, 'Intermediate Strength Builder', 999.99, 'Caloric surplus with high protein intake (1.8g/kg body weight). Complex carbs for energy.', 85.00, 1, 999.99),
(3, 'General Wellness & Toning', 71.00, 'Balanced macronutrients, whole foods, limit processed sugars. Regular hydration.', 65.00, 2, 72.00);

-- --------------------------------------------------------

--
-- Table structure for table `plan_routine`
--

CREATE TABLE `plan_routine` (
  `P_id` int(11) NOT NULL,
  `Day` varchar(50) NOT NULL,
  `Time` varchar(500) NOT NULL,
  `Exercise` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plan_routine`
--

INSERT INTO `plan_routine` (`P_id`, `Day`, `Time`, `Exercise`) VALUES
(1, 'Friday', 'Anytime', 'Full Body Circuit Training (Light weights)'),
(1, 'Monday', 'Morning', '30 min Brisk Walk (Treadmill)'),
(1, 'Sunday', 'Morning', 'Weight Training'),
(1, 'Wednesday', 'Evening', '20 min Cycling (Stationary Bike)'),
(1, 'Wednesday', 'Evening', 'Plank 3x45 seconds'),
(2, 'Saturday', 'Morning', 'Squats 4x8, Leg Press 3x12, Calf Raises 3x20'),
(2, 'Thursday', 'Afternoon', 'Deadlifts 1x5, Pull-ups 3xAMRAP, Barbell Rows 4x8'),
(2, 'Tuesday', 'Afternoon', 'Bench Press 4x8, Dumbbell Flyes 3x12'),
(2, 'Tuesday', 'Afternoon', 'Overhead Press 4x8, Lateral Raises 3x15'),
(3, 'Friday', 'Flexible', 'Yoga or Stretching Session (30-45 min)'),
(3, 'Monday', 'Flexible', '30 min Moderate Cardio (Elliptical/Bike)'),
(3, 'Wednesday', 'Flexible', 'Full Body Dumbbell Workout (Lunges, Rows, Presses)');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `S_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`S_id`) VALUES
(1),
(2);

-- --------------------------------------------------------

--
-- Table structure for table `staff_routine`
--

CREATE TABLE `staff_routine` (
  `S_id` int(11) NOT NULL,
  `Date` date NOT NULL,
  `Start_Time` time NOT NULL,
  `End_Time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_routine`
--

INSERT INTO `staff_routine` (`S_id`, `Date`, `Start_Time`, `End_Time`) VALUES
(1, '2025-05-11', '09:00:00', '12:00:00'),
(1, '2025-05-11', '14:00:00', '17:00:00'),
(1, '2025-05-12', '10:00:00', '13:00:00'),
(2, '2025-05-11', '13:00:00', '18:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `supervised`
--

CREATE TABLE `supervised` (
  `S_id` int(11) NOT NULL,
  `A_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supervised`
--

INSERT INTO `supervised` (`S_id`, `A_id`) VALUES
(1, 3),
(2, 3);

-- --------------------------------------------------------

--
-- Table structure for table `workers`
--

CREATE TABLE `workers` (
  `W_id` int(11) NOT NULL,
  `Name` varchar(500) NOT NULL,
  `Email` varchar(500) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Designation` varchar(500) DEFAULT NULL,
  `Address` varchar(500) DEFAULT NULL,
  `Phone_No` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `workers`
--

INSERT INTO `workers` (`W_id`, `Name`, `Email`, `Password`, `Designation`, `Address`, `Phone_No`) VALUES
(1, 'Daniel Miller', 'daniel.miller@gym.com', '123456', 'Senior Trainer', '10 Fitness Avenue, Gymtown', '555-0101'),
(2, 'Olivia Chen', 'olivia.chen@gym.com', 'abcdef', 'Personal Coach', '20 Wellness Road, Gymtown', '555-0102'),
(3, 'Marcus Administrator', 'marcus.admin@gym.com', '123456', 'Gym Manager', '1 Management Plaza, Gymtown', '555-0100');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`A_id`);

--
-- Indexes for table `certifications`
--
ALTER TABLE `certifications`
  ADD PRIMARY KEY (`W_id`,`Certification`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`E_id`);

--
-- Indexes for table `equipment_serviced`
--
ALTER TABLE `equipment_serviced`
  ADD PRIMARY KEY (`S_id`,`E_id`),
  ADD KEY `E_id` (`E_id`);

--
-- Indexes for table `equipment_used`
--
ALTER TABLE `equipment_used`
  ADD PRIMARY KEY (`M_id`,`E_id`),
  ADD KEY `E_id` (`E_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`F_id`),
  ADD KEY `M_id` (`M_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`M_id`),
  ADD UNIQUE KEY `UQ_Members_Email` (`Email`),
  ADD KEY `FK_Members_Plan_P_id` (`P_id`),
  ADD KEY `FK_Members_Staff_S_id` (`S_id`);

--
-- Indexes for table `plan`
--
ALTER TABLE `plan`
  ADD PRIMARY KEY (`P_id`),
  ADD KEY `S_id` (`S_id`);

--
-- Indexes for table `plan_routine`
--
ALTER TABLE `plan_routine`
  ADD PRIMARY KEY (`P_id`,`Day`,`Time`,`Exercise`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`S_id`);

--
-- Indexes for table `staff_routine`
--
ALTER TABLE `staff_routine`
  ADD PRIMARY KEY (`S_id`,`Date`,`Start_Time`,`End_Time`);

--
-- Indexes for table `supervised`
--
ALTER TABLE `supervised`
  ADD PRIMARY KEY (`S_id`,`A_id`),
  ADD KEY `A_id` (`A_id`);

--
-- Indexes for table `workers`
--
ALTER TABLE `workers`
  ADD PRIMARY KEY (`W_id`),
  ADD UNIQUE KEY `UQ_Workers_Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `E_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `F_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `M_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `plan`
--
ALTER TABLE `plan`
  MODIFY `P_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `workers`
--
ALTER TABLE `workers`
  MODIFY `W_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`A_id`) REFERENCES `workers` (`W_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `certifications`
--
ALTER TABLE `certifications`
  ADD CONSTRAINT `certifications_ibfk_1` FOREIGN KEY (`W_id`) REFERENCES `workers` (`W_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `equipment_serviced`
--
ALTER TABLE `equipment_serviced`
  ADD CONSTRAINT `equipment_serviced_ibfk_1` FOREIGN KEY (`S_id`) REFERENCES `staff` (`S_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `equipment_serviced_ibfk_2` FOREIGN KEY (`E_id`) REFERENCES `equipment` (`E_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `equipment_used`
--
ALTER TABLE `equipment_used`
  ADD CONSTRAINT `equipment_used_ibfk_1` FOREIGN KEY (`M_id`) REFERENCES `members` (`M_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `equipment_used_ibfk_2` FOREIGN KEY (`E_id`) REFERENCES `equipment` (`E_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`M_id`) REFERENCES `members` (`M_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `FK_Members_Plan_P_id` FOREIGN KEY (`P_id`) REFERENCES `plan` (`P_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_Members_Staff_S_id` FOREIGN KEY (`S_id`) REFERENCES `staff` (`S_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `plan`
--
ALTER TABLE `plan`
  ADD CONSTRAINT `plan_ibfk_1` FOREIGN KEY (`S_id`) REFERENCES `staff` (`S_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `plan_routine`
--
ALTER TABLE `plan_routine`
  ADD CONSTRAINT `plan_routine_ibfk_1` FOREIGN KEY (`P_id`) REFERENCES `plan` (`P_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`S_id`) REFERENCES `workers` (`W_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `staff_routine`
--
ALTER TABLE `staff_routine`
  ADD CONSTRAINT `staff_routine_ibfk_1` FOREIGN KEY (`S_id`) REFERENCES `staff` (`S_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `supervised`
--
ALTER TABLE `supervised`
  ADD CONSTRAINT `supervised_ibfk_1` FOREIGN KEY (`S_id`) REFERENCES `staff` (`S_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supervised_ibfk_2` FOREIGN KEY (`A_id`) REFERENCES `admin` (`A_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
